<?php
require '../koneksi.php';

$bulan_ini = date('Y-m');
$nama_bulan = date('F Y');

// 1. Total Laba Pulsa
$q_laba = mysqli_query($conn, "SELECT SUM(laba) as total FROM transaksi_penjualan WHERE status = 'Sukses'");
$laba_bulan_ini = mysqli_fetch_assoc($q_laba)['total'] ?? 0;

// 2. Pengeluaran Bisnis
$q_pengeluaran = mysqli_query($conn, "SELECT SUM(t.nominal) as total FROM transaksi_umum t JOIN dompet d ON t.id_dompet = d.id WHERE t.jenis = 'Pengeluaran' AND d.tipe = 'Bisnis'");
$pengeluaran_bulan_ini = mysqli_fetch_assoc($q_pengeluaran)['total'] ?? 0;

// 3. Pemasukan Lain Bisnis
$q_pemasukan = mysqli_query($conn, "SELECT SUM(t.nominal) as total FROM transaksi_umum t JOIN dompet d ON t.id_dompet = d.id WHERE t.jenis = 'Pemasukan' AND d.tipe = 'Bisnis'");
$pemasukan_bulan_ini = mysqli_fetch_assoc($q_pemasukan)['total'] ?? 0;

// REKAP BULANAN
$rekap_bulanan = [];
// Ambil Laba
$q1 = mysqli_query($conn, "SELECT DATE_FORMAT(tanggal, '%Y-%m') as bln, SUM(laba) as laba FROM transaksi_penjualan WHERE status='Sukses' GROUP BY bln");
while($r = mysqli_fetch_assoc($q1)) { $rekap_bulanan[$r['bln']]['laba'] = $r['laba']; }

