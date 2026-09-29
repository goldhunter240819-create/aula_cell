<?php
require 'koneksi.php';

// Ambil Saldo Pribadi
$q_saldo_pribadi = mysqli_query($conn, "SELECT SUM(saldo) as total FROM dompet WHERE tipe = 'Pribadi'");
$saldo_pribadi = mysqli_fetch_assoc($q_saldo_pribadi)['total'] ?? 0;

// Ambil Saldo Bisnis
$q_saldo_bisnis = mysqli_query($conn, "SELECT SUM(saldo) as total FROM dompet WHERE tipe = 'Bisnis'");
$saldo_bisnis = mysqli_fetch_assoc($q_saldo_bisnis)['total'] ?? 0;

// Ambil Laba Bulan Ini
$bulan_ini = date('Y-m');
$q_laba = mysqli_query($conn, "SELECT SUM(laba) as total_laba FROM transaksi_penjualan WHERE status = 'Sukses' AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'");
$laba_bulan_ini = mysqli_fetch_assoc($q_laba)['total_laba'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aula Cell & Personal Finance</title>
    <link rel="icon" href="aulalogo.png" type="image/jpeg">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#1d4ed8', // blue-700 (Brand)
                        secondary: '#0ea5e9', // sky-500 (Brand)
                        darkbg: '#0f172a', // slate-900
                        cardbg: '#1e293b', // slate-800
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
    <aside class="w-full md:w-64 bg-gradient-to-b from-blue-900 to-primary text-white p-4 flex flex-col gap-4 rounded-3xl md:h-[calc(100vh-3rem)] sticky top-6 z-20 shadow-2xl overflow-y-auto border border-blue-800/50 no-scrollbar">
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

        <nav class="flex-1 flex flex-col gap-1 mt-2 text-sm">
            <a href="index.php" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors <?= ($current_page == 'index.php') ? 'bg-white/20 text-white font-bold shadow-inner' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                <i class="fa-solid fa-house w-4 text-center"></i> Dashboard
            </a>
            
            <!-- Bisnis Dropdown -->
            <details class="group" <?= in_array($current_page, ['jual_pulsa.php', 'laporan_bisnis.php', 'transaksi_bisnis_umum.php', 'buku_hutang.php', 'kategori.php', 'pelanggan.php']) ? 'open' : '' ?>>
                <summary class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-xl text-blue-200 hover:bg-white/10 hover:text-white transition-colors cursor-pointer select-none">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-store w-4 text-center"></i> Bisnis
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
                </summary>
                <div class="flex flex-col gap-1 pl-4 pr-2 py-1 mt-0.5 border-l-2 border-white/10 ml-6">
                    <a href="jual_pulsa.php" class="text-sm py-1.5 px-3 rounded-lg transition-colors <?= ($current_page == 'jual_pulsa.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-pen-to-square w-4 mr-1 text-center"></i> Jual Beli Pulsa
                    </a>
                    <a href="transaksi_bisnis_umum.php" class="text-sm py-1.5 px-3 rounded-lg transition-colors <?= ($current_page == 'transaksi_bisnis_umum.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-briefcase w-4 mr-1 text-center"></i> Operasional Bisnis
                    </a>
                    <a href="kategori.php" class="text-sm py-1.5 px-3 rounded-lg transition-colors <?= ($current_page == 'kategori.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-tags w-4 mr-1 text-center"></i> Kategori Produk
                    </a>
                    <a href="laporan_bisnis.php" class="text-sm py-1.5 px-3 rounded-lg transition-colors <?= ($current_page == 'laporan_bisnis.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-chart-line w-4 mr-1 text-center"></i> Laporan
                    </a>
                    <a href="buku_hutang.php" class="text-sm py-1.5 px-3 rounded-lg transition-colors <?= ($current_page == 'buku_hutang.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-book w-4 mr-1 text-center"></i> Buku Hutang
                    </a>
                    <a href="pelanggan.php" class="text-sm py-1.5 px-3 rounded-lg transition-colors <?= ($current_page == 'pelanggan.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-address-book w-4 mr-1 text-center"></i> Buku Pelanggan
                    </a>
                </div>
            </details>

            <!-- Pribadi Dropdown -->
            <details class="group" <?= in_array($current_page, ['transaksi_umum.php', 'laporan_pribadi.php']) ? 'open' : '' ?>>
                <summary class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-xl text-blue-200 hover:bg-white/10 hover:text-white transition-colors cursor-pointer select-none">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-user w-4 text-center"></i> Pribadi
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
                </summary>
                <div class="flex flex-col gap-1 pl-4 pr-2 py-1 mt-0.5 border-l-2 border-white/10 ml-6">
                    <a href="transaksi_umum.php" class="text-sm py-1.5 px-3 rounded-lg transition-colors <?= ($current_page == 'transaksi_umum.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-pen-to-square w-4 mr-1 text-center"></i> Pencatatan
                    </a>
                    <a href="laporan_pribadi.php" class="text-sm py-1.5 px-3 rounded-lg transition-colors <?= ($current_page == 'laporan_pribadi.php') ? 'bg-white/20 text-white font-bold' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
                        <i class="fa-solid fa-chart-line w-4 mr-1 text-center"></i> Laporan
                    </a>
                </div>
            </details>
            
            <!-- Pengaturan -->
            <a href="pengaturan.php" class="flex items-center gap-3 px-3 py-2 rounded-xl transition-colors <?= ($current_page == 'pengaturan.php') ? 'bg-white/20 text-white font-bold shadow-inner' : 'text-blue-200 hover:bg-white/10 hover:text-white' ?>">
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
        
        <header class="bg-gradient-to-r from-primary to-secondary rounded-3xl p-8 mb-10 flex flex-col md:flex-row justify-between items-start md:items-center shadow-lg shadow-blue-500/30">
            <div>
                <h2 class="text-3xl font-bold text-white mb-2">Ringkasan Keuangan</h2>
                <p class="text-blue-100 text-sm">Pantau arus kas pribadi dan konter Anda di sini.</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="jual_pulsa.php" class="bg-white hover:bg-slate-50 text-blue-600 px-6 py-3 rounded-xl font-bold shadow-md transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Bisnis
                </a>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <!-- Card 1 -->
            <div class="glass-card rounded-2xl p-6 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/20 rounded-full blur-2xl group-hover:bg-emerald-500/30 transition-all"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-medium mb-1">Saldo Bisnis (Modal)</p>
                        <h3 class="text-3xl font-bold text-slate-800">Rp <?= number_format($saldo_bisnis, 0, ',', '.') ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                        <i class="fa-solid fa-store text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-emerald-400 flex items-center gap-1">
                    <i class="fa-solid fa-circle-check"></i> Siap untuk transaksi
                </div>
            </div>

            <!-- Card 2 -->
            <div class="glass-card rounded-2xl p-6 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-primary/20 rounded-full blur-2xl group-hover:bg-primary/30 transition-all"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-medium mb-1">Saldo Pribadi</p>
                        <h3 class="text-3xl font-bold text-slate-800">Rp <?= number_format($saldo_pribadi, 0, ',', '.') ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                        <i class="fa-solid fa-wallet text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-slate-500 flex items-center gap-1">
                    <i class="fa-solid fa-info-circle"></i> Gabungan semua dompet pribadi
                </div>
            </div>

            <!-- Card 3 -->
            <div class="glass-card rounded-2xl p-6 relative overflow-hidden group">
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-500/20 rounded-full blur-2xl group-hover:bg-purple-500/30 transition-all"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-slate-500 text-sm font-medium mb-1">Laba Pulsa Bulan Ini</p>
                        <h3 class="text-3xl font-bold text-slate-800">Rp <?= number_format($laba_bulan_ini, 0, ',', '.') ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-purple-500/10 flex items-center justify-center text-purple-400">
                        <i class="fa-solid fa-chart-line text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 text-xs text-purple-400 flex items-center gap-1">
                    <i class="fa-solid fa-arrow-trend-up"></i> Terus tingkatkan penjualan!
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Table Transaksi -->
            <div class="glass-card rounded-2xl p-6 lg:col-span-2">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Transaksi Penjualan Terakhir</h3>
                    <a href="#" class="text-sm text-primary hover:underline">Lihat Semua</a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 text-sm">
                                <th class="pb-3 font-medium">Tanggal</th>
                                <th class="pb-3 font-medium">Produk</th>
                                <th class="pb-3 font-medium">Laba</th>
                                <th class="pb-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_recent = mysqli_query($conn, "SELECT * FROM transaksi_penjualan ORDER BY tanggal DESC LIMIT 5");
                            if(mysqli_num_rows($q_recent) > 0) {
                                while($row = mysqli_fetch_assoc($q_recent)) {
                                    $status_color = $row['status'] == 'Sukses' ? 'text-emerald-400 bg-emerald-400/10' : ($row['status'] == 'Pending' ? 'text-orange-400 bg-orange-400/10' : 'text-red-400 bg-red-400/10');
                            ?>
                            <tr class="border-b border-slate-200/50 hover:bg-white/50 transition-colors">
                                <td class="py-4 text-sm"><?= date('d M Y, H:i', strtotime($row['tanggal'])) ?></td>
                                <td class="py-4">
                                    <div class="font-medium"><?= $row['produk'] ?></div>
                                    <div class="text-xs text-slate-500"><?= $row['no_tujuan'] ?></div>
                                </td>
                                <td class="py-4 text-sm text-emerald-400 font-medium">+Rp <?= number_format($row['laba'], 0, ',', '.') ?></td>
                                <td class="py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?= $status_color ?>"><?= $row['status'] ?></span>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500 text-sm">Belum ada transaksi penjualan.</td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- List Dompet -->
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold mb-6">Daftar Dompet & Saldo</h3>
                <div class="flex flex-col gap-4">
                    <?php
                    $q_dompet = mysqli_query($conn, "SELECT * FROM dompet ORDER BY tipe, nama_dompet");
                    while($row = mysqli_fetch_assoc($q_dompet)) {
                        $icon = $row['tipe'] == 'Bisnis' ? 'fa-store' : 'fa-wallet';
                        $color = $row['tipe'] == 'Bisnis' ? 'text-emerald-400 bg-emerald-400/10' : 'text-primary bg-primary/10';
                    ?>
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-white/50 transition-colors border border-slate-200/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg <?= $color ?> flex items-center justify-center">
                                <i class="fa-solid <?= $icon ?>"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-slate-800"><?= $row['nama_dompet'] ?></h4>
                                <p class="text-xs text-blue-200"><?= $row['tipe'] ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold">Rp <?= number_format($row['saldo'], 0, ',', '.') ?></p>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
