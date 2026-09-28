<?php
require '../koneksi.php';

$sukses_msg = "";
$error_msg = "";

// Jika tombol Lunasi ditekan
if(isset($_POST['lunasi'])) {
    $id_transaksi = (int)$_POST['id_transaksi'];
    
    mysqli_begin_transaction($conn);
    try {
        // Ambil data transaksi
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
$total_piutang = ($q_total) ? (mysqli_fetch_assoc($q_total)['total'] ?? 0) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Buku Hutang - Aula Cell</title>
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
            
            <div class="bg-gradient-to-r from-red-600 to-rose-500 header-curve pt-10 pb-16 px-6 relative text-white">
                <div class="flex items-center gap-4 mb-6">
                    <a href="pengaturan.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm active:scale-95"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="text-xl font-extrabold tracking-tight">Buku Hutang</h1>
                </div>
                
                <p class="text-xs text-rose-100 font-bold uppercase tracking-widest mb-1">Total Uang Nyangkut</p>
                <h2 class="text-3xl font-black tracking-tight">Rp <?= number_format($total_piutang,0,',','.') ?></h2>
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

                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-extrabold text-slate-800">Daftar Tagihan</h3>
                    <span class="text-xs font-bold text-slate-400"><?= $q_hutang ? mysqli_num_rows($q_hutang) : 0 ?> orang</span>
                </div>

                <div class="flex flex-col gap-4">
                    <?php if($q_hutang && mysqli_num_rows($q_hutang) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($q_hutang)): ?>
                            <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-black text-slate-800 text-sm"><?= $row['produk'] ?></h4>
                                        <p class="text-[10px] font-bold text-slate-400 mt-1"><i class="fa-regular fa-clock"></i> <?= date('d M Y, H:i', strtotime($row['tanggal'])) ?></p>
                                    </div>
                                    <div class="bg-red-50 text-red-600 px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase">Belum Lunas</div>
                                </div>
                                
                                <div class="bg-slate-50 rounded-xl p-3 mb-4 border border-slate-100 flex justify-between items-center">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-500 mb-0.5">Nominal Tagihan</p>
                                        <p class="text-base font-black text-slate-800">Rp <?= number_format($row['harga_jual'],0,',','.') ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] font-bold text-slate-500 mb-0.5">Masuk Ke</p>
                                        <p class="text-xs font-extrabold text-primary"><?= $row['nama_dompet'] ?></p>
                                    </div>
                                </div>
                                
                                <?php if($row['keterangan']): ?>
                                    <p class="text-xs text-slate-500 mb-4 bg-yellow-50 p-2 rounded-lg border border-yellow-100"><i class="fa-solid fa-note-sticky text-yellow-500 mr-1"></i> <?= $row['keterangan'] ?></p>
                                <?php endif; ?>

                                <form method="POST" action="" onsubmit="return confirm('Yakin tagihan ini sudah dibayar lunas?');">
                                    <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                                    <button type="submit" name="lunasi" class="w-full py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-black rounded-xl shadow-lg shadow-emerald-500/30 active:scale-95 transition-transform text-sm flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-hand-holding-dollar"></i> Tandai Lunas
                                    </button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-12 bg-white rounded-2xl border border-slate-200 border-dashed">
                            <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                                <i class="fa-solid fa-face-smile"></i>
                            </div>
                            <h4 class="font-extrabold text-slate-800 mb-1">Wah, Bebas Hutang!</h4>
                            <p class="text-xs font-bold text-slate-400">Tidak ada pelanggan yang ngutang.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>

        <?php include 'footer.php'; ?>
</body>
</html>
