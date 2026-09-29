<?php
require '../koneksi.php';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Buku Pelanggan - Aula Cell</title>
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <meta name="theme-color" content="#2563eb">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ["Nunito", "sans-serif"], }, colors: { primary: "#2563eb", secondary: "#3b82f6", bglight: "#f8fafc", } } } }
    </script>
    <style>
        body { background-color: #0f172a; color: #334155; -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 1rem); }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        .header-curve { border-bottom-left-radius: 2.5rem; border-bottom-right-radius: 2.5rem; box-shadow: 0 4px 20px -2px rgba(37, 99, 235, 0.3); }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 m-0 p-0 overflow-hidden">
    <div class="fixed inset-0 w-full max-w-[28rem] mx-auto bg-slate-50 shadow-2xl overflow-hidden flex flex-col">
        
        <div class="flex-1 overflow-y-auto pb-32 overflow-x-hidden pb-24">
            
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 header-curve pt-10 pb-16 px-6 relative text-white">
                <div class="flex items-center gap-4 mb-6">
                    <a href="pengaturan.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm active:scale-95"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="text-xl font-extrabold tracking-tight">Buku Pelanggan</h1>
                </div>
                
                <p class="text-xs text-blue-100 font-bold uppercase tracking-widest mb-1">Total Kontak Tersimpan</p>
                <h2 class="text-3xl font-black tracking-tight"><?= $total_pelanggan ?> <span class="text-lg font-bold">Orang</span></h2>
                
                <!-- Search Input -->
                <div class="absolute -bottom-6 left-6 right-6">
                    <div class="bg-white rounded-2xl p-2 shadow-lg flex items-center gap-2 border border-slate-100 text-slate-700">
                        <i class="fa-solid fa-search text-slate-400 pl-3"></i>
                        <input type="text" id="searchPelanggan" oninput="filterPelanggan()" placeholder="Cari nama pelanggan..." class="w-full bg-transparent border-none focus:outline-none text-sm font-bold py-2">
                    </div>
                </div>
            </div>
            
            <main class="px-5 pt-12 pb-10">
                <?php if($sukses_msg): ?>
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center gap-3 text-sm font-bold">
                        <i class="fa-solid fa-circle-check text-xl"></i> <?= $sukses_msg ?>
                    </div>
                <?php endif; ?>
                <?php if($error_msg): ?>
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-600 flex items-center gap-3 text-sm font-bold">
                        <i class="fa-solid fa-circle-exclamation text-xl"></i> <?= $error_msg ?>
                    </div>
                <?php endif; ?>

                <button onclick="document.getElementById('tambahModal').classList.remove('hidden')" class="w-full bg-primary/10 border border-primary/20 text-primary py-3.5 rounded-[1.25rem] font-bold active:scale-95 transition-transform mb-6 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> Tambah Pelanggan Baru
                </button>

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-extrabold text-slate-800">Daftar Kontak</h3>
                </div>

                <div class="flex flex-col gap-3" id="listPelanggan">
                    <?php if($total_pelanggan > 0): ?>
                        <?php while($p = mysqli_fetch_assoc($q_pel)): ?>
                            <div class="pelanggan-item bg-white p-4 rounded-[1.25rem] border border-slate-100 shadow-[0_4px_20px_rgba(0,0,0,0.03)] flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center font-bold text-lg">
                                        <?= strtoupper(substr($p['nama'],0,1)) ?>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm text-slate-800 pel-nama"><?= $p['nama'] ?></h4>
                                        <p class="text-xs font-semibold text-slate-500"><?= $p['no_hp'] ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="openEditModal(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', '<?= htmlspecialchars(addslashes($p['no_hp'])) ?>')" class="w-8 h-8 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center active:scale-95 transition-transform">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </button>
                                    <form method="POST" action="" onsubmit="return confirm('Hapus pelanggan <?= addslashes($p['nama']) ?>?');">
                                        <input type="hidden" name="id_pelanggan" value="<?= $p['id'] ?>">
                                        <button type="submit" name="hapus" class="w-8 h-8 rounded-full bg-red-50 text-red-500 flex items-center justify-center active:scale-95 transition-transform">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
                                <i class="fa-solid fa-address-book text-2xl"></i>
                            </div>
                            <h4 class="font-bold text-slate-700">Belum ada pelanggan</h4>
                            <p class="text-xs text-slate-500 mt-1">Tambahkan kontak pelanggan setia Anda.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
        
        <?php include 'footer.php'; ?>
    </div>
    
    <!-- Modal Tambah -->
    <div id="tambahModal" class="hidden fixed inset-0 z-[100] flex items-end justify-center sm:items-center">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('tambahModal').classList.add('hidden')"></div>
        <div class="bg-white w-full md:w-[400px] rounded-t-[2rem] md:rounded-[2rem] p-6 relative transform transition-transform shadow-2xl pb-safe">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-6"></div>
            <h3 class="text-xl font-black text-slate-800 text-center mb-6">Tambah Pelanggan</h3>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Nama Pelanggan</label>
                    <input type="text" name="nama" required placeholder="Cth: Budi Warung" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div class="mb-6">
                    <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Nomor HP</label>
                    <input type="text" name="no_hp" required placeholder="Cth: 08123456789" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <button type="submit" name="tambah" class="w-full bg-primary text-white font-black py-3.5 rounded-xl shadow-[0_8px_20px_rgba(37,99,235,0.3)] active:scale-95 transition-transform mb-3">
                    Simpan Kontak
                </button>
                <button type="button" onclick="document.getElementById('tambahModal').classList.add('hidden')" class="w-full bg-slate-100 text-slate-500 font-bold py-3.5 rounded-xl active:scale-95 transition-transform">
                    Batal
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="hidden fixed inset-0 z-[100] flex items-end justify-center sm:items-center">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('editModal').classList.add('hidden')"></div>
        <div class="bg-white w-full md:w-[400px] rounded-t-[2rem] md:rounded-[2rem] p-6 relative transform transition-transform shadow-2xl pb-safe">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-6"></div>
            <h3 class="text-xl font-black text-slate-800 text-center mb-6">Edit Pelanggan</h3>
            
            <form method="POST" action="">
                <input type="hidden" name="id_pelanggan" id="edit_id">
                <div class="mb-4">
                    <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Nama Pelanggan</label>
                    <input type="text" name="nama" id="edit_nama" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <div class="mb-6">
                    <label class="block text-[10px] font-extrabold text-slate-400 uppercase tracking-widest mb-1.5">Nomor HP</label>
                    <input type="text" name="no_hp" id="edit_no_hp" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
                <button type="submit" name="edit" class="w-full bg-primary text-white font-black py-3.5 rounded-xl shadow-[0_8px_20px_rgba(37,99,235,0.3)] active:scale-95 transition-transform mb-3">
                    Simpan Perubahan
                </button>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="w-full bg-slate-100 text-slate-500 font-bold py-3.5 rounded-xl active:scale-95 transition-transform">
                    Batal
                </button>
            </form>
        </div>
    </div>

    <script>
        function filterPelanggan() {
            const term = document.getElementById('searchPelanggan').value.toLowerCase();
            const items = document.querySelectorAll('.pelanggan-item');
            items.forEach(el => {
                const nama = el.querySelector('.pel-nama').innerText.toLowerCase();
                if(nama.includes(term)) {
                    el.style.display = 'flex';
                } else {
                    el.style.display = 'none';
                }
            });
        }
        function openEditModal(id, nama, telp) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_no_hp').value = telp;
            document.getElementById('editModal').classList.remove('hidden');
        }
    </script>
</body>
</html>
