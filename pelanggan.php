<?php
require 'koneksi.php';

$sukses_msg = "";
$error_msg = "";

// Jika tambah pelanggan
if(isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    
    // Cek nomor sudah ada belum
    $cek = mysqli_query($conn, "SELECT id FROM pelanggan WHERE no_hp = '$no_hp'");
    if(mysqli_num_rows($cek) > 0) {
        $error_msg = "Nomor HP sudah terdaftar!";
    } else {
        if(mysqli_query($conn, "INSERT INTO pelanggan (nama, no_hp) VALUES ('$nama', '$no_hp')")) {
            $sukses_msg = "Pelanggan berhasil ditambahkan!";
        } else {
            $error_msg = "Gagal menambahkan pelanggan.";
        }
    }
}

// Jika edit pelanggan
if(isset($_POST['edit'])) {
    $id = (int)$_POST['id_pelanggan'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    
    // Cek nomor sudah ada belum (kecuali nomor dia sendiri)
    $cek = mysqli_query($conn, "SELECT id FROM pelanggan WHERE no_hp = '$no_hp' AND id != $id");
    if(mysqli_num_rows($cek) > 0) {
        $error_msg = "Nomor HP sudah terdaftar pada kontak lain!";
    } else {
        if(mysqli_query($conn, "UPDATE pelanggan SET nama = '$nama', no_hp = '$no_hp' WHERE id = $id")) {
            $sukses_msg = "Pelanggan berhasil diedit!";
        } else {
            $error_msg = "Gagal mengedit pelanggan.";
        }
    }
}

// Jika hapus pelanggan
if(isset($_POST['hapus'])) {
    $id = (int)$_POST['id_pelanggan'];
    if(mysqli_query($conn, "DELETE FROM pelanggan WHERE id = $id")) {
        $sukses_msg = "Pelanggan berhasil dihapus!";
    } else {
        $error_msg = "Gagal menghapus pelanggan.";
    }
}

// Ambil data pelanggan
$q_pel = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama ASC");
$total_pelanggan = $q_pel ? mysqli_num_rows($q_pel) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Pelanggan - Aula Cell</title>
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
        
        <header class="bg-gradient-to-r from-blue-700 to-blue-500 rounded-3xl p-8 mb-10 shadow-lg shadow-blue-500/30 flex justify-between items-center text-white relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10">
                <i class="fa-solid fa-address-book text-9xl"></i>
            </div>
            <div class="relative z-10">
                <h2 class="text-3xl font-bold mb-2">Buku Pelanggan</h2>
                <p class="text-blue-100 text-sm">Kelola daftar kontak dan pelanggan setia konter Anda.</p>
            </div>
            <div class="relative z-10 text-right bg-white/10 p-4 rounded-2xl backdrop-blur-sm border border-white/20">
                <p class="text-xs text-blue-100 font-bold uppercase tracking-widest mb-1">Total Kontak</p>
                <h3 class="text-3xl font-black tracking-tight"><?= $total_pelanggan ?> <span class="text-sm">Orang</span></h3>
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
                <h3 class="font-bold text-lg text-slate-800">Daftar Pelanggan</h3>
                <button onclick="document.getElementById('tambahModal').classList.remove('hidden')" class="bg-primary hover:bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-md shadow-primary/30 transition-all active:scale-95 flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Tambah Baru
                </button>
            </div>

            <!-- Search -->
            <div class="relative mb-6">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchDesk" onkeyup="searchTable()" placeholder="Cari nama atau nomor HP pelanggan..." class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse" id="tabelPelanggan">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="py-4 px-4 font-bold text-slate-500 text-sm rounded-l-xl w-16">No</th>
                            <th class="py-4 px-4 font-bold text-slate-500 text-sm">Nama Pelanggan</th>
                            <th class="py-4 px-4 font-bold text-slate-500 text-sm">Nomor HP</th>
                            <th class="py-4 px-4 font-bold text-slate-500 text-sm rounded-r-xl text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($q_pel) > 0): ?>
                            <?php $no=1; while($row = mysqli_fetch_assoc($q_pel)): ?>
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors group pel-row">
                                <td class="py-4 px-4 text-sm font-bold text-slate-400"><?= $no++ ?></td>
                                <td class="py-4 px-4">
                                    <div class="text-sm font-bold text-slate-800 pel-nama"><?= $row['nama'] ?></div>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="text-sm font-bold text-slate-600 pel-hp"><?= $row['no_hp'] ?></div>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['nama'])) ?>', '<?= htmlspecialchars(addslashes($row['no_hp'])) ?>')" class="bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white w-8 h-8 rounded-lg font-bold text-xs transition-colors border border-blue-200 hover:border-blue-500 flex items-center justify-center">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <form method="POST" action="" onsubmit="return confirm('Yakin ingin menghapus pelanggan <?= addslashes($row['nama']) ?>?');">
                                            <input type="hidden" name="id_pelanggan" value="<?= $row['id'] ?>">
                                            <button type="submit" name="hapus" class="bg-red-50 text-red-500 hover:bg-red-500 hover:text-white w-8 h-8 rounded-lg font-bold text-xs transition-colors border border-red-200 hover:border-red-500 flex items-center justify-center">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr id="emptyRow">
                                <td colspan="4" class="py-12 text-center">
                                    <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                                        <i class="fa-solid fa-address-book"></i>
                                    </div>
                                    <h4 class="font-bold text-slate-800 mb-1">Belum Ada Pelanggan</h4>
                                    <p class="text-sm text-slate-500">Klik tombol Tambah Baru untuk menyimpan kontak.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
    
    <!-- Modal Tambah Desktop -->
    <div id="tambahModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('tambahModal').classList.add('hidden')"></div>
        <div class="bg-white w-full max-w-md rounded-2xl p-6 relative transform transition-transform shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-slate-800">Tambah Pelanggan Baru</h3>
                <button type="button" onclick="document.getElementById('tambahModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Pelanggan</label>
                    <input type="text" name="nama" required placeholder="Contoh: Budi Konter" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nomor Handphone</label>
                    <input type="text" name="no_hp" required placeholder="Contoh: 081234567890" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('tambahModal').classList.add('hidden')" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" name="tambah" class="flex-1 bg-primary hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-md shadow-primary/30 transition-all">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Desktop -->
    <div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('editModal').classList.add('hidden')"></div>
        <div class="bg-white w-full max-w-md rounded-2xl p-6 relative transform transition-transform shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-black text-slate-800">Edit Pelanggan</h3>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="id_pelanggan" id="edit_id_desk">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Pelanggan</label>
                    <input type="text" name="nama" id="edit_nama_desk" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nomor HP</label>
                    <input type="text" name="no_hp" id="edit_no_hp_desk" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3 rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" name="edit" class="flex-1 bg-primary hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-md shadow-primary/30 transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function searchTable() {
            let input = document.getElementById("searchDesk");
            let filter = input.value.toLowerCase();
            let trs = document.querySelectorAll(".pel-row");
            
            trs.forEach(tr => {
                let nama = tr.querySelector(".pel-nama").innerText.toLowerCase();
                let hp = tr.querySelector(".pel-hp").innerText.toLowerCase();
                if (nama.indexOf(filter) > -1 || hp.indexOf(filter) > -1) {
                    tr.style.display = "";
                } else {
                    tr.style.display = "none";
                }
            });
        }
        function openEditModal(id, nama, telp) {
            document.getElementById('edit_id_desk').value = id;
            document.getElementById('edit_nama_desk').value = nama;
            document.getElementById('edit_no_hp_desk').value = telp;
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</body>
</html>
