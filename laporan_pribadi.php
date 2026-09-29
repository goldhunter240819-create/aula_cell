<?php
require 'koneksi.php';

$bulan_ini = date('Y-m');
$nama_bulan = date('F Y');

// 1. Pemasukan Pribadi
$q_pemasukan = mysqli_query($conn, "SELECT SUM(t.nominal) as total FROM transaksi_umum t JOIN dompet d ON t.id_dompet = d.id WHERE t.jenis = 'Pemasukan' AND d.tipe = 'Pribadi' AND DATE_FORMAT(t.tanggal, '%Y-%m') = '$bulan_ini'");
$pemasukan_bulan_ini = mysqli_fetch_assoc($q_pemasukan)['total'] ?? 0;

// 2. Pengeluaran Pribadi
$q_pengeluaran = mysqli_query($conn, "SELECT SUM(t.nominal) as total FROM transaksi_umum t JOIN dompet d ON t.id_dompet = d.id WHERE t.jenis = 'Pengeluaran' AND d.tipe = 'Pribadi' AND DATE_FORMAT(t.tanggal, '%Y-%m') = '$bulan_ini'");
$pengeluaran_bulan_ini = mysqli_fetch_assoc($q_pengeluaran)['total'] ?? 0;

// 3. Saldo Bersih
$saldo_bersih = $pemasukan_bulan_ini - $pengeluaran_bulan_ini;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - Aula Cell</title>
    <link rel="icon" href="aulalogo.png" type="image/jpeg">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], },
                    colors: {
                        primary: '#1d4ed8', secondary: '#0ea5e9',
                        darkbg: '#0f172a', cardbg: '#1e293b',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #e2e8f0; /* slate-100 */
            color: #1e293b; /* slate-800 */
        }
        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex flex-col md:flex-row md:p-6 md:gap-8 overflow-x-hidden">

        <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-gradient-to-b from-blue-900 to-primary text-white p-5 flex flex-col gap-6 rounded-3xl md:h-[calc(100vh-3rem)] sticky top-6 z-20 shadow-2xl overflow-y-auto border border-blue-800/50">
        <?php $current_page = basename($_SERVER["PHP_SELF"]); ?>
        <style> details > summary { list-style: none; } details > summary::-webkit-details-marker { display: none; } </style>
        <div class="flex items-center gap-3 px-2">
            <div class="w-12 h-12 rounded-xl overflow-hidden shadow-lg shadow-primary/30 flex-shrink-0">
                <img src="aulalogo.png" alt="Aula Cell" class="w-full h-full object-cover">
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-white">Aula Cell</h1>
                <p class="text-xs text-blue-200">Finance Manager</p>
            </div>
        </div>

        <nav class="flex-1 flex flex-col gap-2 mt-4 text-sm">
            <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= ($current_page == 'index.php') ? 'bg-white/20 text-white font-bold shadow-inner' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                <i class="fa-solid fa-house w-4 text-center"></i> Dashboard
            </a>
            
            <!-- Bisnis Dropdown -->
            <details class="group" <?= in_array($current_page, ['jual_pulsa.php', 'laporan_bisnis.php', 'transaksi_bisnis_umum.php', 'buku_hutang.php', 'kategori.php', 'pelanggan.php']) ? 'open' : '' ?>>
                <summary class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl text-blue-200 hover:bg-white/10 hover:text-white transition-colors cursor-pointer select-none">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-store w-4 text-center"></i> Bisnis
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
                </summary>
                <div class="flex flex-col gap-1 pl-4 pr-2 py-1 mt-1 border-l-2 border-white/10 ml-6">
                    <a href="jual_pulsa.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == 'jual_pulsa.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-pen-to-square w-4 mr-1 text-center"></i> Jual Beli Pulsa
                    </a>
                    <a href="transaksi_bisnis_umum.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == 'transaksi_bisnis_umum.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-briefcase w-4 mr-1 text-center"></i> Operasional Bisnis
                    </a>
                    <a href="kategori.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == 'kategori.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-tags w-4 mr-1 text-center"></i> Kategori Produk
                    </a>
                    <a href="laporan_bisnis.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == 'laporan_bisnis.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-chart-line w-4 mr-1 text-center"></i> Laporan
                    </a>
                    <a href="buku_hutang.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == 'buku_hutang.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-book w-4 mr-1 text-center"></i> Buku Hutang
                    </a>
                    <a href="pelanggan.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == 'pelanggan.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-address-book w-4 mr-1 text-center"></i> Buku Pelanggan
                    </a>
                </div>
            </details>

            <!-- Pribadi Dropdown -->
            <details class="group" <?= in_array($current_page, ['transaksi_umum.php', 'laporan_pribadi.php']) ? 'open' : '' ?>>
                <summary class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl text-blue-200 hover:bg-white/10 hover:text-white transition-colors cursor-pointer select-none">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-user w-4 text-center"></i> Pribadi
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
                </summary>
                <div class="flex flex-col gap-1 pl-4 pr-2 py-1 mt-1 border-l-2 border-white/10 ml-6">
                    <a href="transaksi_umum.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == 'transaksi_umum.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-pen-to-square w-4 mr-1 text-center"></i> Pencatatan
                    </a>
                    <a href="laporan_pribadi.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == 'laporan_pribadi.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-chart-line w-4 mr-1 text-center"></i> Laporan
                    </a>
                </div>
            </details>
            
            <!-- Pengaturan -->
            <a href="pengaturan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-colors <?= ($current_page == 'pengaturan.php') ? 'bg-white/20 text-white font-bold shadow-inner' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                <i class="fa-solid fa-gear w-4 text-center"></i> Pengaturan
            </a>
        </nav>
        
        <div class="mt-auto px-4 py-3 text-sm">
            <a href="logout.php" class="flex items-center gap-3 text-blue-200 hover:text-red-300 transition-colors">
                <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i> Keluar
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 w-full max-w-full pb-10">
        
        <header class="bg-gradient-to-r from-primary to-secondary rounded-3xl p-8 mb-10 shadow-lg shadow-blue-500/30">
            <h2 class="text-3xl font-bold text-white mb-2">Laporan Pribadi</h2>
            <p class="text-blue-100 text-sm">Ringkasan aktivitas keuangan Anda bulan ini (<?= $nama_bulan ?>).</p>
        </header>

        <!-- Ringkasan Bulan Ini -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="glass-card rounded-2xl p-6 border-t-4 border-t-emerald-500">
                <p class="text-slate-500 text-sm font-medium mb-1">Total Pemasukan Pribadi</p>
                <h3 class="text-2xl font-bold text-slate-800">Rp <?= number_format($pemasukan_bulan_ini, 0, ',', '.') ?></h3>
            </div>
            <div class="glass-card rounded-2xl p-6 border-t-4 border-t-red-500">
                <p class="text-slate-500 text-sm font-medium mb-1">Total Pengeluaran Pribadi</p>
                <h3 class="text-2xl font-bold text-slate-800">Rp <?= number_format($pengeluaran_bulan_ini, 0, ',', '.') ?></h3>
            </div>
            <div class="glass-card rounded-2xl p-6 border-t-4 <?= $saldo_bersih >= 0 ? 'border-t-blue-500' : 'border-t-orange-500' ?>">
                <p class="text-slate-500 text-sm font-medium mb-1">Sisa Uang Pribadi (Bulan Ini)</p>
                <h3 class="text-2xl font-bold text-slate-800">Rp <?= number_format($saldo_bersih, 0, ',', '.') ?></h3>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8">
            

            <!-- Tabel Riwayat Umum -->
            <div class="glass-card rounded-2xl p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Riwayat Keuangan Umum</h3>
                    <div class="text-xs text-purple-400 bg-purple-400/10 px-3 py-1 rounded-full"><i class="fa-solid fa-money-bill-transfer mr-1"></i>Pribadi / Umum</div>
                </div>
                
                <div class="overflow-x-auto max-h-[500px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-white z-10 shadow-md">
                            <tr class="text-slate-500 text-sm">
                                <th class="p-3 font-medium">Tgl</th>
                                <th class="p-3 font-medium">Keterangan</th>
                                <th class="p-3 font-medium text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_umum = mysqli_query($conn, "
                                SELECT tu.*, d.nama_dompet 
                                FROM transaksi_umum tu 
                                JOIN dompet d ON tu.id_dompet = d.id 
                                ORDER BY tu.tanggal DESC, tu.id DESC LIMIT 50
                            ");
                            if(mysqli_num_rows($q_umum) > 0) {
                                while($row = mysqli_fetch_assoc($q_umum)) {
                                    $is_masuk = $row['jenis'] == 'Pemasukan';
                                    $color = $is_masuk ? 'text-emerald-400' : 'text-red-400';
                                    $sign = $is_masuk ? '+' : '-';
                            ?>
                            <tr class="border-b border-slate-200/50 hover:bg-white/50 transition-colors">
                                <td class="p-3 text-xs text-slate-500"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td class="p-3">
                                    <div class="font-medium text-sm"><?= $row['keterangan'] ?></div>
                                    <div class="text-xs text-slate-500"><?= $row['nama_dompet'] ?></div>
                                </td>
                                <td class="p-3 text-sm font-medium text-right <?= $color ?>">
                                    <?= $sign ?> Rp <?= number_format($row['nominal'], 0, ',', '.') ?>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="3" class="p-6 text-center text-slate-500 text-sm">Belum ada data keuangan.</td></tr>';
                            } 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</body>
</html>
