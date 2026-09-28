<?php
require 'koneksi.php';

$sukses_msg = "";
$error_msg = "";

// Jika tombol Lunasi ditekan
if(isset($_POST['lunasi'])) {
    $id_transaksi = (int)$_POST['id_transaksi'];
    
    mysqli_begin_transaction($conn);
    try {
        $q_trx = mysqli_query($conn, "SELECT * FROM transaksi_penjualan WHERE id = $id_transaksi AND status_pembayaran = 'Hutang'");
        if(mysqli_num_rows($q_trx) == 0) {
            throw new Exception("Data hutang tidak ditemukan atau sudah lunas.");
        }
        $trx = mysqli_fetch_assoc($q_trx);
        
        $harga_jual = $trx['harga_jual'];
        $id_dompet_pemasukan = $trx['id_dompet_pemasukan'];
        
        // 1. Update status jadi Lunas
        mysqli_query($conn, "UPDATE transaksi_penjualan SET status_pembayaran = 'Lunas' WHERE id = $id_transaksi");
        
        // 2. Tambahkan uang ke dompet pemasukan
        mysqli_query($conn, "UPDATE dompet SET saldo = saldo + $harga_jual WHERE id = $id_dompet_pemasukan");
        
        mysqli_commit($conn);
        $sukses_msg = "Mantap! Hutang '{$trx['produk']}' sebesar Rp " . number_format($harga_jual,0,',','.') . " berhasil dilunasi. Uang sudah masuk ke kas!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = $e->getMessage();
    }
}

// Ambil data semua hutang yang belum lunas
$q_hutang = mysqli_query($conn, "
    SELECT t.*, d.nama_dompet 
    FROM transaksi_penjualan t
    JOIN dompet d ON t.id_dompet_pemasukan = d.id
    WHERE t.status_pembayaran = 'Hutang'
    ORDER BY t.tanggal DESC
");

// Hitung total piutang (uang di luar)
$q_total = mysqli_query($conn, "SELECT SUM(harga_jual) as total FROM transaksi_penjualan WHERE status_pembayaran = 'Hutang'");
$total_piutang = mysqli_fetch_assoc($q_total)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Hutang - Aula Cell</title>
    <link rel="icon" href="aulalogo.png" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Inter', 'sans-serif'], }, colors: { primary: '#1d4ed8', secondary: '#0ea5e9', darkbg: '#0f172a', cardbg: '#1e293b', } } }
        }
    </script>
    <style>
        body { background-color: #e2e8f0; color: #1e293b; }
        .glass-card { background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
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
            <details class="group" <?= in_array($current_page, ['jual_pulsa.php', 'laporan_bisnis.php', 'transaksi_bisnis_umum.php', 'buku_hutang.php', 'kategori.php']) ? 'open' : '' ?>>
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
        
        <header class="bg-gradient-to-r from-red-600 to-rose-500 rounded-3xl p-8 mb-10 shadow-lg shadow-red-500/30 flex justify-between items-center text-white relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10">
                <i class="fa-solid fa-book text-9xl"></i>
            </div>
            <div class="relative z-10">
                <h2 class="text-3xl font-bold mb-2">Buku Hutang</h2>
                <p class="text-rose-100 text-sm">Daftar pelanggan yang masih belum bayar Lunas.</p>
            </div>
            <div class="relative z-10 text-right bg-white/10 p-4 rounded-2xl backdrop-blur-sm border border-white/20">
                <p class="text-xs text-rose-100 font-bold uppercase tracking-widest mb-1">Total Uang Nyangkut</p>
                <h3 class="text-3xl font-black tracking-tight">Rp <?= number_format($total_piutang,0,',','.') ?></h3>
            </div>
        </header>

        <?php if($sukses_msg): ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 font-bold flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <p><?= $sukses_msg ?></p>
            </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-600 border border-red-200 font-bold flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
                <p><?= $error_msg ?></p>
            </div>
        <?php endif; ?>

        <div class="glass-card rounded-2xl p-6 lg:p-8">
            <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
                <h3 class="font-bold text-lg text-slate-800">Daftar Tagihan Berjalan</h3>
                <span class="bg-red-50 text-red-500 px-3 py-1 rounded-full text-xs font-bold"><?= mysqli_num_rows($q_hutang) ?> Tagihan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="py-4 px-4 font-bold text-slate-500 text-sm rounded-l-xl">Tanggal</th>
                            <th class="py-4 px-4 font-bold text-slate-500 text-sm">Nama Transaksi</th>
                            <th class="py-4 px-4 font-bold text-slate-500 text-sm">Penerima Kas</th>
                            <th class="py-4 px-4 font-bold text-slate-500 text-sm">Nominal Tagihan</th>
                            <th class="py-4 px-4 font-bold text-slate-500 text-sm rounded-r-xl text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($q_hutang) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($q_hutang)): ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors group">
                                <td class="py-4 px-4">
                                    <div class="text-sm font-bold text-slate-800"><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
                                    <div class="text-xs text-slate-400"><?= date('H:i', strtotime($row['tanggal'])) ?></div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="text-sm font-bold text-slate-800"><?= $row['produk'] ?></div>
                                    <?php if($row['keterangan']): ?>
                                        <div class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-note-sticky text-slate-400 mr-1"></i> <?= $row['keterangan'] ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4 text-sm font-bold text-primary">
                                    <?= $row['nama_dompet'] ?>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="text-base font-black text-slate-800">Rp <?= number_format($row['harga_jual'],0,',','.') ?></div>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <form method="POST" action="" onsubmit="return confirm('Yakin tagihan ini sudah dibayar lunas?');">
                                        <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                                        <button type="submit" name="lunasi" class="bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white px-4 py-2 rounded-lg font-bold text-xs transition-colors border border-emerald-200 hover:border-emerald-500">
                                            <i class="fa-solid fa-hand-holding-dollar mr-1"></i> Tandai Lunas
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                                        <i class="fa-solid fa-face-smile"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-800 mb-1">Wah, Bebas Hutang!</h4>
                                    <p class="text-sm text-slate-500">Tidak ada pelanggan yang ngutang saat ini.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</body>
</html>
