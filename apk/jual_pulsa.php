<?php
require '../koneksi.php';

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
    $status_pembayaran = mysqli_real_escape_string($conn, $_POST['status_pembayaran']); // Lunas / Hutang
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal = date('Y-m-d H:i:s');
    
    mysqli_begin_transaction($conn);
    try {
        $q_insert = "INSERT INTO transaksi_penjualan 
                    (tanggal, id_kategori, produk, no_tujuan, harga_modal, harga_jual, id_dompet_modal, id_dompet_pemasukan, status, status_pembayaran, keterangan) 
                    VALUES 
                    ('$tanggal', $id_kategori, '$produk', '$no_tujuan', $harga_modal, $harga_jual, $id_dompet_modal, $id_dompet_pemasukan, 'Sukses', '$status_pembayaran', '$keterangan')";
        
        if(!mysqli_query($conn, $q_insert)) {
            throw new Exception("Gagal input transaksi: " . mysqli_error($conn));
        }
        
        // Update saldo dompet (Modal selalu terpotong karena barang/pulsa dibeli)
        mysqli_query($conn, "UPDATE dompet SET saldo = saldo - $harga_modal WHERE id = $id_dompet_modal");
        
        // Pemasukan hanya bertambah jika Lunas
        if($status_pembayaran == 'Lunas') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + $harga_jual WHERE id = $id_dompet_pemasukan");
        }
        
        mysqli_commit($conn);
        if($status_pembayaran == 'Hutang') {
            $sukses_msg = "Transaksi dicatat sebagai HUTANG! Modal dipotong, tapi uang masuk belum ditambahkan.";
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
<body class="font-sans antialiased bg-slate-900 flex justify-center h-[100dvh] overflow-hidden">
    <div class="w-full max-w-[400px] h-[100dvh] bg-slate-50 relative shadow-2xl overflow-hidden flex flex-col">
        <div class="flex-1 overflow-y-auto pb-32 overflow-x-hidden pb-24">
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 header-curve pt-10 pb-10 px-6 relative text-white">
                <div class="flex items-center gap-4">
                    <a href="index.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm active:scale-95"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="text-xl font-extrabold tracking-tight">Pencatatan Bisnis</h1>
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
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Transaksi</label>
            <input type="text" name="produk" placeholder="Cth: Beli pulsa Budi" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">No Tujuan (Bila Ada)</label>
            <input type="text" name="no_tujuan" placeholder="08123456789" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Modal (Rp)</label>
            <input type="number" name="harga_modal" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Jual (Rp)</label>
            <input type="number" name="harga_jual" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Potong Saldo (Modal)</label>
            <select name="id_dompet_modal" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                <?php
                $q_dompet = mysqli_query($conn, "SELECT * FROM dompet WHERE tipe='Bisnis'");
                while($d = mysqli_fetch_assoc($q_dompet)):
                ?>
                <option value="<?= $d['id'] ?>"><?= $d['nama_dompet'] ?> (Rp <?= number_format($d['saldo'],0,',','.') ?>)</option>
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
                <option value="<?= $d['id'] ?>"><?= $d['nama_dompet'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status Pembayaran</label>
            <div class="grid grid-cols-2 gap-4">
                <label class="flex items-center justify-center gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100">
                    <input type="radio" name="status_pembayaran" value="Lunas" checked class="w-4 h-4 text-primary">
                    <span class="text-sm font-bold text-slate-700">Cash / Lunas</span>
                </label>
                <label class="flex items-center justify-center gap-2 p-3 bg-red-50 border border-red-200 rounded-xl cursor-pointer hover:bg-red-100">
                    <input type="radio" name="status_pembayaran" value="Hutang" class="w-4 h-4 text-red-500">
                    <span class="text-sm font-bold text-red-600">Hutang</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Keterangan (Opsional)</label>
            <textarea name="keterangan" rows="2" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"></textarea>
        </div>

        <button type="submit" name="submit" class="w-full mt-2 bg-primary hover:bg-blue-700 text-white font-black py-4 rounded-xl shadow-[0_8px_20px_rgba(37,99,235,0.3)] active:scale-95 transition-transform text-sm">
            Simpan Transaksi
        </button>
    </form>
    
    <div class="mt-8 text-center text-xs font-bold text-slate-400">
        <p>Aplikasi Aula Cell &copy; <?= date('Y') ?></p>
    </div>
</main>
        </div>
        
        <!-- Clean Bottom Nav (Mifhda Style floating) -->
        <?php include 'footer.php'; ?>
</body>
</html>