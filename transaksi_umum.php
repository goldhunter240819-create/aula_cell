<?php
require 'koneksi.php';

$sukses_msg = "";
$error_msg = "";

// Jika form disubmit
if(isset($_POST['submit'])) {
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
    $id_kategori = (int)$_POST['id_kategori'];
    $id_dompet = (int)$_POST['id_dompet'];
    $nominal = (int)$_POST['nominal'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    
    // Mulai transaksi database
    mysqli_begin_transaction($conn);
    try {
        // 1. Insert ke tabel transaksi_umum
        $q_insert = "INSERT INTO transaksi_umum 
                    (tanggal, jenis, id_kategori, id_dompet, nominal, keterangan) 
                    VALUES 
                    ('$tanggal', '$jenis', $id_kategori, $id_dompet, $nominal, '$keterangan')";
        
        if(!mysqli_query($conn, $q_insert)) {
            throw new Exception("Gagal input transaksi: " . mysqli_error($conn));
        }
        
        // 2. Jika sukses, update saldo dompet
        if($jenis == 'Pemasukan') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + $nominal WHERE id = $id_dompet");
        } else { // Pengeluaran
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo - $nominal WHERE id = $id_dompet");
        }
        
        mysqli_commit($conn);
        $sukses_msg = "Transaksi $jenis sebesar Rp " . number_format($nominal,0,',','.') . " berhasil dicatat!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keuangan Pribadi - Aula Cell</title>
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
        .input-glass { background: #ffffff; border: 1px solid rgba(0, 0, 0, 0.05); color: #0f172a; }
        .input-glass:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); }
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
            <h2 class="text-3xl font-bold text-white mb-2">Keuangan Pribadi</h2>
            <p class="text-blue-100 text-sm">Catat uang jajan, belanja, gaji masuk, atau mutasi dana.</p>
        </header>

        <?php if($sukses_msg): ?>
            <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <p><?= $sukses_msg ?></p>
            </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-xl"></i>
                <p><?= $error_msg ?></p>
            </div>
        <?php endif; ?>

        <div class="glass-card rounded-2xl p-6 lg:p-8 relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-primary/5 rounded-full blur-3xl"></div>
            
            <form action="" method="POST" class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Jenis Transaksi</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="jenis" value="Pengeluaran" checked class="w-4 h-4 text-red-400 bg-white border-slate-300 focus:ring-red-400 focus:ring-2">
                            <span class="text-red-400 font-medium">Uang Keluar / Pengeluaran</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="jenis" value="Pemasukan" class="w-4 h-4 text-emerald-400 bg-white border-slate-300 focus:ring-emerald-400 focus:ring-2">
                            <span class="text-emerald-400 font-medium">Uang Masuk / Pemasukan</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Tanggal</label>
                    <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>" class="w-full px-4 py-3 rounded-xl input-glass transition-all [&::-webkit-calendar-picker-indicator]:filter [&::-webkit-calendar-picker-indicator]:invert">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nominal (Rp)</label>
                    <input type="hidden" name="nominal" id="nominalDesktopPribadi">
                    <input type="text" inputmode="numeric" required placeholder="10.000" oninput="formatNominal(this, 'nominalDesktopPribadi')" class="w-full px-4 py-3 rounded-xl input-glass transition-all text-lg font-bold">
                </div>
                

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Kategori</label>
                    <select name="id_kategori" required class="w-full px-4 py-3 rounded-xl input-glass transition-all [&>option]:bg-white">
                        <option value="">-- Pilih Kategori --</option>
                        <optgroup label="Pemasukan">
                            <?php 
                            $q_kat_in = mysqli_query($conn, "SELECT * FROM kategori WHERE tipe = 'Pribadi' AND jenis = 'Pemasukan' ORDER BY nama_kategori");
                            while($row = mysqli_fetch_assoc($q_kat_in)) {
                                echo "<option value='{$row['id']}'>{$row['nama_kategori']}</option>";
                            }
                            ?>
                        </optgroup>
                        <optgroup label="Pengeluaran">
                            <?php 
                            $q_kat_out = mysqli_query($conn, "SELECT * FROM kategori WHERE tipe = 'Pribadi' AND jenis = 'Pengeluaran' ORDER BY nama_kategori");
                            while($row = mysqli_fetch_assoc($q_kat_out)) {
                                echo "<option value='{$row['id']}'>{$row['nama_kategori']}</option>";
                            }
                            ?>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Dompet / Rekening</label>
                    <select name="id_dompet" required class="w-full px-4 py-3 rounded-xl input-glass transition-all [&>option]:bg-white">
                        <?php 
                        $q_dompet = mysqli_query($conn, "SELECT * FROM dompet ORDER BY tipe, nama_dompet");
                        while($row = mysqli_fetch_assoc($q_dompet)) {
                            $selected = ($row['nama_dompet'] == 'Kas Pribadi') ? 'selected' : '';
                            echo "<option value='{$row['id']}' $selected>{$row['nama_dompet']} ({$row['tipe']}) - Rp " . number_format($row['saldo'],0,',','.') . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Keterangan / Catatan</label>
                    <input type="text" name="keterangan" required placeholder="Contoh: Beli bensin motor, Uang jajan hari ini, dll" class="w-full px-4 py-3 rounded-xl input-glass transition-all">
                </div>

                <div class="md:col-span-2 mt-4">
                    <button type="submit" name="submit" class="w-full bg-primary hover:bg-blue-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-primary/30 transition-all transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
<script>
function formatNominal(el, hiddenId) {
    let raw = el.value.replace(/\D/g, '');
    document.getElementById(hiddenId).value = raw;
    el.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
</script>
</html>
