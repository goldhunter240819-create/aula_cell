<?php
require '../koneksi.php';

$bulan_ini = date('Y-m');
$nama_bulan = date('F Y');

$q_pemasukan = mysqli_query($conn, "SELECT SUM(t.nominal) as total FROM transaksi_umum t JOIN dompet d ON t.id_dompet = d.id WHERE t.jenis = 'Pemasukan' AND d.tipe = 'Pribadi'");
$pemasukan_bulan_ini = mysqli_fetch_assoc($q_pemasukan)['total'] ?? 0;

$q_pengeluaran = mysqli_query($conn, "SELECT SUM(t.nominal) as total FROM transaksi_umum t JOIN dompet d ON t.id_dompet = d.id WHERE t.jenis = 'Pengeluaran' AND d.tipe = 'Pribadi'");
$pengeluaran_bulan_ini = mysqli_fetch_assoc($q_pengeluaran)['total'] ?? 0;

$saldo_bersih = $pemasukan_bulan_ini - $pengeluaran_bulan_ini;

// REKAP BULANAN
$rekap_bulanan = [];
$q3 = mysqli_query($conn, "SELECT DATE_FORMAT(t.tanggal, '%Y-%m') as bln, t.jenis, SUM(t.nominal) as total FROM transaksi_umum t JOIN dompet d ON t.id_dompet = d.id WHERE d.tipe = 'Pribadi' GROUP BY bln, t.jenis");
while($r = mysqli_fetch_assoc($q3)) {
    if($r['jenis'] == 'Pemasukan') $rekap_bulanan[$r['bln']]['pemasukan'] = $r['total'];
    if($r['jenis'] == 'Pengeluaran') $rekap_bulanan[$r['bln']]['pengeluaran'] = $r['total'];
}
krsort($rekap_bulanan);
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Lap Pribadi - Aula Cell</title>
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
<body class="font-sans antialiased bg-slate-900 flex justify-center h-[100dvh] overflow-hidden">
    <div class="w-full h-[100dvh] md:max-w-[400px] md:h-[95dvh] md:max-h-[850px] bg-slate-50 relative md:shadow-2xl md:rounded-[2.5rem] overflow-hidden flex flex-col md:border-8 md:border-slate-800">
        <div class="flex-1 overflow-y-auto pb-32 overflow-x-hidden pb-24">
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 header-curve pt-10 pb-8 px-6 relative text-white">
                <div class="flex items-center gap-4 mb-2">
                    <a href="index.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm active:scale-95"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="text-xl font-extrabold tracking-tight">Laporan Pribadi</h1>
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
        
        <!-- Baris 2: Sisa (1 Kolom Full) -->
        <div class="bg-gradient-to-r from-teal-500 to-emerald-500 rounded-[1.25rem] p-5 shadow-[0_8px_30px_rgba(16,185,129,0.2)] text-white flex justify-between items-center">
            <div>
                <p class="text-emerald-100 text-[11px] font-extrabold uppercase tracking-widest mb-1">Sisa Uang (Keseluruhan)</p>
                <h3 class="text-3xl font-black tracking-tight">Rp <?= number_format($saldo_bersih, 0, ',', '.') ?></h3>
            </div>
            <i class="fa-solid fa-scale-balanced text-white/20 text-5xl"></i>
        </div>
    </div>
    
    <!-- ADDED: RIWAYAT TRANSAKSI PRIBADI -->
    <a href="riwayat_pribadi.php" class="w-full bg-white border border-slate-200 shadow-sm p-4 rounded-2xl flex items-center justify-between mb-8 active:scale-95 transition-transform">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">
                <i class="fa-solid fa-list-ul"></i>
            </div>
            <div class="text-left">
                <h4 class="font-bold text-sm text-slate-800">Riwayat Pribadi</h4>
                <p class="text-[10px] text-slate-500 font-semibold">Lihat semua transaksi sebelumnya</p>
            </div>
        </div>
        <i class="fa-solid fa-chevron-right text-slate-300"></i>
    </a>

    <div class="flex items-center gap-2 font-extrabold text-slate-800 mb-4">
        <i class="fa-solid fa-calendar-days text-emerald-500"></i>
        <h2 class="text-lg">Rekap Bulanan</h2>
    </div>
    
    <div class="flex flex-col gap-3 mb-8">
        <?php foreach($rekap_bulanan as $bln => $data): 
            $p = $data['pemasukan'] ?? 0;
            $k = $data['pengeluaran'] ?? 0;
            $bersih = $p - $k;
        ?>
        <div class="bg-white p-4 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-200">
            <h4 class="font-extrabold text-slate-800 border-b border-slate-100 pb-2 mb-2"><?= date('F Y', strtotime($bln.'-01')) ?></h4>
            <div class="flex justify-between text-xs font-bold text-slate-500 mb-1">
                <span>Pemasukan:</span> <span class="text-emerald-500">Rp <?= number_format($p,0,',','.') ?></span>
            </div>
            <div class="flex justify-between text-xs font-bold text-slate-500 mb-2">
                <span>Pengeluaran:</span> <span class="text-red-500">Rp <?= number_format($k,0,',','.') ?></span>
            </div>
            <div class="flex justify-between text-sm font-black text-slate-800 pt-2 border-t border-slate-100">
                <span>Sisa/Selisih:</span> <span class="<?= $bersih >= 0 ? 'text-emerald-600' : 'text-red-500' ?>">Rp <?= number_format($bersih,0,',','.') ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    
</main></div>
        <?php include 'footer.php'; ?>
</body>
</html>