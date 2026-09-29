<?php
require 'koneksi.php';

$sukses_msg = "";
$error_msg = "";

if(isset($_POST['submit'])) {
    $id_kategori = (int)$_POST['id_kategori'];
    $produk = mysqli_real_escape_string($conn, $_POST['produk']);
    $no_tujuan = mysqli_real_escape_string($conn, $_POST['no_tujuan']);
    $harga_modal = (int)$_POST['harga_modal'];
    $harga_jual = (int)$_POST['harga_jual'];
    $id_dompet_modal = (int)$_POST['id_dompet_modal'];
    $id_dompet_pemasukan = (int)$_POST['id_dompet_pemasukan'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $status_pembayaran = mysqli_real_escape_string($conn, $_POST['status_pembayaran']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal = date('Y-m-d H:i:s');
    
    // Pelanggan
    $simpan_pelanggan = isset($_POST['simpan_pelanggan']);
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan'] ?? '');
    
    mysqli_begin_transaction($conn);
    try {
        $q_insert = "INSERT INTO transaksi_penjualan 
                    (tanggal, id_kategori, produk, no_tujuan, harga_modal, harga_jual, id_dompet_modal, id_dompet_pemasukan, status, status_pembayaran, keterangan) 
                    VALUES 
                    ('$tanggal', $id_kategori, '$produk', '$no_tujuan', $harga_modal, $harga_jual, $id_dompet_modal, $id_dompet_pemasukan, '$status', '$status_pembayaran', '$keterangan')";
        
        if(!mysqli_query($conn, $q_insert)) {
            throw new Exception("Gagal input transaksi: " . mysqli_error($conn));
        }
        
        if($status == 'Sukses') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo - $harga_modal WHERE id = $id_dompet_modal");
            if($status_pembayaran == 'Lunas') {
                mysqli_query($conn, "UPDATE dompet SET saldo = saldo + $harga_jual WHERE id = $id_dompet_pemasukan");
            }
        }
        
        // Simpan Pelanggan Baru
        if($simpan_pelanggan && $no_tujuan != '' && $nama_pelanggan != '') {
            $cek_pelanggan = mysqli_query($conn, "SELECT id FROM pelanggan WHERE no_hp = '$no_tujuan'");
            if(mysqli_num_rows($cek_pelanggan) == 0) {
                mysqli_query($conn, "INSERT INTO pelanggan (nama, no_hp) VALUES ('$nama_pelanggan', '$no_tujuan')");
            }
        }
        
        mysqli_commit($conn);
        if($status_pembayaran == 'Hutang') {
            $sukses_msg = "Transaksi dicatat sebagai HUTANG! Modal dipotong, uang masuk belum ditambahkan.";
        } else {
            $sukses_msg = "Transaksi penjualan berhasil disimpan dan saldo otomatis diupdate!";
        }
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
    <title>Catat Bisnis - Aula Cell</title>
    <link rel="icon" href="aulalogo.png" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], },
                    colors: { primary: '#1d4ed8', secondary: '#0ea5e9', darkbg: '#0f172a', cardbg: '#1e293b', }
                }
            }
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
        <header class="bg-gradient-to-r from-primary to-secondary rounded-3xl p-8 mb-10 shadow-lg shadow-blue-500/30">
            <h2 class="text-3xl font-bold text-white mb-2">Pencatatan Bisnis</h2>
            <p class="text-blue-100 text-sm">Catat transaksi penjualan dengan Kategori dan status Hutang.</p>
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

        <div class="glass-card rounded-2xl p-6 lg:p-8 relative overflow-hidden">
            <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Kategori Produk</label>
                    <select name="id_kategori" required class="w-full px-4 py-3 rounded-xl input-glass">
                        <option value="0">Pulsa Regular</option>
                        <option value="1">Paket Data</option>
                        <option value="2">E-Wallet (DANA/OVO/GoPay)</option>
                        <option value="3">Token PLN</option>
                        <option value="4">Voucher Game</option>
                        <option value="5">Transfer Bank</option>
                        <option value="6">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Transaksi</label>
                    <input type="text" name="produk" required placeholder="Contoh: Beli pulsa Budi" class="w-full px-4 py-3 rounded-xl input-glass">
                </div>

                <div class="md:col-span-2">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-bold text-slate-700">Nomor Tujuan (Opsional)</label>
                        <button type="button" onclick="openPelangganModal()" class="text-xs font-bold bg-blue-100 text-primary px-3 py-1.5 rounded-full hover:bg-blue-200 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-address-book"></i> Pilih Kontak
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" name="no_tujuan" id="no_tujuan" placeholder="081234567890" class="w-full px-4 py-3 rounded-xl input-glass" oninput="checkPelanggan(this.value)">
                        <button type="button" id="clearBtn" onclick="clearNoTujuan()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-slate-200 text-slate-500 rounded-full flex items-center justify-center text-xs hover:bg-slate-300">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Checkbox Simpan Pelanggan -->
                    <div id="simpanPelangganBox" class="mt-3 hidden bg-blue-50/50 border border-blue-100 p-4 rounded-xl transition-all">
                        <label class="flex items-center gap-2 cursor-pointer w-max">
                            <input type="checkbox" name="simpan_pelanggan" id="chkSimpanPelanggan" onchange="toggleNamaPelanggan()" class="w-4 h-4 rounded text-primary focus:ring-primary border-slate-300">
                            <span class="text-sm font-bold text-slate-700">Simpan ke Buku Pelanggan</span>
                        </label>
                        <div id="inputNamaPelangganBox" class="mt-3 hidden">
                            <input type="text" name="nama_pelanggan" id="inputNamaPelanggan" placeholder="Nama Pelanggan Baru" class="w-full md:w-1/2 px-4 py-2.5 bg-white border border-blue-200 rounded-lg text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Harga Modal (Rp)</label>
                    <input type="number" name="harga_modal" id="harga_modal" required class="w-full px-4 py-3 rounded-xl input-glass" oninput="hitungLaba()">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Harga Jual (Rp)</label>
                    <input type="number" name="harga_jual" id="harga_jual" required class="w-full px-4 py-3 rounded-xl input-glass" oninput="hitungLaba()">
                </div>
                
                <div class="md:col-span-2">
                    <p class="text-sm font-bold text-slate-500">Estimasi Laba: <span id="laba_text" class="text-emerald-500 text-lg">Rp 0</span></p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Potong Saldo (Modal)</label>
                    <select name="id_dompet_modal" required class="w-full px-4 py-3 rounded-xl input-glass">
                        <?php 
                        $q = mysqli_query($conn, "SELECT * FROM dompet WHERE tipe='Bisnis'");
                        while($row = mysqli_fetch_assoc($q)) {
                            echo "<option value='{$row['id']}'>{$row['nama_dompet']} (Saldo: Rp " . number_format($row['saldo'],0,',','.') . ")</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Terima Pembayaran di (Laba)</label>
                    <select name="id_dompet_pemasukan" required class="w-full px-4 py-3 rounded-xl input-glass">
                        <?php 
                        $q2 = mysqli_query($conn, "SELECT * FROM dompet WHERE tipe='Bisnis'");
                        while($row = mysqli_fetch_assoc($q2)) {
                            $selected = ($row['nama_dompet'] == 'Laci Konter') ? 'selected' : '';
                            echo "<option value='{$row['id']}' $selected>{$row['nama_dompet']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Status Pembayaran</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer p-3 border border-slate-200 rounded-xl bg-slate-50 flex-1 hover:bg-slate-100 transition-colors">
                            <input type="radio" name="status_pembayaran" value="Lunas" checked class="w-4 h-4 text-primary">
                            <span class="font-bold text-sm">Cash / Lunas</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer p-3 border border-red-200 rounded-xl bg-red-50 flex-1 hover:bg-red-100 transition-colors">
                            <input type="radio" name="status_pembayaran" value="Hutang" class="w-4 h-4 text-red-500">
                            <span class="font-bold text-sm text-red-600">Hutang</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Status Transaksi</label>
                    <div class="flex gap-4 h-11 items-center">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="Sukses" checked class="w-4 h-4 text-primary">
                            <span class="font-bold text-sm">Sukses</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="Pending" class="w-4 h-4 text-orange-400">
                            <span class="font-bold text-sm">Pending</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="status" value="Gagal" class="w-4 h-4 text-red-500">
                            <span class="font-bold text-sm">Gagal</span>
                        </label>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Keterangan (Opsional)</label>
                    <textarea name="keterangan" rows="2" class="w-full px-4 py-3 rounded-xl input-glass"></textarea>
                </div>

                <div class="md:col-span-2 mt-4">
                    <button type="submit" name="submit" class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg active:scale-95 transition-transform">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function hitungLaba() {
            const m = parseInt(document.getElementById('harga_modal').value) || 0;
            const j = parseInt(document.getElementById('harga_jual').value) || 0;
            const laba = j - m;
            document.getElementById('laba_text').innerText = 'Rp ' + laba.toLocaleString('id-ID');
            if (laba < 0) {
                document.getElementById('laba_text').classList.remove('text-emerald-500');
                document.getElementById('laba_text').classList.add('text-red-500');
            } else {
                document.getElementById('laba_text').classList.remove('text-red-500');
                document.getElementById('laba_text').classList.add('text-emerald-500');
            }
        }
    </script>
</body>
<!-- Modal Buku Pelanggan Desktop -->
<div id="pelangganModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closePelangganModal()"></div>
    <div class="bg-white w-full max-w-md rounded-2xl p-6 relative transform transition-transform shadow-2xl flex flex-col max-h-[80vh]">
        <div class="flex justify-between items-center mb-4 flex-shrink-0">
            <h3 class="text-xl font-black text-slate-800">Buku Pelanggan</h3>
            <button type="button" onclick="closePelangganModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        
        <div class="relative mb-4 flex-shrink-0">
            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" id="searchPelanggan" oninput="filterPelanggan()" placeholder="Cari nama atau nomor..." class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
        </div>
        
        <div class="flex-1 overflow-y-auto min-h-0 flex flex-col gap-2 pr-1" id="listPelanggan">
            <?php
            $q_pel_desk = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama ASC");
            $ada_pelanggan_desk = false;
            while($p = mysqli_fetch_assoc($q_pel_desk)):
                $ada_pelanggan_desk = true;
            ?>
            <button type="button" onclick="pilihPelanggan('<?= $p['no_hp'] ?>', '<?= addslashes($p['nama']) ?>')" class="pelanggan-item flex items-center justify-between p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition-all bg-white text-left group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-primary flex items-center justify-center font-bold group-hover:bg-primary group-hover:text-white transition-colors">
                        <?= strtoupper(substr($p['nama'],0,1)) ?>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm pel-nama"><?= $p['nama'] ?></h4>
                        <p class="text-xs text-slate-500 font-semibold pel-hp"><?= $p['no_hp'] ?></p>
                    </div>
                </div>
                <i class="fa-solid fa-check text-primary opacity-0 group-hover:opacity-100 transition-opacity"></i>
            </button>
            <?php endwhile; ?>
            
            <?php if(!$ada_pelanggan_desk): ?>
            <div class="text-center py-8 text-slate-400">
                <i class="fa-solid fa-address-book text-4xl mb-3 text-slate-300"></i>
                <p class="text-sm font-bold">Belum ada pelanggan</p>
                <p class="text-xs mt-1">Simpan nomor pelanggan saat transaksi</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const dbPelanggan = [
        <?php
        mysqli_data_seek($q_pel_desk, 0);
        while($p = mysqli_fetch_assoc($q_pel_desk)){
            echo "'" . $p['no_hp'] . "',";
        }
        ?>
    ];

    function openPelangganModal() {
        document.getElementById('pelangganModal').classList.remove('hidden');
        document.getElementById('searchPelanggan').value = '';
        filterPelanggan();
    }
    function closePelangganModal() {
        document.getElementById('pelangganModal').classList.add('hidden');
    }
    function filterPelanggan() {
        const term = document.getElementById('searchPelanggan').value.toLowerCase();
        const items = document.querySelectorAll('.pelanggan-item');
        items.forEach(el => {
            const nama = el.querySelector('.pel-nama').innerText.toLowerCase();
            const hp = el.querySelector('.pel-hp').innerText.toLowerCase();
            el.style.display = (nama.includes(term) || hp.includes(term)) ? 'flex' : 'none';
        });
    }
    function pilihPelanggan(hp, nama) {
        document.getElementById('no_tujuan').value = hp;
        closePelangganModal();
        checkPelanggan(hp);
    }
    function clearNoTujuan() {
        document.getElementById('no_tujuan').value = '';
        checkPelanggan('');
        document.getElementById('no_tujuan').focus();
    }
    function checkPelanggan(val) {
        const box = document.getElementById('simpanPelangganBox');
        const clearBtn = document.getElementById('clearBtn');
        clearBtn.classList.toggle('hidden', val.length === 0);

        if(val.length >= 10 && !dbPelanggan.includes(val)) {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
            document.getElementById('chkSimpanPelanggan').checked = false;
            toggleNamaPelanggan();
        }
    }
    function toggleNamaPelanggan() {
        const chk = document.getElementById('chkSimpanPelanggan');
        const box = document.getElementById('inputNamaPelangganBox');
        const input = document.getElementById('inputNamaPelanggan');
        if(chk.checked) {
            box.classList.remove('hidden');
            input.required = true;
            input.focus();
        } else {
            box.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    }
</script>
</html>