<?php
require '../koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Riwayat Bisnis - Aula Cell</title>
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <meta name="theme-color" content="#2563eb">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ["Nunito", "sans-serif"], }, colors: { primary: "#2563eb", secondary: "#3b82f6", bglight: "#f1f5f9", } } } }
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
        
        <div class="flex-1 overflow-y-auto pb-32 overflow-x-hidden pb-8">
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 header-curve pt-10 pb-8 px-6 relative text-white mb-6">
                <div class="flex items-center gap-4 mb-2">
                    <a href="laporan_bisnis.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm active:scale-95"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="text-xl font-extrabold tracking-tight">Riwayat Penjualan</h1>
                </div>
            </div>
            
            <main class="px-5 pb-10">
                <div class="flex flex-col gap-3">
                    <?php 
                    $q_jual = mysqli_query($conn, "SELECT t.*, d_m.nama_dompet as dompet_modal, d_p.nama_dompet as dompet_pemasukan FROM transaksi_penjualan t JOIN dompet d_m ON t.id_dompet_modal = d_m.id JOIN dompet d_p ON t.id_dompet_pemasukan = d_p.id ORDER BY t.tanggal DESC LIMIT 50");
                    if(mysqli_num_rows($q_jual) == 0): ?>
                        <p class="text-sm text-slate-400 text-center italic py-4 bg-white rounded-2xl border border-slate-100">Belum ada transaksi</p>
                    <?php else:
                        while($r = mysqli_fetch_assoc($q_jual)): 
                            $status_color = $r['status'] == 'Sukses' ? 'text-emerald-500' : ($r['status'] == 'Pending' ? 'text-amber-500' : 'text-red-500');
                    ?>
                    <div class="bg-white p-4 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-200 flex justify-between items-center">
                        <div class="flex gap-3 items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-primary text-sm">
                                <i class="fa-solid fa-mobile-screen"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-slate-800"><?= $r['produk'] ?></h4>
                                <p class="text-[10px] text-slate-500 font-semibold"><?= date('d M Y', strtotime($r['tanggal'])) ?> &bull; <?= $r['no_tujuan'] ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-extrabold text-sm text-slate-800">Rp <?= number_format($r['harga_jual'],0,',','.') ?></p>
                            <p class="text-[10px] font-bold <?= $status_color ?>"><?= $r['status'] ?></p>
                        </div>
                    </div>
                    <?php endwhile; endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>