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
<body class="font-sans antialiased bg-slate-900 flex justify-center items-center h-[100dvh] overflow-hidden md:p-4">
    <div class="w-full md:max-w-[400px] md:h-[800px] h-[100dvh] bg-slate-50 relative md:shadow-2xl md:rounded-[2.5rem] overflow-hidden flex flex-col md:border-8 md:border-slate-800">
        <div class="flex-1 overflow-y-auto overflow-x-hidden pb-24">
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
    
    <a href="logout.php" class="mt-4 w-full flex items-center justify-center gap-2 p-4 rounded-[1.25rem] bg-red-50 text-red-600 font-black border border-red-100 shadow-sm active:scale-95 transition-transform">
        <i class="fa-solid fa-power-off"></i> Keluar Aplikasi
    </a>

            </main>
        </div>
        <div class="w-full z-50 mt-auto bg-white border-t border-slate-200 relative shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
            <div class="flex justify-around items-end px-2 pb-safe pt-2">
                <a href="index.php" class="flex flex-col items-center gap-1 text-slate-400 hover:text-primary w-16 pb-2">
                    <i class="fa-solid fa-house text-lg"></i>
                    <span class="text-[10px] font-bold">Beranda</span>
                </a>
                <a href="laporan_bisnis.php" class="flex flex-col items-center gap-1 text-slate-400 hover:text-primary transition-colors w-16 pb-2">
                    <i class="fa-solid fa-store text-lg"></i>
                    <span class="text-[10px] font-bold">Bisnis</span>
                </a>
                <div class="relative flex flex-col items-center justify-end w-16 h-full z-20">
                    <button onclick="document.getElementById('catatModal').classList.remove('hidden')" class="absolute -top-12 w-16 h-16 bg-primary rounded-full flex items-center justify-center text-white shadow-[0_8px_20px_rgba(37,99,235,0.4)] active:scale-95 transition-transform ring-[8px] ring-bglight" focus:outline-none">
                        <i class="fa-solid fa-plus text-xl"></i>
                    </button>
                    <span class="text-[10px] font-bold text-slate-500 pb-2">Catat</span>
                </div>
                <a href="laporan_pribadi.php" class="flex flex-col items-center gap-1 text-slate-400 hover:text-primary transition-colors w-16 pb-2">
                    <i class="fa-solid fa-user text-lg"></i>
                    <span class="text-[10px] font-bold">Pribadi</span>
                </a>
                <a href="pengaturan.php" class="flex flex-col items-center gap-1 text-primary transition-colors w-16 pb-2">
                    <i class="fa-solid fa-gear text-lg"></i>
                    <span class="text-[10px] font-bold">Profil</span>
                </a>
            </div>
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
    </script>
    <!-- Modal Catat -->
    <div id="catatModal" class="hidden fixed inset-0 z-[100] flex items-end justify-center sm:items-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('catatModal').classList.add('hidden')"></div>
        
        <!-- Modal Panel -->
        <div class="bg-white w-full md:w-[400px] rounded-t-[2rem] md:rounded-[2rem] p-6 relative transform transition-transform shadow-2xl pb-safe border-t border-slate-100">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-6"></div>
            
            <h3 class="text-xl font-black text-slate-800 text-center mb-2">Mau Catat Apa Nih?</h3>
            <p class="text-xs font-bold text-slate-400 text-center mb-6">Pilih jenis transaksi yang mau dicatat hari ini</p>
            
            <div class="flex flex-col gap-3">
                <a href="jual_pulsa.php" class="bg-blue-50/50 border border-blue-100 rounded-2xl p-4 flex items-center gap-4 hover:bg-blue-50 transition-colors active:scale-95">
                    <div class="w-12 h-12 rounded-full bg-blue-100 text-primary flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-extrabold text-slate-800 text-sm">Jual Beli Konter</h4>
                        <p class="text-[10px] font-bold text-slate-500 mt-0.5">Jual pulsa, topup, dll</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300"></i>
                </a>

                <a href="transaksi_bisnis_umum.php" class="bg-purple-50/50 border border-purple-100 rounded-2xl p-4 flex items-center gap-4 hover:bg-purple-50 transition-colors active:scale-95">
                    <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-500 flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-extrabold text-slate-800 text-sm">Operasional Bisnis</h4>
                        <p class="text-[10px] font-bold text-slate-500 mt-0.5">Bayar listrik, modal, dll</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300"></i>
                </a>
                
                <a href="transaksi_umum.php" class="bg-emerald-50/50 border border-emerald-100 rounded-2xl p-4 flex items-center gap-4 hover:bg-emerald-50 transition-colors active:scale-95">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-extrabold text-slate-800 text-sm">Transaksi Pribadi</h4>
                        <p class="text-[10px] font-bold text-slate-500 mt-0.5">Uang jajan, bensin, & tabungan</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300"></i>
                </a>
            </div>
            
            <button onclick="document.getElementById('catatModal').classList.add('hidden')" class="w-full mt-6 py-4 bg-slate-100 text-slate-500 font-extrabold rounded-[1.25rem] active:scale-95 transition-transform text-sm hover:bg-slate-200">
                Batal
            </button>
        </div>
    </div>
</body>
</html>