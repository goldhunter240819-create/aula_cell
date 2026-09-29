<?php
require '../koneksi.php';

if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 
    
    $q = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if(mysqli_num_rows($q) > 0) {
        $user = mysqli_fetch_assoc($q);
        if(password_verify($password, $user['password']) || $password === $user['password']) {
            if($password === $user['password'] && !password_verify($password, $user['password'])) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE id = " . $user['id']);
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <title>Login Aula Cell Mobile</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ["Nunito", "sans-serif"], },
                    colors: {
                        primary: "#2563eb", bglight: "#f8fafc",
                    }
                }
            }
        }
    </script>
    <style>
        body { color: #334155; -webkit-tap-highlight-color: transparent; }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        .header-curve {
            border-bottom-left-radius: 3rem;
            border-bottom-right-radius: 3rem;
        }
        
        /* Biometric Button Styles */
        @keyframes fingerprint-pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            50% { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0); }
        }
        @keyframes fingerprint-scan {
            0% { transform: translateY(100%); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(-100%); opacity: 0; }
        }
        @keyframes shimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }
        @keyframes success-pop {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .biometric-btn {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .biometric-btn:hover {
            transform: translateY(-2px);
        }
        .biometric-btn:active {
            transform: scale(0.96);
        }
        .biometric-btn.scanning {
            animation: fingerprint-pulse 1.5s ease-in-out infinite;
        }
        .biometric-btn.scanning .scan-line {
            display: block;
            animation: fingerprint-scan 1.5s ease-in-out infinite;
        }
        .biometric-btn .scan-line {
            display: none;
            position: absolute;
            left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.8), transparent);
            z-index: 10;
        }
        
        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #cbd5e1, transparent);
        }

        /* Toast notification */
        .toast {
            position: fixed;
            top: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(-120%);
            z-index: 9999;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-width: 90%;
        }
        .toast.show {
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 m-0 p-0 overflow-hidden">

    <!-- Toast Notification -->
    <div id="biometricToast" class="toast">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 px-5 py-3.5 flex items-center gap-3">
            <div id="toastIcon" class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-fingerprint text-emerald-500 text-sm"></i>
            </div>
            <p id="toastMsg" class="text-sm font-bold text-slate-700"></p>
        </div>
    </div>

    <!-- Mobile Frame -->
    <div class="fixed inset-0 w-full max-w-[28rem] mx-auto bg-slate-50 shadow-2xl overflow-hidden flex flex-col">
        
        <!-- Curved Blue Background -->
        <div class="absolute top-0 w-full h-80 bg-gradient-to-b from-blue-700 to-blue-500 header-curve z-0"></div>
        
        <div class="relative z-10 flex-1 flex flex-col justify-center px-6 py-10">
            
            <div class="flex flex-col items-center mb-8">
                <div class="w-24 h-24 rounded-2xl bg-white p-1 shadow-lg mb-4">
                    <img src="../aulalogo.png" class="w-full h-full object-cover rounded-xl">
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Aula Cell</h1>
                <p class="text-blue-100 font-semibold text-sm">Finance Manager</p>
            </div>
            
            <form action="" method="POST" class="bg-white w-full rounded-[2rem] p-6 shadow-[0_10px_40px_rgba(0,0,0,0.08)]">
                <h2 class="text-xl font-extrabold text-slate-800 mb-6 text-center">Silakan Masuk</h2>
                
                <?php if($error): ?>
                    <div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-500 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <div class="space-y-4">
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <input type="text" name="username" required 
                                class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-slate-700"
                                placeholder="Username">
                        </div>
                    </div>
                    
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input type="password" id="password" name="password" required 
                                class="w-full pl-11 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-slate-700"
                                placeholder="Password">
                            <button type="button" onclick="togglePass()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-primary transition-colors focus:outline-none">
                                <i class="fa-solid fa-eye-slash" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Tombol Masuk + Biometrik -->
                <div class="flex gap-3 mt-6">
                    <button type="submit" name="login" 
                        class="flex-1 bg-primary hover:bg-blue-700 text-white font-extrabold py-3.5 rounded-xl shadow-lg shadow-blue-500/30 active:scale-95 transition-transform">
                        Masuk
                    </button>
                    <button type="button" id="biometricLoginBtn" onclick="loginWithBiometric()" 
                        class="w-14 bg-gradient-to-br from-emerald-500 to-teal-500 text-white rounded-xl shadow-lg shadow-emerald-500/30 active:scale-90 transition-all flex items-center justify-center"
                        title="Login Biometrik">
                        <i class="fa-solid fa-fingerprint text-xl" id="biometricIcon"></i>
                    </button>
                </div>
            </form>
            
            <button id="installAppBtn" class="mx-auto mt-6 px-6 py-3 bg-white text-slate-700 font-extrabold text-xs rounded-full shadow-[0_4px_15px_rgba(0,0,0,0.05)] border border-slate-200 active:scale-95 transition-transform flex items-center justify-center gap-2">
                <i class="fa-solid fa-download text-primary"></i> Install ke Layar Utama
            </button>
            
            <p class="text-slate-400 font-bold text-[10px] mt-8 text-center uppercase tracking-widest">&copy; <?= date('Y') ?> Aula Cell App</p>
        </div>
        
    </div>
    
    <script>
        function togglePass() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }

        let deferredPrompt;
        const installBtn = document.getElementById('installAppBtn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    installBtn.classList.add('hidden');
                }
                deferredPrompt = null;
            } else {
                alert("Sistem belum siap atau aplikasi sudah diinstall. \n\nTips: Klik icon titik tiga di pojok kanan atas browser (Chrome), lalu pilih 'Tambahkan ke Layar Utama' atau 'Install Aplikasi'.");
            }
        });

        // =========================================
        // BIOMETRIC LOGIN (WebAuthn)
        // =========================================
        
        function showToast(message, type = 'info') {
            const toast = document.getElementById('biometricToast');
            const msg = document.getElementById('toastMsg');
            const icon = document.getElementById('toastIcon');
            
            msg.textContent = message;
            
            if (type === 'success') {
                icon.className = 'w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0';
                icon.innerHTML = '<i class="fa-solid fa-check text-emerald-500 text-sm"></i>';
            } else if (type === 'error') {
                icon.className = 'w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0';
                icon.innerHTML = '<i class="fa-solid fa-xmark text-red-500 text-sm"></i>';
            } else {
                icon.className = 'w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0';
                icon.innerHTML = '<i class="fa-solid fa-fingerprint text-blue-500 text-sm"></i>';
            }
            
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3500);
        }
        
        // Cek apakah ada credential terdaftar & tampilkan tombol
        async function initBiometric() {
            // Cek apakah ada credential terdaftar di server
            try {
                const res = await fetch('api_biometric.php?action=check');
                const data = await res.json();
                
                if (data.success && data.has_credentials) {
                    // Tampilkan tombol biometrik
                    document.getElementById('biometricLoginBtn').style.display = 'flex';
                }
            } catch(e) {
                console.log('Biometric check failed:', e);
            }
        }
        
        // Login dengan biometrik
        async function loginWithBiometric() {
            const btn = document.getElementById('biometricLoginBtn');
            const btnIcon = document.getElementById('biometricIcon');
            
            // Cek browser support dulu
            if (!window.PublicKeyCredential) {
                showToast('Browser tidak mendukung biometrik', 'error');
                return;
            }
            
            // Cek dulu apakah ada credential terdaftar
            try {
                const checkRes = await fetch('api_biometric.php?action=check');
                const checkData = await checkRes.json();
                if (!checkData.success || !checkData.has_credentials) {
                    showToast('Belum ada biometrik terdaftar. Daftarkan di menu Pengaturan', 'error');
                    return;
                }
            } catch(e) {
                showToast('Gagal cek biometrik', 'error');
                return;
            }
            
            // Start scanning animation
            btn.classList.add('scanning');
            btn.disabled = true;
            
            try {
                // 1. Ambil options dari server
                const optRes = await fetch('api_biometric.php?action=login_options');
                const optData = await optRes.json();
                
                if (!optData.success) {
                    throw new Error(optData.error);
                }
                
                // 2. Konversi challenge & credential IDs
                const publicKey = optData.publicKey;
                publicKey.challenge = base64urlToBuffer(publicKey.challenge);
                
                if (publicKey.allowCredentials) {
                    publicKey.allowCredentials = publicKey.allowCredentials.map(cred => ({
                        ...cred,
                        id: base64urlToBuffer(cred.id)
                    }));
                }
                
                // 3. Minta browser untuk verifikasi biometrik
                const assertion = await navigator.credentials.get({ publicKey });
                
                // 4. Kirim hasilnya ke server
                const verifyRes = await fetch('api_biometric.php?action=login_complete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: assertion.id,
                        rawId: bufferToBase64url(assertion.rawId),
                        response: {
                            authenticatorData: bufferToBase64url(assertion.response.authenticatorData),
                            clientDataJSON: bufferToBase64url(assertion.response.clientDataJSON),
                            signature: bufferToBase64url(assertion.response.signature)
                        },
                        type: assertion.type
                    })
                });
                
                const verifyData = await verifyRes.json();
                
                if (verifyData.success) {
                    btn.classList.remove('scanning');
                    btnIcon.className = 'fa-solid fa-check text-xl';
                    
                    showToast('Login biometrik berhasil! Selamat datang, ' + verifyData.user, 'success');
                    
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1200);
                } else {
                    throw new Error(verifyData.error);
                }
                
            } catch(err) {
                btn.classList.remove('scanning');
                btn.disabled = false;
                btnIcon.className = 'fa-solid fa-fingerprint text-xl';
                
                if (err.name === 'NotAllowedError') {
                    showToast('Verifikasi biometrik dibatalkan', 'error');
                } else {
                    showToast(err.message || 'Gagal verifikasi biometrik', 'error');
                }
            }
        }
        
        // Utility: base64url <-> ArrayBuffer
        function base64urlToBuffer(base64url) {
            const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
            const padLen = (4 - base64.length % 4) % 4;
            const padded = base64 + '='.repeat(padLen);
            const binary = atob(padded);
            const buffer = new Uint8Array(binary.length);
            for (let i = 0; i < binary.length; i++) {
                buffer[i] = binary.charCodeAt(i);
            }
            return buffer.buffer;
        }
        
        function bufferToBase64url(buffer) {
            const bytes = new Uint8Array(buffer);
            let binary = '';
            bytes.forEach(b => binary += String.fromCharCode(b));
            return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        }
        
        // Init on page load
        initBiometric();
    </script>
</body>
</html>