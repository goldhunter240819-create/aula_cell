<?php
// Session lifetime 1 tahun (365 hari)
$session_lifetime = 365 * 24 * 60 * 60; // 31536000 detik
ini_set('session.gc_maxlifetime', $session_lifetime);
ini_set('session.cookie_lifetime', $session_lifetime);
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "aula_cell";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Global Auth Check
$current_page = basename($_SERVER['PHP_SELF']);
if(!isset($_SESSION['user_id']) && $current_page != 'login.php') {
    header("Location: login.php");
    exit;
}

if(isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    
    // Auto-migrate: Add user_id to dompet if not exists
    $cek_col = mysqli_query($conn, "SHOW COLUMNS FROM dompet LIKE 'user_id'");
    if(mysqli_num_rows($cek_col) == 0) {
        mysqli_query($conn, "ALTER TABLE dompet ADD COLUMN user_id INT NULL AFTER tipe");
        // Update existing Pribadi wallets to belong to user ID 1 (Admin) as a fallback so they aren't lost
        mysqli_query($conn, "UPDATE dompet SET user_id = 1 WHERE tipe = 'Pribadi'");
    }
    
    // Ensure the current user has Cash and Saldo wallets
    $cek_cash = mysqli_query($conn, "SELECT id FROM dompet WHERE tipe = 'Pribadi' AND user_id = $uid AND (nama_dompet LIKE '%Cash%' OR nama_dompet LIKE '%Kas%')");
    if(mysqli_num_rows($cek_cash) == 0) {
        mysqli_query($conn, "INSERT INTO dompet (nama_dompet, tipe, user_id, saldo) VALUES ('Cash', 'Pribadi', $uid, 0)");
    } else {
        mysqli_query($conn, "UPDATE dompet SET nama_dompet = 'Cash' WHERE tipe = 'Pribadi' AND user_id = $uid AND (nama_dompet LIKE '%Kas%')");
    }

    $cek_saldo = mysqli_query($conn, "SELECT id FROM dompet WHERE tipe = 'Pribadi' AND user_id = $uid AND nama_dompet LIKE '%Saldo%'");
    if(mysqli_num_rows($cek_saldo) == 0) {
        mysqli_query($conn, "INSERT INTO dompet (nama_dompet, tipe, user_id, saldo) VALUES ('Saldo', 'Pribadi', $uid, 0)");
    }
}
?>
