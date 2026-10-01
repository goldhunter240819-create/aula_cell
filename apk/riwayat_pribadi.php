<?php
require '../koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Riwayat Pribadi - Aula Cell</title>
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <meta name="theme-color" content="#10b981">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ["Nunito", "sans-serif"], }, colors: { primary: "#2563eb", secondary: "#3b82f6", bglight: "#f1f5f9", } } } }
    </script>
    <style>
        body { background-color: #0f172a; color: #334155; -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 1rem); }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        .header-curve { border-bottom-left-radius: 2.5rem; border-bottom-right-radius: 2.5rem; box-shadow: 0 4px 20px -2px rgba(16, 185, 129, 0.3); }
        .modal-overlay { background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 m-0 p-0 overflow-hidden">
    <div class="fixed inset-0 w-full max-w-[28rem] mx-auto bg-slate-50 shadow-2xl overflow-hidden flex flex-col">
        
        <div class="flex-1 overflow-y-auto pb-32 overflow-x-hidden pb-8">
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-400 header-curve pt-10 pb-8 px-6 relative text-white mb-6">
                <div class="flex items-center gap-4 mb-2">
                    <a href="laporan_pribadi.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm active:scale-95"><i class="fa-solid fa-arrow-left"></i></a>
                    <h1 class="text-xl font-extrabold tracking-tight">Riwayat Pribadi</h1>
                </div>
            </div>
            
            <main class="px-5 pb-10">

                <div class="flex flex-col gap-3">
                    <?php 
                    $q_umum = mysqli_query($conn, "SELECT t.*, k.nama_kategori, d.nama_dompet FROM transaksi_umum t JOIN kategori k ON t.id_kategori = k.id JOIN dompet d ON t.id_dompet = d.id WHERE d.tipe = 'Pribadi' ORDER BY t.tanggal DESC LIMIT 50");
                    if(mysqli_num_rows($q_umum) == 0): ?>
                        <p class="text-sm text-slate-400 text-center italic py-4 bg-white rounded-2xl border border-slate-100">Belum ada transaksi</p>
                    <?php else:
                        while($r = mysqli_fetch_assoc($q_umum)): 
                            $is_in = $r['jenis'] == 'Pemasukan';
                            $sign = $is_in ? '+' : '-';
                            $color = $is_in ? 'text-emerald-500' : 'text-red-500';
                            $bg = $is_in ? 'bg-emerald-50 text-emerald-500' : 'bg-red-50 text-red-500';
                            $icon = $is_in ? 'fa-arrow-down' : 'fa-arrow-up';
                    ?>
                    <div class="bg-white p-4 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-200 relative group">
                        
                        <!-- Header & Action Buttons -->
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex gap-3 items-center flex-1 min-w-0">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm flex-shrink-0 <?= $bg ?>">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm text-slate-800 truncate"><?= $r['keterangan'] ?></h4>
                                    <p class="text-[10px] text-slate-500 font-semibold"><?= date('d M Y', strtotime($r['tanggal'])) ?> &bull; <?= $r['nama_dompet'] ?></p>
                                </div>
                            </div>
                            
                            <!-- Minimalist Edit & Delete -->
                            <div class="flex items-center gap-3 ml-2 text-slate-300">
                                <a href="edit_transaksi_umum.php?id=<?= $r['id'] ?>" class="hover:text-blue-500 transition-colors"><i class="fa-solid fa-pen text-[11px]"></i></a>
                                <a href="hapus_transaksi_umum.php?id=<?= $r['id'] ?>" onclick="confirmDelete(event, this.href)" class="hover:text-red-500 transition-colors"><i class="fa-solid fa-trash text-[11px]"></i></a>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="w-full h-px border-t border-dashed border-slate-200 mb-2.5"></div>

                        <!-- Footer -->
                        <div class="flex justify-between items-end">
                            <div class="flex gap-1.5 pb-0.5">
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-50 text-slate-500"><?= $r['nama_kategori'] ?></span>
                            </div>
                            
                            <div class="text-right">
                                <p class="font-extrabold text-sm <?= $color ?>"><?= $sign ?>Rp <?= number_format($r['nominal'],0,',','.') ?></p>
                            </div>
                        </div>
                        
                    </div>
                    <?php endwhile; endif; ?>
                </div>
            </main>
        </div>

        <?php include 'footer.php'; ?>
    </div>

<script>
function confirmDelete(e, url) {
    e.preventDefault();
    Swal.fire({
        title: 'Yakin dihapus?',
        text: "Riwayat ini akan hilang permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-3xl shadow-2xl',
            confirmButton: 'rounded-xl font-bold px-5 py-2.5',
            cancelButton: 'rounded-xl font-bold px-5 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    })
}
</script>

</body>
</html>