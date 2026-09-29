<?php
/**
 * WebAuthn API untuk Login Biometrik
 * Menggunakan Web Authentication API (fingerprint/face recognition)
 */
require '../koneksi.php';

header('Content-Type: application/json');

// Disable session requirement for this API
$action = $_GET['action'] ?? '';

// Helper: Generate random bytes sebagai base64url
function generateChallenge($length = 32) {
    $bytes = random_bytes($length);
    return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
}

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
}

// ============================================
// REGISTER: Mulai pendaftaran biometrik
// ============================================
if ($action === 'register_options') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Belum login']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $q = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
    $user = mysqli_fetch_assoc($q);

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'User tidak ditemukan']);
        exit;
    }

    $challenge = generateChallenge();
    $_SESSION['webauthn_challenge'] = $challenge;

    // Ambil credential yang sudah terdaftar (untuk exclude)
    $existing = [];
    $q_cred = mysqli_query($conn, "SELECT credential_id FROM webauthn_credentials WHERE user_id = '$user_id'");
    while ($row = mysqli_fetch_assoc($q_cred)) {
        $existing[] = [
            'id' => $row['credential_id'],
            'type' => 'public-key',
            'transports' => ['internal']
        ];
    }

    $options = [
        'success' => true,
        'publicKey' => [
            'challenge' => $challenge,
            'rp' => [
                'name' => 'Aula Cell',
                'id' => $_SERVER['HTTP_HOST']
            ],
            'user' => [
                'id' => base64url_encode($user['id']),
                'name' => $user['username'],
                'displayName' => $user['nama_lengkap']
            ],
            'pubKeyCredParams' => [
                ['alg' => -7, 'type' => 'public-key'],   // ES256
                ['alg' => -257, 'type' => 'public-key']  // RS256
            ],
            'timeout' => 60000,
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform',
                'userVerification' => 'required',
                'residentKey' => 'preferred'
            ],
            'attestation' => 'none',
            'excludeCredentials' => $existing
        ]
    ];

    echo json_encode($options);
    exit;
}

// ============================================
// REGISTER: Simpan credential biometrik
// ============================================
if ($action === 'register_complete') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Belum login']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['id']) || !isset($data['rawId']) || !isset($data['response'])) {
        echo json_encode(['success' => false, 'error' => 'Data credential tidak valid']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $credential_id = mysqli_real_escape_string($conn, $data['id']);
    $public_key = mysqli_real_escape_string($conn, $data['response']['publicKey'] ?? $data['rawId']);
    $device_name = mysqli_real_escape_string($conn, $data['deviceName'] ?? 'Perangkat Biometrik');

    // Buat tabel jika belum ada
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS webauthn_credentials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        credential_id TEXT NOT NULL,
        public_key TEXT NOT NULL,
        device_name VARCHAR(255) DEFAULT 'Perangkat Biometrik',
        sign_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_used_at TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Simpan credential
    $q = mysqli_query($conn, "INSERT INTO webauthn_credentials (user_id, credential_id, public_key, device_name) 
                              VALUES ('$user_id', '$credential_id', '$public_key', '$device_name')");

    if ($q) {
        echo json_encode(['success' => true, 'message' => 'Biometrik berhasil didaftarkan!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal menyimpan: ' . mysqli_error($conn)]);
    }
    exit;
}

// ============================================
// LOGIN: Mulai autentikasi biometrik
// ============================================
if ($action === 'login_options') {
    $challenge = generateChallenge();
    $_SESSION['webauthn_challenge'] = $challenge;

    // Ambil semua credential yang terdaftar
    $credentials = [];
    $q = mysqli_query($conn, "SELECT credential_id FROM webauthn_credentials");
    while ($row = mysqli_fetch_assoc($q)) {
        $credentials[] = [
            'id' => $row['credential_id'],
            'type' => 'public-key',
            'transports' => ['internal']
        ];
    }

    if (empty($credentials)) {
        echo json_encode(['success' => false, 'error' => 'Belum ada biometrik yang terdaftar. Silakan login manual dulu, lalu daftarkan biometrik di Pengaturan.']);
        exit;
    }

    $options = [
        'success' => true,
        'publicKey' => [
            'challenge' => $challenge,
            'rpId' => $_SERVER['HTTP_HOST'],
            'allowCredentials' => $credentials,
            'timeout' => 60000,
            'userVerification' => 'required'
        ]
    ];

    echo json_encode($options);
    exit;
}

// ============================================
// LOGIN: Verifikasi biometrik & buat session
// ============================================
if ($action === 'login_complete') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['id'])) {
        echo json_encode(['success' => false, 'error' => 'Data credential tidak valid']);
        exit;
    }

    $credential_id = mysqli_real_escape_string($conn, $data['id']);

    // Cari credential di database
    $q = mysqli_query($conn, "SELECT wc.*, u.id as uid, u.nama_lengkap, u.username 
                              FROM webauthn_credentials wc 
                              JOIN users u ON wc.user_id = u.id 
                              WHERE wc.credential_id = '$credential_id'");

    if (mysqli_num_rows($q) === 0) {
        echo json_encode(['success' => false, 'error' => 'Biometrik tidak dikenali']);
        exit;
    }

    $cred = mysqli_fetch_assoc($q);

    // Update last used
    mysqli_query($conn, "UPDATE webauthn_credentials SET last_used_at = NOW(), sign_count = sign_count + 1 WHERE credential_id = '$credential_id'");

    // Set session
    $_SESSION['user_id'] = $cred['uid'];
    $_SESSION['nama_lengkap'] = $cred['nama_lengkap'];

    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil!',
        'user' => $cred['nama_lengkap']
    ]);
    exit;
}

