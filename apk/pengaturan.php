<?php
require "../koneksi.php";

$sukses_msg = "";
$error_msg = "";

// 1. Ambil data user
$user_id = $_SESSION['user_id'];
$q_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($q_user);
$foto_profil = $user_data['foto_profil'] ?? '';
$foto_path = empty($foto_profil) ? "../aulalogo.png" : "../uploads/" . $foto_profil;

// 2. Handle Upload Foto
if(isset($_POST["upload_foto"]) && isset($_FILES["foto"])) {
    $file = $_FILES["foto"];
    if($file["error"] == 0) {
        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
        $new_name = "profil_" . time() . "." . $ext;
        
        if(!is_dir("../uploads")) mkdir("../uploads");
        
        if(move_uploaded_file($file["tmp_name"], "../uploads/" . $new_name)) {
            mysqli_query($conn, "UPDATE users SET foto_profil = '$new_name' WHERE id = " . $user_data['id']);
            $sukses_msg = "Foto profil berhasil diperbarui!";
            $foto_path = "../uploads/" . $new_name; // Update var lokal
        } else {
            $error_msg = "Gagal mengunggah foto.";
        }
    }
}

// 3. Handle Ubah Password
if(isset($_POST["ubah_password"])){
    $pass1 = $_POST["new_password"];
    $pass2 = $_POST["confirm_password"];
    
    if($pass1 === $pass2){
        $hashed = password_hash($pass1, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET password = '$hashed' WHERE id = " . $user_data['id']);
        if($update){
            $sukses_msg = "Password berhasil diubah!";
        } else {
            $error_msg = "Gagal mengubah password!";
        }
    } else {
        $error_msg = "Konfirmasi password tidak cocok!";
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Profil - Aula Cell</title>
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <meta name="theme-color" content="#2563eb">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ["Nunito", "sans-serif"], },
                    colors: { primary: "#2563eb", secondary: "#3b82f6", bglight: "#f1f5f9", }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f172a; color: #334155; -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 1rem); }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        .header-curve { border-bottom-left-radius: 2.5rem; border-bottom-right-radius: 2.5rem; box-shadow: 0 4px 20px -2px rgba(37, 99, 235, 0.3); }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 m-0 p-0 overflow-hidden">
    <div class="fixed inset-0 w-full max-w-[28rem] mx-auto bg-slate-50 shadow-2xl overflow-hidden flex flex-col">
        <div class="flex-1 overflow-y-auto pb-32 overflow-x-hidden pb-24">
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 header-curve pt-10 pb-10 px-6 relative text-white">
                <div class="flex items-center gap-4">
                    <a href="index.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm active:scale-95"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="text-xl font-extrabold tracking-tight">Profil & Pengaturan</h1>
                </div>
            </div>
            <main class="px-5 pt-8 pb-10">
    <?php if($sukses_msg): ?>
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center gap-3 text-sm font-bold shadow-sm">
            <i class="fa-solid fa-circle-check text-xl"></i> <?= $sukses_msg ?>
        </div>
    <?php endif; ?>
    <?php if($error_msg): ?>
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 flex items-center gap-3 text-sm font-bold shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-xl"></i> <?= $error_msg ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-[1.25rem] p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-slate-200 flex flex-col items-center mb-6 relative">
        <div class="relative w-24 h-24 mb-4">
            <div class="w-full h-full rounded-full border-4 border-slate-100 overflow-hidden shadow-sm">
                <img src="<?= $foto_path ?>" class="w-full h-full object-cover">
            </div>
            <button onclick="document.getElementById('input_foto').click()" class="absolute bottom-0 right-0 w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center shadow-lg border-2 border-white active:scale-95 transition-transform">
                <i class="fa-solid fa-camera text-xs"></i>
            </button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" id="form_foto" class="hidden">
            <input type="file" name="foto" id="input_foto" accept="image/*" onchange="document.getElementById('form_foto').submit()">
            <input type="hidden" name="upload_foto" value="1">
        </form>

        <h2 class="text-xl font-black text-slate-800 tracking-tight"><?= $_SESSION['nama_lengkap'] ?></h2>
        <p class="text-[11px] font-extrabold uppercase tracking-widest text-slate-400 mt-1">Owner Aula Cell</p>
    </div>
    
    <a href="buku_hutang.php" class="w-full bg-white border border-slate-200 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-4 rounded-[1.25rem] flex items-center justify-between mb-3 active:scale-95 transition-transform">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center text-lg">
                <i class="fa-solid fa-book"></i>
            </div>
            <div class="text-left">
                <h4 class="font-bold text-sm text-slate-800">Buku Hutang</h4>
                <p class="text-[10px] text-slate-500 font-semibold">Kelola piutang pelanggan</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-right text-slate-300"></i>
    </a>
    
    <button onclick="togglePasswordForm()" class="w-full bg-white border border-slate-200 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-4 rounded-[1.25rem] flex items-center justify-between mb-4 active:scale-95 transition-transform">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div class="text-left">
                <h4 class="font-bold text-sm text-slate-800">Ubah Password</h4>
                <p class="text-[10px] text-slate-500 font-semibold">Ganti kata sandi keamanan</p>
            </div>
        </div>
        <i id="pwd_icon" class="fa-solid fa-chevron-down text-slate-300 transition-transform"></i>
    </button>

    <form method="POST" action="" id="form_password" class="hidden bg-white rounded-[1.25rem] p-5 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-slate-200 flex flex-col gap-4 mb-6">
        <div>
            <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Password Lama</label>
            <input type="password" name="old_password" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
        </div>
        <div>
            <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Password Baru</label>
            <input type="password" name="new_password" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
        </div>
        <div>
            <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-1.5">Konfirmasi Password Baru</label>
            <input type="password" name="confirm_password" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
        </div>
        <button type="submit" name="ubah_password" class="w-full mt-2 bg-primary text-white font-black py-3.5 rounded-xl shadow-[0_8px_20px_rgba(37,99,235,0.3)] active:scale-95 transition-transform">
            Simpan Password
        </button>
    </form>
    
    <!-- Biometric Section -->
    <div id="biometricSection" class="mb-4" style="display:none;">
        <div class="w-full bg-white border border-slate-200 shadow-[0_4px_20px_rgba(0,0,0,0.03)] p-4 rounded-[1.25rem]">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-fingerprint"></i>
                    </div>
                    <div class="text-left">
                        <h4 class="font-bold text-sm text-slate-800">Login Biometrik</h4>
                        <p class="text-[10px] text-slate-500 font-semibold">Sidik jari / Face ID</p>
                    </div>
                </div>
                <div id="biometricStatus" class="flex items-center gap-1.5 text-[10px] font-extrabold px-2.5 py-1 rounded-full">
                    <!-- Status diisi via JS -->
                </div>
            </div>
            
            <!-- Info biometrik terdaftar -->
            <div id="biometricInfo" class="mb-3 text-xs text-slate-500 font-semibold bg-slate-50 rounded-xl p-3 hidden">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fa-solid fa-circle-info text-blue-400"></i>
                    <span id="biometricInfoText">Memuat...</span>
                </div>
            </div>

            <!-- Register Button -->
            <button id="btnRegisterBiometric" onclick="registerBiometric()" 
                class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-extrabold py-3 rounded-xl shadow-lg shadow-emerald-500/30 active:scale-95 transition-all flex items-center justify-center gap-2 text-sm">
                <i class="fa-solid fa-fingerprint"></i>
                <span id="btnRegisterText">Daftarkan Biometrik</span>
            </button>

            <!-- Remove Button (hidden by default) -->
            <button id="btnRemoveBiometric" onclick="removeBiometric()" 
                class="hidden w-full mt-2 bg-red-50 border border-red-100 text-red-500 font-extrabold py-3 rounded-xl active:scale-95 transition-all flex items-center justify-center gap-2 text-sm">
                <i class="fa-solid fa-trash-can"></i> Hapus Biometrik
            </button>
        </div>
    </div>

    <a href="logout.php" class="mt-4 w-full flex items-center justify-center gap-2 p-4 rounded-[1.25rem] bg-red-50 text-red-600 font-black border border-red-100 shadow-sm active:scale-95 transition-transform">
        <i class="fa-solid fa-power-off"></i> Keluar Aplikasi
    </a>

            </main>
        </div>

    <!-- Toast Notification -->
    <div id="biometricToast" class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999] transition-all duration-400 -translate-y-[120%] max-w-[90%]" style="transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 px-5 py-3.5 flex items-center gap-3">
            <div id="toastIcon" class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-fingerprint text-emerald-500 text-sm"></i>
            </div>
            <p id="toastMsg" class="text-sm font-bold text-slate-700"></p>
        </div>
    </div>

    <script>
        function togglePasswordForm() {
            const form = document.getElementById('form_password');
            const icon = document.getElementById('pwd_icon');
            
            if(form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                form.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        // =============================================
        // Toast
        // =============================================
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
            
            toast.style.transform = 'translateX(-50%) translateY(0)';
            setTimeout(() => { toast.style.transform = 'translateX(-50%) translateY(-120%)'; }, 3500);
        }

        // =============================================
        // Biometric Management
        // =============================================
        
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

        async function initBiometricSettings() {
            // Cek browser support
            if (!window.PublicKeyCredential) return;
            
            try {
                const available = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
                if (!available) return;
            } catch(e) {
                return;
            }

            // Show biometric section
            document.getElementById('biometricSection').style.display = 'block';
            
            // Cek status
            await refreshBiometricStatus();
        }

        async function refreshBiometricStatus() {
            try {
                const res = await fetch('api_biometric.php?action=check');
                const data = await res.json();
                
                const statusEl = document.getElementById('biometricStatus');
                const infoEl = document.getElementById('biometricInfo');
                const infoText = document.getElementById('biometricInfoText');
                const btnRegister = document.getElementById('btnRegisterBiometric');
                const btnRemove = document.getElementById('btnRemoveBiometric');
                const btnRegisterText = document.getElementById('btnRegisterText');
                
                if (data.user_has_biometric) {
                    statusEl.className = 'flex items-center gap-1.5 text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600';
                    statusEl.innerHTML = '<i class="fa-solid fa-shield-check"></i> Aktif';
                    
                    // Ambil detail credential
                    const listRes = await fetch('api_biometric.php?action=list');
                    const listData = await listRes.json();
                    
                    if (listData.success && listData.credentials.length > 0) {
                        const cred = listData.credentials[0];
                        const createdDate = new Date(cred.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                        infoText.textContent = `${cred.device_name} • Didaftarkan ${createdDate} • Digunakan ${cred.sign_count}x`;
                        infoEl.classList.remove('hidden');
                    }
                    
                    btnRegisterText.textContent = 'Tambah Perangkat Baru';
                    btnRemove.classList.remove('hidden');
                    btnRemove.classList.add('flex');
                } else {
                    statusEl.className = 'flex items-center gap-1.5 text-[10px] font-extrabold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500';
                    statusEl.innerHTML = '<i class="fa-solid fa-circle-minus"></i> Nonaktif';
                    
                    infoEl.classList.add('hidden');
                    btnRegisterText.textContent = 'Daftarkan Biometrik';
                    btnRemove.classList.add('hidden');
                }
            } catch(e) {
                console.error('Failed to check biometric status:', e);
            }
        }

        async function registerBiometric() {
            const btn = document.getElementById('btnRegisterBiometric');
            const btnText = document.getElementById('btnRegisterText');
            const originalText = btnText.textContent;
            
            btn.disabled = true;
            btnText.textContent = 'Memverifikasi...';
            
            try {
                // 1. Get registration options
                const optRes = await fetch('api_biometric.php?action=register_options');
                const optData = await optRes.json();
                
                if (!optData.success) throw new Error(optData.error);
                
                // 2. Prepare options
                const publicKey = optData.publicKey;
                publicKey.challenge = base64urlToBuffer(publicKey.challenge);
                publicKey.user.id = base64urlToBuffer(publicKey.user.id);
                
                if (publicKey.excludeCredentials) {
                    publicKey.excludeCredentials = publicKey.excludeCredentials.map(c => ({
                        ...c, id: base64urlToBuffer(c.id)
                    }));
                }
                
                // 3. Create credential (triggers biometric prompt)
                const credential = await navigator.credentials.create({ publicKey });
                
                // 4. Send to server
                const saveRes = await fetch('api_biometric.php?action=register_complete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: credential.id,
                        rawId: bufferToBase64url(credential.rawId),
                        response: {
                            attestationObject: bufferToBase64url(credential.response.attestationObject),
                            clientDataJSON: bufferToBase64url(credential.response.clientDataJSON),
                            publicKey: bufferToBase64url(credential.response.getPublicKey ? credential.response.getPublicKey() : new ArrayBuffer(0))
                        },
                        type: credential.type,
                        deviceName: getDeviceName()
                    })
                });
                
                const saveData = await saveRes.json();
                
                if (saveData.success) {
                    showToast('Biometrik berhasil didaftarkan! 🎉', 'success');
                    await refreshBiometricStatus();
                } else {
                    throw new Error(saveData.error);
                }
                
            } catch(err) {
                if (err.name === 'NotAllowedError') {
                    showToast('Pendaftaran biometrik dibatalkan', 'error');
                } else if (err.name === 'InvalidStateError') {
                    showToast('Perangkat ini sudah terdaftar', 'error');
                } else {
                    showToast(err.message || 'Gagal mendaftarkan biometrik', 'error');
                }
            } finally {
                btn.disabled = false;
                btnText.textContent = originalText;
            }
        }

        async function removeBiometric() {
            if (!confirm('Hapus semua data biometrik? Anda perlu mendaftarkan ulang nanti.')) return;
            
            try {
                const res = await fetch('api_biometric.php?action=delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({})
                });
                const data = await res.json();
                
                if (data.success) {
                    showToast('Biometrik berhasil dihapus', 'success');
                    await refreshBiometricStatus();
                } else {
                    throw new Error(data.error);
                }
            } catch(err) {
                showToast(err.message || 'Gagal menghapus biometrik', 'error');
            }
        }

        function getDeviceName() {
            const ua = navigator.userAgent;
            if (/iPhone/i.test(ua)) return 'iPhone';
            if (/iPad/i.test(ua)) return 'iPad';
            if (/Samsung/i.test(ua)) return 'Samsung';
            if (/Xiaomi|Redmi|POCO/i.test(ua)) return 'Xiaomi';
            if (/OPPO/i.test(ua)) return 'OPPO';
            if (/vivo/i.test(ua)) return 'Vivo';
            if (/Realme/i.test(ua)) return 'Realme';
            if (/Android/i.test(ua)) return 'Android';
            if (/Windows/i.test(ua)) return 'Windows Hello';
            if (/Mac/i.test(ua)) return 'MacBook Touch ID';
            return 'Perangkat Biometrik';
        }

        // Init
        initBiometricSettings();
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>