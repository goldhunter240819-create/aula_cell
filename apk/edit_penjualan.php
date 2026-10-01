<?php
require '../koneksi.php';

$sukses_msg = "";
$error_msg = "";

if(!isset($_GET['id'])) {
    header("Location: riwayat_bisnis.php");
    exit;
}
$id = (int)$_GET['id'];

// Get existing data
$q_exist = mysqli_query($conn, "SELECT * FROM transaksi_penjualan WHERE id = $id");
if(mysqli_num_rows($q_exist) == 0) {
    header("Location: riwayat_bisnis.php");
    exit;
}
$old = mysqli_fetch_assoc($q_exist);

if(isset($_POST['submit'])) {
    $id_kategori = (int)$_POST['id_kategori'];
    $produk = mysqli_real_escape_string($conn, $_POST['produk']);
    $no_tujuan = mysqli_real_escape_string($conn, $_POST['no_tujuan']);
    $harga_modal = (int)$_POST['harga_modal'];
    $harga_jual = (int)$_POST['harga_jual'];
    $id_dompet_modal = (int)$_POST['id_dompet_modal'];
    $id_dompet_pemasukan = (int)$_POST['id_dompet_pemasukan'];
    $status_pembayaran = mysqli_real_escape_string($conn, $_POST['status_pembayaran']); // Lunas / Hutang
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    
    // Pelanggan
    $simpan_pelanggan = isset($_POST['simpan_pelanggan']);
    $nama_pelanggan = mysqli_real_escape_string($conn, $_POST['nama_pelanggan'] ?? '');
    
    mysqli_begin_transaction($conn);
    try {
        // 1. KEMBALIKAN saldo lama
        mysqli_query($conn, "UPDATE dompet SET saldo = saldo + {$old['harga_modal']} WHERE id = {$old['id_dompet_modal']}");
        if($old['status_pembayaran'] == 'Lunas') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo - {$old['harga_jual']} WHERE id = {$old['id_dompet_pemasukan']}");
        }
        
        // 2. UPDATE transaksi dengan data baru
        $q_update = "UPDATE transaksi_penjualan SET 
                    id_kategori = $id_kategori,
                    produk = '$produk',
                    no_tujuan = '$no_tujuan',
                    harga_modal = $harga_modal,
                    harga_jual = $harga_jual,
                    id_dompet_modal = $id_dompet_modal,
                    id_dompet_pemasukan = $id_dompet_pemasukan,
                    status_pembayaran = '$status_pembayaran',
                    keterangan = '$keterangan'
                    WHERE id = $id";
        
        if(!mysqli_query($conn, $q_update)) {
            throw new Exception("Gagal update transaksi: " . mysqli_error($conn));
        }
        
        // 3. POTONG saldo baru
        mysqli_query($conn, "UPDATE dompet SET saldo = saldo - $harga_modal WHERE id = $id_dompet_modal");
        if($status_pembayaran == 'Lunas') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + $harga_jual WHERE id = $id_dompet_pemasukan");
        }
        
        // Simpan Pelanggan Baru
        if($simpan_pelanggan && $no_tujuan != '' && $nama_pelanggan != '') {
            $cek_pelanggan = mysqli_query($conn, "SELECT id FROM pelanggan WHERE no_hp = '$no_tujuan'");
            if(mysqli_num_rows($cek_pelanggan) == 0) {
                mysqli_query($conn, "INSERT INTO pelanggan (nama, no_hp) VALUES ('$nama_pelanggan', '$no_tujuan')");
            }
        }
        
        mysqli_commit($conn);
        // Update local old data so form reflects new data
        $old = array_merge($old, [
            'id_kategori' => $id_kategori, 'produk' => $produk, 'no_tujuan' => $no_tujuan,
            'harga_modal' => $harga_modal, 'harga_jual' => $harga_jual, 
            'id_dompet_modal' => $id_dompet_modal, 'id_dompet_pemasukan' => $id_dompet_pemasukan,
            'status_pembayaran' => $status_pembayaran, 'keterangan' => $keterangan
        ]);
        
        $sukses_msg = "Perubahan transaksi berhasil disimpan!";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Catat Bisnis - Aula Cell</title>
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
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 header-curve pt-10 pb-10 px-6 relative text-white">
                <div class="flex items-center gap-4">
                    <a href="riwayat_bisnis.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm active:scale-95"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="text-xl font-extrabold tracking-tight">Edit Transaksi</h1>
                </div>
            </div>
            <main class="px-5 pt-8 pb-10">
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

    <form method="POST" action="" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 flex flex-col gap-4">
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Kategori Produk</label>
            <select name="id_kategori" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <option value="0" <?= $old['id_kategori'] == 0 ? 'selected' : '' ?>>Pulsa Regular</option>
                <option value="1" <?= $old['id_kategori'] == 1 ? 'selected' : '' ?>>Paket Data</option>
                <option value="2" <?= $old['id_kategori'] == 2 ? 'selected' : '' ?>>E-Wallet (DANA/OVO/GoPay)</option>
                <option value="3" <?= $old['id_kategori'] == 3 ? 'selected' : '' ?>>Token PLN</option>
                <option value="4" <?= $old['id_kategori'] == 4 ? 'selected' : '' ?>>Voucher Game</option>
                <option value="5" <?= $old['id_kategori'] == 5 ? 'selected' : '' ?>>Transfer Bank</option>
                <option value="6" <?= $old['id_kategori'] == 6 ? 'selected' : '' ?>>Lainnya</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Transaksi</label>
            <input type="text" name="produk" value="<?= $old['produk'] ?>" placeholder="Cth: Beli pulsa Budi" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
        </div>
        
        <div>
            <div class="flex justify-between items-center mb-2">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">No Tujuan</label>
                <button type="button" onclick="openPelangganModal()" class="text-[10px] font-bold bg-blue-100 text-primary px-3 py-1 rounded-full active:scale-95 transition-transform flex items-center gap-1">
                    <i class="fa-solid fa-address-book"></i> Pilih
                </button>
            </div>
            <div class="relative">
                <input type="text" name="no_tujuan" id="no_tujuan" value="<?= $old['no_tujuan'] ?>" placeholder="08123456789" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" oninput="checkPelanggan(this.value)">
                <button type="button" id="clearBtn" onclick="clearNoTujuan()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-slate-200 text-slate-500 rounded-full flex items-center justify-center text-xs active:scale-95">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <!-- Checkbox Simpan Pelanggan -->
            <div id="simpanPelangganBox" class="mt-3 hidden bg-blue-50 border border-blue-100 p-3 rounded-xl transition-all">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="simpan_pelanggan" id="chkSimpanPelanggan" onchange="toggleNamaPelanggan()" class="w-4 h-4 rounded text-primary focus:ring-primary">
                    <span class="text-xs font-bold text-slate-700">Simpan ke Buku Pelanggan</span>
                </label>
                <div id="inputNamaPelangganBox" class="mt-2 hidden">
                    <input type="text" name="nama_pelanggan" id="inputNamaPelanggan" placeholder="Nama Pelanggan Baru" class="w-full px-3 py-2 bg-white border border-blue-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                </div>
            </div>
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Modal (Rp)</label>
            <input type="hidden" name="harga_modal" id="hiddenModal" value="<?= $old['harga_modal'] ?>">
            <input type="text" inputmode="numeric" required placeholder="10.000" value="<?= number_format($old['harga_modal'],0,',','.') ?>" oninput="formatRp(this, 'hiddenModal')" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jual (Rp)</label>
            <input type="hidden" name="harga_jual" id="hiddenJual" value="<?= $old['harga_jual'] ?>">
            <input type="text" inputmode="numeric" required placeholder="15.000" value="<?= number_format($old['harga_jual'],0,',','.') ?>" oninput="formatRp(this, 'hiddenJual')" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Potong Saldo (Modal)</label>
            <select name="id_dompet_modal" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <?php
                $q_dompet = mysqli_query($conn, "SELECT * FROM dompet WHERE tipe='Bisnis'");
                while($d = mysqli_fetch_assoc($q_dompet)):
                ?>
                <option value="<?= $d['id'] ?>" <?= $d['id'] == $old['id_dompet_modal'] ? 'selected' : '' ?>><?= $d['nama_dompet'] ?> (Rp <?= number_format($d['saldo'],0,',','.') ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Terima Uang Ke (Laba)</label>
            <select name="id_dompet_pemasukan" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <?php
                $q_dompet = mysqli_query($conn, "SELECT * FROM dompet WHERE tipe='Bisnis'");
                while($d = mysqli_fetch_assoc($q_dompet)):
                ?>
                <option value="<?= $d['id'] ?>" <?= $d['id'] == $old['id_dompet_pemasukan'] ? 'selected' : '' ?>><?= $d['nama_dompet'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status Pembayaran</label>
            <div class="grid grid-cols-2 gap-4">
                <label class="flex items-center justify-center gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="radio" name="status_pembayaran" value="Lunas" <?= $old['status_pembayaran'] == 'Lunas' ? 'checked' : '' ?> class="w-4 h-4 text-primary">
                    <span class="text-sm font-bold text-slate-700">Cash / Lunas</span>
                </label>
                <label class="flex items-center justify-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl cursor-pointer hover:bg-red-100">
                    <input type="radio" name="status_pembayaran" value="Hutang" <?= $old['status_pembayaran'] == 'Hutang' ? 'checked' : '' ?> class="w-4 h-4 text-red-500">
                    <span class="text-sm font-bold text-red-600">Hutang</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Keterangan (Opsional)</label>
            <textarea name="keterangan" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"><?= $old['keterangan'] ?></textarea>
        </div>

        <button type="submit" name="submit" class="w-full mt-2 bg-primary hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-[0_8px_20px_rgba(37,99,235,0.3)] active:scale-95 transition-transform text-sm">
            Simpan Perubahan
        </button>
    </form>
    
    <div class="mt-8 text-center text-xs font-bold text-slate-400">
        <p>Aplikasi Aula Cell &copy; <?= date('Y') ?></p>
    </div>
</main>
        </div>
        
        <!-- Clean Bottom Nav (Mifhda Style floating) -->
        <?php include 'footer.php'; ?>
        
        <!-- Modal Buku Pelanggan -->
        <div id="pelangganModal" class="hidden fixed inset-0 z-[100] flex items-end justify-center sm:items-center">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="closePelangganModal()"></div>
            <div class="bg-white w-full md:w-[400px] rounded-t-[2rem] md:rounded-[2rem] p-6 relative transform transition-transform shadow-2xl pb-safe flex flex-col h-[70vh]">
                <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-4 flex-shrink-0"></div>
                <h3 class="text-lg font-black text-slate-800 text-center mb-4 flex-shrink-0">Buku Pelanggan</h3>
                
                <!-- Search Input -->
                <div class="relative mb-4 flex-shrink-0">
                    <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="searchPelanggan" oninput="filterPelanggan()" placeholder="Cari nama atau nomor..." class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                
                <!-- List Pelanggan -->
                <div class="flex-1 overflow-y-auto min-h-0 flex flex-col gap-2 -mx-2 px-2" id="listPelanggan">
                    <?php
                    $q_pel = mysqli_query($conn, "SELECT * FROM pelanggan ORDER BY nama ASC");
                    $ada_pelanggan = false;
                    while($p = mysqli_fetch_assoc($q_pel)):
                        $ada_pelanggan = true;
                    ?>
                    <button type="button" onclick="pilihPelanggan('<?= $p['no_hp'] ?>', '<?= addslashes($p['nama']) ?>')" class="pelanggan-item flex items-center justify-between p-3 rounded-xl border border-slate-100 hover:bg-slate-50 active:scale-[0.98] transition-all bg-white text-left">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-primary flex items-center justify-center font-bold">
                                <?= strtoupper(substr($p['nama'],0,1)) ?>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm pel-nama"><?= $p['nama'] ?></h4>
                                <p class="text-xs text-slate-500 font-semibold pel-hp"><?= $p['no_hp'] ?></p>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-slate-300 text-xs"></i>
                    </button>
                    <?php endwhile; ?>
                    
                    <?php if(!$ada_pelanggan): ?>
                    <div class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-address-book text-3xl mb-2 text-slate-300"></i>
                        <p class="text-sm font-bold">Belum ada pelanggan</p>
                        <p class="text-[10px]">Pelanggan yang Anda simpan akan muncul di sini</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            // Data Pelanggan untuk pengecekan cepat (apakah nomor sudah ada di DB)
            const dbPelanggan = [
                <?php
                mysqli_data_seek($q_pel, 0);
                while($p = mysqli_fetch_assoc($q_pel)){
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
                    if(nama.includes(term) || hp.includes(term)) {
                        el.style.display = 'flex';
                    } else {
                        el.style.display = 'none';
                    }
                });
            }

            function pilihPelanggan(hp, nama) {
                let textVal = nama + ' - ' + hp;
                document.getElementById('no_tujuan').value = textVal;
                closePelangganModal();
                checkPelanggan(textVal);
            }
            
            function clearNoTujuan() {
                document.getElementById('no_tujuan').value = '';
                checkPelanggan('');
                document.getElementById('no_tujuan').focus();
            }

            function checkPelanggan(val) {
                const box = document.getElementById('simpanPelangganBox');
                const clearBtn = document.getElementById('clearBtn');
                
                if (val.length > 0) {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }

                if (val.includes(' - ')) {
                    box.classList.add('hidden');
                    document.getElementById('chkSimpanPelanggan').checked = false;
                    toggleNamaPelanggan();
                    return;
                }

                if(val.length >= 10 && !dbPelanggan.includes(val)) {
                    // Nomor valid & belum ada di DB -> Tampilkan opsi simpan
                    box.classList.remove('hidden');
                } else {
                    // Kosong, kependekan, atau sudah ada di DB
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

            function formatRp(el, hiddenId) {
                let raw = el.value.replace(/\D/g, '');
                document.getElementById(hiddenId).value = raw;
                el.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        </script>
</body>
</html>