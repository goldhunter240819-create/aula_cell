<?php
require '../koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Riwayat Pengeluaran Konter - Aula Cell</title>
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <meta name="theme-color" content="#ef4444">
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
        .header-curve { border-bottom-left-radius: 2.5rem; border-bottom-right-radius: 2.5rem; box-shadow: 0 4px 20px -2px rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 m-0 p-0 overflow-hidden">
    <div class="fixed inset-0 w-full max-w-[28rem] mx-auto bg-slate-50 shadow-2xl overflow-hidden flex flex-col">
        
        <div class="flex-1 overflow-y-auto pb-32 overflow-x-hidden pb-8">
            <div class="bg-gradient-to-r from-red-600 to-red-400 header-curve pt-10 pb-8 px-6 relative text-white mb-6">
                <div class="flex items-center gap-4 mb-2">
                    <a href="laporan_bisnis.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm active:scale-95"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="text-xl font-extrabold tracking-tight">Riwayat Pengeluaran Konter</h1>
                </div>
            </div>
            
            <main class="px-5 pb-10">
                <div class="flex flex-col gap-3">
                    <?php 
                    $q = mysqli_query($conn, "SELECT t.*, k.nama_kategori, d.nama_dompet FROM transaksi_umum t JOIN kategori k ON t.id_kategori = k.id JOIN dompet d ON t.id_dompet = d.id WHERE t.jenis = 'Pengeluaran' AND d.tipe = 'Bisnis' ORDER BY t.tanggal DESC LIMIT 50");
                    if(mysqli_num_rows($q) == 0): ?>
                        <p class="text-sm text-slate-400 text-center italic py-4 bg-white rounded-2xl border border-slate-100">Belum ada pengeluaran</p>
                    <?php else:
                        while($r = mysqli_fetch_assoc($q)): 
                    ?>
                    <div class="bg-white p-4 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-200">
                        <div class="flex justify-between items-center">
                            <div class="flex gap-3 items-center flex-1 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-red-50 text-red-500 flex items-center justify-center text-sm flex-shrink-0">
                                    <i class="fa-solid fa-arrow-up"></i>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm text-slate-800 truncate"><?= $r['keterangan'] ?></h4>
                                    <p class="text-[10px] text-slate-500 font-semibold"><?= date('d M Y', strtotime($r['tanggal'])) ?> &bull; <?= $r['nama_dompet'] ?></p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                <p class="font-extrabold text-sm text-red-500">-Rp <?= number_format($r['nominal'],0,',','.') ?></p>
                                <p class="text-[10px] text-slate-400 font-semibold"><?= $r['nama_kategori'] ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; endif; ?>
                </div>
            </main>
        </div>

        <?php include 'footer.php'; ?>
    </div>

</body>
</html>
