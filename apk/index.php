<?php
require "../koneksi.php";

$bulan_ini = date("Y-m");

// Saldo Pribadi
$q_saldo_pribadi = mysqli_query($conn, "SELECT SUM(saldo) as total FROM dompet WHERE tipe = 'Pribadi'");
$saldo_pribadi = mysqli_fetch_assoc($q_saldo_pribadi)["total"] ?? 0;

// Saldo Bisnis
$q_saldo_bisnis = mysqli_query($conn, "SELECT SUM(saldo) as total FROM dompet WHERE tipe = 'Bisnis'");
$saldo_bisnis = mysqli_fetch_assoc($q_saldo_bisnis)["total"] ?? 0;

// Data User (Foto Profil)
$user_id = $_SESSION['user_id'];
$q_u = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id = '$user_id'");
$r_u = mysqli_fetch_assoc($q_u);
$foto = $r_u['foto_profil'] ?? '';
$foto_url = empty($foto) ? "../aulalogo.png" : "../uploads/" . $foto;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <title>Aula Cell - Mobile</title>
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
                        primary: "#2563eb", // Royal blue
                        secondary: "#3b82f6", 
                        bglight: "#f1f5f9",
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f172a; color: #334155; -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 1rem); }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        
        /* The header curve */
        .header-curve {
            border-bottom-left-radius: 2.5rem;
            border-bottom-right-radius: 2.5rem;
            box-shadow: 0 4px 20px -2px rgba(37, 99, 235, 0.3);
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 flex justify-center items-center h-[100dvh] overflow-hidden">

    <!-- Mobile Frame -->
    <div class="w-full h-[100dvh] md:max-w-[400px] md:h-[95dvh] md:max-h-[850px] bg-slate-50 relative md:shadow-2xl md:rounded-[2.5rem] overflow-hidden flex flex-col md:border-8 md:border-slate-800">
        
        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden pb-24">
            
            <!-- Clean Header (Mifhda style but Aula Cell colors) -->
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 header-curve pt-10 pb-16 px-6 relative text-white">
                
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2 mb-2 bg-white/20 w-max px-3 py-1 rounded-full text-xs font-bold backdrop-blur-sm">
                            <i class="fa-solid fa-store text-yellow-300"></i>
                            <span>Aula Cell</span>
                        </div>
                        <p class="text-sm text-blue-100 font-semibold">Selamat Datang,</p>
                        <h1 class="text-2xl font-extrabold tracking-tight">Bos <?= explode(' ', trim($_SESSION['nama_lengkap']))[0] ?></h1>
                    </div>
                    
                    <a href="pengaturan.php" class="w-12 h-12 rounded-full border-2 border-white/40 overflow-hidden bg-white/20 flex items-center justify-center shadow-inner">
                        <img src="<?= $foto_url ?>" class="w-full h-full object-cover">
                    </a>
                </div>
                
                <!-- Date Bar -->
                <div class="absolute -bottom-5 left-6 right-6 bg-white rounded-2xl p-4 shadow-lg flex justify-between items-center text-slate-700 border border-slate-100 z-10">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <i class="fa-regular fa-calendar text-primary"></i> <?= date('d M Y') ?>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-500 bg-emerald-50 px-2 py-1 rounded-md">
                        <i class="fa-solid fa-shield-halved"></i> Aman
                    </div>
                </div>
            </div>

            <!-- Main Body -->
            <main class="px-5 pt-12 pb-10">
                
                <div class="flex items-center gap-2 font-extrabold text-slate-800 mb-4">
                    <i class="fa-solid fa-wallet text-amber-500"></i>
                    <h2 class="text-lg">Ringkasan Saldo</h2>
                </div>
                
                <!-- Enhanced Minimalist Vertical Cards -->
                <div class="grid grid-cols-1 gap-4 mb-8">
                    <!-- Card Bisnis -->
                    <div class="bg-white rounded-[1.25rem] p-5 shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-slate-200 flex justify-between items-center relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-primary"></div>
                        <div class="pl-2 relative z-10">
                            <p class="text-slate-500 text-[11px] font-extrabold uppercase tracking-widest mb-1">Modal Bisnis</p>
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Rp <?= number_format($saldo_bisnis, 0, ',', '.') ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center relative z-10">
                            <i class="fa-solid fa-store text-primary text-xl"></i>
                        </div>
                    </div>
                    
                    <!-- Card Pribadi -->
                    <div class="bg-white rounded-[1.25rem] p-5 shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-slate-200 flex justify-between items-center relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500"></div>
                        <div class="pl-2 relative z-10">
                            <p class="text-slate-500 text-[11px] font-extrabold uppercase tracking-widest mb-1">Uang Pribadi</p>
                            <h3 class="text-2xl font-black text-slate-800 tracking-tight">Rp <?= number_format($saldo_pribadi, 0, ',', '.') ?></h3>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center relative z-10">
                            <i class="fa-solid fa-user text-emerald-500 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Quote Romantis / Motivasi -->
                <div class="mt-2 mb-8 bg-gradient-to-br from-blue-50/80 to-indigo-50/50 rounded-2xl p-4 border border-blue-100/50 flex gap-3 items-start shadow-sm">
                    <i class="fa-solid fa-quote-left text-blue-300 text-lg mt-0.5"></i>
                    <div>
                        <p class="text-xs font-semibold text-slate-600 italic leading-relaxed">
                            "Kita usahakan semuanya pelan-pelan ya. Apapun badainya, kita pasti bisa lewati sama-sama."
                        </p>
                        <p class="text-[10px] font-extrabold text-blue-500 mt-2 tracking-wide uppercase">&hearts; Semangat terus, Sayang!</p>
                    </div>
                </div>

            </main>
        </div>

        <!-- Clean Bottom Nav (Mifhda Style floating) -->
        <div class="w-full z-50 mt-auto bg-white border-t border-slate-200 relative shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
            <div class="flex justify-around items-end px-2 pb-safe pt-2">
                
                <a href="index.php" class="flex flex-col items-center gap-1 text-primary w-16 pb-2">
                    <i class="fa-solid fa-house text-lg"></i>
                    <span class="text-[10px] font-bold">Beranda</span>
                </a>
                
                <a href="laporan_bisnis.php" class="flex flex-col items-center gap-1 text-slate-400 hover:text-primary transition-colors w-16 pb-2">
                    <i class="fa-solid fa-store text-lg"></i>
                    <span class="text-[10px] font-bold">Bisnis</span>
                </a>
                
                <!-- Floating Center Button -->
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
                
                <a href="pengaturan.php" class="flex flex-col items-center gap-1 text-slate-400 hover:text-primary transition-colors w-16 pb-2">
                    <i class="fa-solid fa-gear text-lg"></i>
                    <span class="text-[10px] font-bold">Profil</span>
                </a>
            </div>
        </div>
        
    </div>
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