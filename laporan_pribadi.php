<?php
require 'koneksi.php';

$sukses_msg = "";
$error_msg = "";

// ==========================================
// HANDLER: HAPUS TRANSAKSI UMUM
// ==========================================
if(isset($_POST['hapus_umum'])) {
    $id = (int)$_POST['id_transaksi'];
    mysqli_begin_transaction($conn);
    try {
        $q = mysqli_query($conn, "SELECT * FROM transaksi_umum WHERE id = $id");
        if(mysqli_num_rows($q) == 0) throw new Exception("Transaksi tidak ditemukan.");
        $trx = mysqli_fetch_assoc($q);
        
        // Kembalikan saldo: kebalikan dari jenis transaksi
        if($trx['jenis'] == 'Pemasukan') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo - {$trx['nominal']} WHERE id = {$trx['id_dompet']}");
        } else {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + {$trx['nominal']} WHERE id = {$trx['id_dompet']}");
        }
        
        mysqli_query($conn, "DELETE FROM transaksi_umum WHERE id = $id");
        
        mysqli_commit($conn);
        $sukses_msg = "Transaksi '{$trx['keterangan']}' berhasil dihapus dan saldo dikembalikan!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = $e->getMessage();
    }
}

// ==========================================
// HANDLER: EDIT TRANSAKSI UMUM
// ==========================================
if(isset($_POST['edit_umum'])) {
    $id = (int)$_POST['id_transaksi'];
    $nominal_baru = (int)$_POST['edit_nominal'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['edit_keterangan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['edit_tanggal']);
    
    mysqli_begin_transaction($conn);
    try {
        $q = mysqli_query($conn, "SELECT * FROM transaksi_umum WHERE id = $id");
        if(mysqli_num_rows($q) == 0) throw new Exception("Transaksi tidak ditemukan.");
        $old = mysqli_fetch_assoc($q);
        
        $selisih = $nominal_baru - $old['nominal'];
        
        if($old['jenis'] == 'Pemasukan') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + ($selisih) WHERE id = {$old['id_dompet']}");
        } else {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo - ($selisih) WHERE id = {$old['id_dompet']}");
        }
        
        mysqli_query($conn, "UPDATE transaksi_umum SET nominal=$nominal_baru, keterangan='$keterangan', tanggal='$tanggal' WHERE id = $id");
        
        mysqli_commit($conn);
        $sukses_msg = "Transaksi '$keterangan' berhasil diedit dan saldo disesuaikan!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = $e->getMessage();
    }
}

// ==========================================
// DATA LAPORAN
// ==========================================
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
            background-color: #e2e8f0;
            color: #1e293b;
        }
        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        }
        .modal-overlay { background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); }
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
        
        <header class="bg-gradient-to-r from-primary to-secondary rounded-3xl p-8 mb-10 shadow-lg shadow-blue-500/30">
            <h2 class="text-3xl font-bold text-white mb-2">Laporan Pribadi</h2>
            <p class="text-blue-100 text-sm">Ringkasan aktivitas keuangan Anda bulan ini (<?= $nama_bulan ?>).</p>
        </header>

        <!-- Notifikasi -->
        <?php if($sukses_msg): ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-3 text-sm font-medium">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <p><?= $sukses_msg ?></p>
            </div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-3 text-sm font-medium">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
                <p><?= $error_msg ?></p>
            </div>
        <?php endif; ?>

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
                    <h3 class="text-lg font-bold">Riwayat Keuangan Pribadi</h3>
                    <div class="text-xs text-purple-500 bg-purple-50 px-3 py-1 rounded-full"><i class="fa-solid fa-money-bill-transfer mr-1"></i>Pribadi / Umum</div>
                </div>
                
                <div class="overflow-x-auto max-h-[500px]">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-white z-10 shadow-md">
                            <tr class="text-slate-500 text-sm">
                                <th class="p-3 font-medium">Tgl</th>
                                <th class="p-3 font-medium">Keterangan</th>
                                <th class="p-3 font-medium">Jenis</th>
                                <th class="p-3 font-medium text-right">Nominal</th>
                                <th class="p-3 font-medium text-center">Aksi</th>
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
                                    $color = $is_masuk ? 'text-emerald-600' : 'text-red-600';
                                    $sign = $is_masuk ? '+' : '-';
                                    $badge_bg = $is_masuk ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600';
                            ?>
                            <tr class="border-b border-slate-200/50 hover:bg-slate-50/50 transition-colors group">
                                <td class="p-3 text-xs text-slate-500"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                <td class="p-3">
                                    <div class="font-medium text-sm"><?= $row['keterangan'] ?></div>
                                    <div class="text-xs text-slate-500"><?= $row['nama_dompet'] ?></div>
                                </td>
                                <td class="p-3">
                                    <span class="text-[10px] font-bold uppercase px-2 py-1 rounded-md <?= $badge_bg ?>"><?= $row['jenis'] ?></span>
                                </td>
                                <td class="p-3 text-sm font-semibold text-right <?= $color ?>">
                                    <?= $sign ?> Rp <?= number_format($row['nominal'], 0, ',', '.') ?>
                                </td>
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-1 opacity-50 group-hover:opacity-100 transition-opacity">
                                        <button type="button" onclick='openEditUmum(<?= json_encode($row) ?>)' class="w-8 h-8 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 flex items-center justify-center transition-colors" title="Edit">
                                            <i class="fa-solid fa-pen text-xs"></i>
                                        </button>
                                        <form method="POST" action="" onsubmit="return confirm('Yakin hapus transaksi ini? Saldo dompet akan dihitung ulang.');" class="inline">
                                            <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                                            <button type="submit" name="hapus_umum" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 flex items-center justify-center transition-colors" title="Hapus">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="5" class="p-6 text-center text-slate-500 text-sm">Belum ada data keuangan.</td></tr>';
                            } 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- ==========================================
         MODAL EDIT TRANSAKSI UMUM
         ========================================== -->
    <div id="modalEditUmum" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="modal-overlay absolute inset-0" onclick="closeModal('modalEditUmum')"></div>
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg relative z-10 mx-4 border border-slate-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-800"><i class="fa-solid fa-pen-to-square text-blue-500 mr-2"></i>Edit Transaksi</h3>
                <button onclick="closeModal('modalEditUmum')" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-400 hover:bg-slate-200 flex items-center justify-center"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="id_transaksi" id="eu_id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Tanggal</label>
                        <input type="date" name="edit_tanggal" id="eu_tanggal" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Nominal (Rp)</label>
                        <input type="number" name="edit_nominal" id="eu_nominal" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Keterangan</label>
                        <input type="text" name="edit_keterangan" id="eu_keterangan" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal('modalEditUmum')" class="flex-1 py-2.5 bg-slate-100 text-slate-500 font-semibold rounded-xl hover:bg-slate-200 transition-colors">Batal</button>
                    <button type="submit" name="edit_umum" class="flex-1 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/25 transition-all"><i class="fa-solid fa-check mr-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditUmum(data) {
            document.getElementById('eu_id').value = data.id;
            document.getElementById('eu_tanggal').value = data.tanggal;
            document.getElementById('eu_nominal').value = Math.round(data.nominal);
            document.getElementById('eu_keterangan').value = data.keterangan || '';
            document.getElementById('modalEditUmum').classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if(e.key === 'Escape') {
                closeModal('modalEditUmum');
            }
        });
    </script>

</body>
</html>
