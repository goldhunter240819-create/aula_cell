<?php
require 'koneksi.php';

$sukses_msg = "";
$error_msg = "";

// Jika tambah kategori
if(isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    $jenis = 'Produk';
    $tipe = 'Bisnis';
    
    if(mysqli_query($conn, "INSERT INTO kategori (nama_kategori, jenis, tipe) VALUES ('$nama', '$jenis', '$tipe')")) {
        $sukses_msg = "Kategori berhasil ditambahkan!";
    } else {
        $error_msg = "Gagal menambahkan kategori.";
    }
}

// Jika hapus kategori
if(isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if(mysqli_query($conn, "DELETE FROM kategori WHERE id = $id")) {
        $sukses_msg = "Kategori berhasil dihapus!";
    } else {
        $error_msg = "Gagal hapus. Mungkin kategori sedang digunakan di transaksi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Aula Cell</title>
    <link rel="icon" href="logoaula.jpeg" type="image/jpeg">
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
        .input-glass { background: #f8fafc; border: 1px solid #e2e8f0; }
        .input-glass:focus { outline: none; border-color: #1d4ed8; ring: 2px; ring-color: rgba(29, 78, 216, 0.2); }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex flex-col md:flex-row md:p-6 md:gap-8 overflow-x-hidden">

        <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-gradient-to-b from-blue-900 to-primary text-white p-5 flex flex-col gap-6 rounded-3xl md:h-[calc(100vh-3rem)] sticky top-6 z-20 shadow-2xl overflow-y-auto border border-blue-800/50">
        <?php $current_page = basename($_SERVER["PHP_SELF"]); ?>
        <style> details > summary { list-style: none; } details > summary::-webkit-details-marker { display: none; } </style>
        <div class="flex items-center gap-3 px-2">
            <div class="w-12 h-12 rounded-xl overflow-hidden shadow-lg shadow-primary/30 flex-shrink-0">
                <img src="logoaula.jpeg" alt="Aula Cell" class="w-full h-full object-cover">
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
        <header class="bg-gradient-to-r from-primary to-secondary rounded-3xl p-8 mb-10 shadow-lg shadow-blue-500/30">
            <h2 class="text-3xl font-bold text-white mb-2">Kelola Kategori</h2>
            <p class="text-blue-100 text-sm">Tambahkan kategori untuk produk bisnis dan transaksi pribadi Anda.</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Form Tambah -->
            <div class="glass-card rounded-2xl p-6 relative overflow-hidden h-fit">
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Tambah Kategori Baru</h3>
                <form method="POST" class="flex flex-col gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kategori</label>
                        <input type="text" name="nama_kategori" required placeholder="Cth: Pulsa Regular" class="w-full px-4 py-3 rounded-xl input-glass">
                    </div>

                    <button type="submit" name="tambah" class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-3.5 rounded-xl shadow-lg active:scale-95 transition-transform mt-2">
                        <i class="fa-solid fa-plus mr-2"></i> Tambahkan
                    </button>
                </form>
            </div>

            <!-- Tabel Daftar Kategori -->
            <div class="lg:col-span-2 glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Daftar Kategori Saat Ini</h3>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200">
                                <th class="py-3 px-2 font-bold text-slate-500 text-sm">Nama Kategori</th>
                                <th class="py-3 px-2 font-bold text-slate-500 text-sm text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_kat = mysqli_query($conn, "SELECT * FROM kategori ORDER BY tipe, jenis, nama_kategori");
                            while($row = mysqli_fetch_assoc($q_kat)):
                            ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-2 font-bold text-slate-800 text-sm"><?= $row['nama_kategori'] ?></td>

                                <td class="py-3 px-2 text-right">
                                    <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus kategori ini?')" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</body>
</html>