// Ambil Pengeluaran & Pemasukan Lain
$q2 = mysqli_query($conn, "SELECT DATE_FORMAT(t.tanggal, '%Y-%m') as bln, t.jenis, SUM(t.nominal) as total FROM transaksi_umum t JOIN dompet d ON t.id_dompet = d.id WHERE d.tipe = 'Bisnis' GROUP BY bln, t.jenis");
while($r = mysqli_fetch_assoc($q2)) {
    if($r['jenis'] == 'Pemasukan') $rekap_bulanan[$r['bln']]['pemasukan'] = $r['total'];
    if($r['jenis'] == 'Pengeluaran') $rekap_bulanan[$r['bln']]['pengeluaran'] = $r['total'];
}
krsort($rekap_bulanan);
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Lap Bisnis - Aula Cell</title>
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <meta name="theme-color" content="#2563eb">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ["Nunito", "sans-serif"], }, colors: { primary: "#2563eb", secondary: "#3b82f6", bglight: "#f1f5f9", } } } }
    </script>
    <style>
        body { background-color: #0f172a; color: #334155; -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 1rem); }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        .header-curve { border-bottom-left-radius: 2.5rem; border-bottom-right-radius: 2.5rem; box-shadow: 0 4px 20px -2px rgba(37, 99, 235, 0.3); }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 flex justify-center items-center h-[100dvh] overflow-hidden">
    <div class="w-full h-[100dvh] md:max-w-[400px] md:h-[95dvh] md:max-h-[850px] bg-slate-50 relative md:shadow-2xl md:rounded-[2.5rem] overflow-hidden flex flex-col md:border-8 md:border-slate-800">
        <div class="flex-1 overflow-y-auto pb-32 overflow-x-hidden pb-24">
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 header-curve pt-10 pb-8 px-6 relative text-white">
                <div class="flex items-center gap-4 mb-2">
                    <a href="index.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm active:scale-95"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="text-xl font-extrabold tracking-tight">Laporan Bisnis</h1>
                </div>
            </div>
            <main class="px-5 pt-8 pb-10">
    
    <div class="mb-8">
        <!-- Baris 1: Pemasukan & Pengeluaran (2 Kolom) -->
        <div class="grid grid-cols-2 gap-3 mb-3">
            <div class="bg-white rounded-[1.25rem] p-4 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-slate-200">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center"><i class="fa-solid fa-arrow-down text-xs"></i></div>
                    <p class="text-slate-400 text-[10px] font-extrabold uppercase tracking-widest">Pemasukan</p>
                </div>
                <h3 class="text-lg font-black text-slate-800">Rp <?= number_format($pemasukan_bulan_ini, 0, ',', '.') ?></h3>
            </div>
            
            <div class="bg-white rounded-[1.25rem] p-4 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-slate-200">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-7 h-7 rounded-full bg-red-50 text-red-500 flex items-center justify-center"><i class="fa-solid fa-arrow-up text-xs"></i></div>
                    <p class="text-slate-400 text-[10px] font-extrabold uppercase tracking-widest">Pengeluaran</p>
                </div>
                <h3 class="text-lg font-black text-slate-800">Rp <?= number_format($pengeluaran_bulan_ini, 0, ',', '.') ?></h3>
            </div>
        </div>
        
        <!-- Baris 2: Laba (1 Kolom Full) -->
        <div class="bg-gradient-to-r from-blue-700 to-primary rounded-[1.25rem] p-5 shadow-[0_8px_30px_rgba(37,99,235,0.2)] text-white flex justify-between items-center">
            <div>
                <p class="text-blue-100 text-[11px] font-extrabold uppercase tracking-widest mb-1">Total Laba Penjualan</p>
                <h3 class="text-3xl font-black tracking-tight">Rp <?= number_format($laba_bulan_ini, 0, ',', '.') ?></h3>
            </div>
            <i class="fa-solid fa-money-bill-wave text-white/20 text-5xl"></i>
        </div>
    </div>
    
    <!-- ADDED: RIWAYAT TRANSAKSI BISNIS -->
    <a href="riwayat_bisnis.php" class="w-full bg-white border border-slate-200 shadow-sm p-4 rounded-2xl flex items-center justify-between mb-8 active:scale-95 transition-transform">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-50 text-primary flex items-center justify-center">
                <i class="fa-solid fa-list-ul"></i>
            </div>
            <div class="text-left">
                <h4 class="font-bold text-sm text-slate-800">Riwayat Penjualan</h4>
                <p class="text-[10px] text-slate-500 font-semibold">Lihat semua transaksi sebelumnya</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-right text-slate-300"></i>
    </a>

    <div class="flex items-center gap-2 font-extrabold text-slate-800 mb-4">
        <i class="fa-solid fa-calendar-days text-primary"></i>
        <h2 class="text-lg">Rekap Bulanan</h2>
    </div>
    
    <div class="flex flex-col gap-3 mb-8">
        <?php foreach($rekap_bulanan as $bln => $data): 
            $l = $data['laba'] ?? 0;
            $p = $data['pemasukan'] ?? 0;
            $k = $data['pengeluaran'] ?? 0;
            $bersih = ($l + $p) - $k;
        ?>
        <div class="bg-white p-4 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-200">
            <h4 class="font-extrabold text-slate-800 border-b border-slate-100 pb-2 mb-2"><?= date('F Y', strtotime($bln.'-01')) ?></h4>
            <div class="flex justify-between text-xs font-bold text-slate-500 mb-1">
                <span>Total Laba:</span> <span class="text-blue-600">Rp <?= number_format($l,0,',','.') ?></span>
            </div>
            <div class="flex justify-between text-xs font-bold text-slate-500 mb-1">
                <span>Pemasukan Lain:</span> <span class="text-emerald-500">Rp <?= number_format($p,0,',','.') ?></span>
            </div>
            <div class="flex justify-between text-xs font-bold text-slate-500 mb-2">
                <span>Pengeluaran:</span> <span class="text-red-500">Rp <?= number_format($k,0,',','.') ?></span>
            </div>
            <div class="flex justify-between text-sm font-black text-slate-800 pt-2 border-t border-slate-100">
                <span>Laba Bersih:</span> <span class="<?= $bersih >= 0 ? 'text-emerald-600' : 'text-red-500' ?>">Rp <?= number_format($bersih,0,',','.') ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    
</main></div>
        <div class="absolute bottom-0 left-0 right-0 w-full z-50 bg-white border-t border-slate-200 relative shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
            <div class="flex justify-around items-end px-2 pb-safe pt-2">
                <a href="index.php" class="flex flex-col items-center gap-1 text-slate-400 hover:text-primary w-16 pb-2">
                    <i class="fa-solid fa-house text-lg"></i>
                    <span class="text-[10px] font-bold">Beranda</span>
                </a>
                <a href="laporan_bisnis.php" class="flex flex-col items-center gap-1 text-primary transition-colors w-16 pb-2">
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