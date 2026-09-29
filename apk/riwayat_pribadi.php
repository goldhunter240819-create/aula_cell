<?php
require '../koneksi.php';

$sukses_msg = "";
$error_msg = "";

// HANDLER: HAPUS TRANSAKSI UMUM
if(isset($_POST['hapus_umum'])) {
    $id = (int)$_POST['id_transaksi'];
    mysqli_begin_transaction($conn);
    try {
        $q = mysqli_query($conn, "SELECT * FROM transaksi_umum WHERE id = $id");
        if(mysqli_num_rows($q) == 0) throw new Exception("Transaksi tidak ditemukan.");
        $trx = mysqli_fetch_assoc($q);
        
        if($trx['jenis'] == 'Pemasukan') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo - {$trx['nominal']} WHERE id = {$trx['id_dompet']}");
        } else {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + {$trx['nominal']} WHERE id = {$trx['id_dompet']}");
        }
        mysqli_query($conn, "DELETE FROM transaksi_umum WHERE id = $id");
        
        mysqli_commit($conn);
        $sukses_msg = "Transaksi dihapus & saldo dikembalikan!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = $e->getMessage();
    }
}

// HANDLER: EDIT TRANSAKSI UMUM
if(isset($_POST['edit_umum'])) {
    $id = (int)$_POST['id_transaksi'];
    $nominal_baru = (int)$_POST['edit_nominal'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['edit_keterangan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['edit_tanggal']);
    
    mysqli_begin_transaction($conn);
    try {
        $q = mysqli_query($conn, "SELECT * FROM transaksi_umum WHERE id = $id");
        if(mysqli_num_rows($q) == 0) throw new Exception("Transaksi tidak ditemukan.");
        $old = mysqli_fetch_assoc($q);
        
        $selisih = $nominal_baru - $old['nominal'];
        
        if($old['jenis'] == 'Pemasukan') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + ($selisih) WHERE id = {$old['id_dompet']}");
        } else {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo - ($selisih) WHERE id = {$old['id_dompet']}");
        }
        
        mysqli_query($conn, "UPDATE transaksi_umum SET nominal=$nominal_baru, keterangan='$keterangan', tanggal='$tanggal' WHERE id = $id");
        
        mysqli_commit($conn);
        $sukses_msg = "Transaksi berhasil diedit!";
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
    <title>Riwayat Pribadi - Aula Cell</title>
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <meta name="theme-color" content="#10b981">
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
                <!-- Notifikasi -->
                <?php if($sukses_msg): ?>
                    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center gap-2 text-xs font-bold">
                        <i class="fa-solid fa-circle-check"></i> <?= $sukses_msg ?>
                    </div>
                <?php endif; ?>
                <?php if($error_msg): ?>
                    <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 flex items-center gap-2 text-xs font-bold">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= $error_msg ?>
                    </div>
                <?php endif; ?>

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
                    <div class="bg-white p-4 rounded-2xl shadow-[0_2px_10px_rgba(0,0,0,0.02)] border border-slate-200">
                        <div class="flex justify-between items-center">
                            <div class="flex gap-3 items-center flex-1 min-w-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm flex-shrink-0 <?= $bg ?>">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm text-slate-800 truncate"><?= $r['keterangan'] ?></h4>
                                    <p class="text-[10px] text-slate-500 font-semibold"><?= date('d M Y', strtotime($r['tanggal'])) ?> &bull; <?= $r['nama_dompet'] ?></p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                <p class="font-extrabold text-sm <?= $color ?>"><?= $sign ?>Rp <?= number_format($r['nominal'],0,',','.') ?></p>
                                <p class="text-[10px] text-slate-400 font-semibold"><?= $r['nama_kategori'] ?></p>
                            </div>
                        </div>
                        <!-- Tombol Aksi -->
                        <div class="flex gap-2 mt-3 pt-3 border-t border-slate-100">
                            <button type="button" onclick='openEditUmum(<?= json_encode($r) ?>)' class="flex-1 py-2 bg-blue-50 text-blue-600 rounded-xl text-xs font-bold hover:bg-blue-100 active:scale-95 transition-all flex items-center justify-center gap-1">
                                <i class="fa-solid fa-pen text-[10px]"></i> Edit
                            </button>
                            <form method="POST" action="" onsubmit="return confirm('Yakin hapus? Saldo akan dihitung ulang.');" class="flex-1">
                                <input type="hidden" name="id_transaksi" value="<?= $r['id'] ?>">
                                <button type="submit" name="hapus_umum" class="w-full py-2 bg-red-50 text-red-600 rounded-xl text-xs font-bold hover:bg-red-100 active:scale-95 transition-all flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-trash text-[10px]"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; endif; ?>
                </div>
            </main>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <!-- MODAL EDIT TRANSAKSI UMUM (Mobile) -->
    <div id="modalEditUmum" class="hidden fixed inset-0 z-[100] flex items-end justify-center sm:items-center">
        <div class="modal-overlay fixed inset-0" onclick="closeModal()"></div>
        <div class="bg-white w-full md:w-[400px] rounded-t-[2rem] md:rounded-[2rem] p-6 relative z-10 shadow-2xl pb-safe">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-4"></div>
            <h3 class="text-lg font-extrabold text-slate-800 text-center mb-4"><i class="fa-solid fa-pen-to-square text-blue-500 mr-1"></i> Edit Transaksi</h3>
            <form method="POST" action="" class="flex flex-col gap-3">
                <input type="hidden" name="id_transaksi" id="eu_id">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Tanggal</label>
                    <input type="date" name="edit_tanggal" id="eu_tanggal" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nominal (Rp)</label>
                    <input type="hidden" name="edit_nominal" id="eu_nominal_hidden">
                    <input type="text" inputmode="numeric" id="eu_nominal" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary" oninput="formatRp(this, 'eu_nominal_hidden')">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Keterangan</label>
                    <input type="text" name="edit_keterangan" id="eu_keterangan" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary">
                </div>
                <div class="grid grid-cols-2 gap-3 mt-2">
                    <button type="button" onclick="closeModal()" class="py-3 bg-slate-100 text-slate-500 font-extrabold rounded-xl text-sm">Batal</button>
                    <button type="submit" name="edit_umum" class="py-3 bg-primary text-white font-extrabold rounded-xl text-sm shadow-lg shadow-primary/30"><i class="fa-solid fa-check mr-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function formatRp(el, hiddenId) {
            let raw = el.value.replace(/\D/g, '');
            document.getElementById(hiddenId).value = raw;
            el.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function openEditUmum(data) {
            document.getElementById('eu_id').value = data.id;
            document.getElementById('eu_tanggal').value = data.tanggal;
            
            document.getElementById('eu_nominal_hidden').value = Math.round(data.nominal);
            document.getElementById('eu_nominal').value = Math.round(data.nominal).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            
            document.getElementById('eu_keterangan').value = data.keterangan || '';
            document.getElementById('modalEditUmum').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modalEditUmum').classList.add('hidden');
        }

        document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModal(); });
    </script>

</body>
</html>