// ============================================
// CEK: Apakah ada biometrik terdaftar
// ============================================
if ($action === 'check') {
    // Buat tabel jika belum ada
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS webauthn_credentials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        credential_id TEXT NOT NULL,
        public_key TEXT NOT NULL,
        device_name VARCHAR(255) DEFAULT 'Perangkat Biometrik',
        sign_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_used_at TIMESTAMP NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM webauthn_credentials");
    $row = mysqli_fetch_assoc($q);
    
    $has_credentials = ($row['total'] > 0);

    // Cek juga apakah user yang sedang login punya biometrik
    $user_has = false;
    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        $q2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM webauthn_credentials WHERE user_id = '$uid'");
        $row2 = mysqli_fetch_assoc($q2);
        $user_has = ($row2['total'] > 0);
    }

    echo json_encode([
        'success' => true,
        'has_credentials' => $has_credentials,
        'user_has_biometric' => $user_has
    ]);
    exit;
}

// ============================================
// HAPUS: Hapus credential biometrik
// ============================================
if ($action === 'delete') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Belum login']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $cred_id = intval($data['cred_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if ($cred_id > 0) {
        mysqli_query($conn, "DELETE FROM webauthn_credentials WHERE id = $cred_id AND user_id = '$user_id'");
        echo json_encode(['success' => true, 'message' => 'Biometrik dihapus']);
    } else {
        // Hapus semua milik user ini
        mysqli_query($conn, "DELETE FROM webauthn_credentials WHERE user_id = '$user_id'");
        echo json_encode(['success' => true, 'message' => 'Semua biometrik dihapus']);
    }
    exit;
}

// ============================================
// LIST: Daftar credential user saat ini
// ============================================
if ($action === 'list') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Belum login']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $q = mysqli_query($conn, "SELECT id, device_name, sign_count, created_at, last_used_at FROM webauthn_credentials WHERE user_id = '$user_id' ORDER BY created_at DESC");
    
    $credentials = [];
    while ($row = mysqli_fetch_assoc($q)) {
        $credentials[] = $row;
    }

    echo json_encode(['success' => true, 'credentials' => $credentials]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Action tidak valid']);
