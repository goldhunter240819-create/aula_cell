<?php
require '../koneksi.php';

$sukses_msg = "";
$error_msg = "";

if(!isset($_GET['id'])) {
    header("Location: riwayat_pribadi.php");
    exit;
}
$id = (int)$_GET['id'];

// Get existing data
$q_exist = mysqli_query($conn, "SELECT * FROM transaksi_umum WHERE id = $id");
if(mysqli_num_rows($q_exist) == 0) {
    header("Location: riwayat_pribadi.php");
    exit;
}
$old = mysqli_fetch_assoc($q_exist);

// Jika form disubmit
if(isset($_POST['submit'])) {
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
    $id_kategori = (int)$_POST['id_kategori'];
    $id_dompet = (int)$_POST['id_dompet'];
    $nominal = (int)$_POST['nominal'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    
    // Mulai transaksi database
    mysqli_begin_transaction($conn);
    try {
        // 1. Kembalikan saldo lama
        if($old['jenis'] == 'Pemasukan') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo - {$old['nominal']} WHERE id = {$old['id_dompet']}");
        } else {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + {$old['nominal']} WHERE id = {$old['id_dompet']}");
        }
        
        // 2. Update transaksi
        $q_update = "UPDATE transaksi_umum SET 
                    tanggal = '$tanggal',
                    jenis = '$jenis',
                    id_kategori = $id_kategori,
                    id_dompet = $id_dompet,
                    nominal = $nominal,
                    keterangan = '$keterangan'
                    WHERE id = $id";
        
        if(!mysqli_query($conn, $q_update)) {
            throw new Exception("Gagal update transaksi: " . mysqli_error($conn));
        }
        
        // 3. Update saldo baru
        if($jenis == 'Pemasukan') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + $nominal WHERE id = $id_dompet");
        } else { // Pengeluaran
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo - $nominal WHERE id = $id_dompet");
        }
        
        mysqli_commit($conn);
        // Refresh local data
        $old = array_merge($old, [
            'tanggal' => $tanggal, 'jenis' => $jenis, 'id_kategori' => $id_kategori,
            'id_dompet' => $id_dompet, 'nominal' => $nominal, 'keterangan' => $keterangan
        ]);
        
        $sukses_msg = "Perubahan transaksi $jenis sebesar Rp " . number_format($nominal,0,',','.') . " berhasil disimpan!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = $e->getMessage();
    }
}

// Persiapkan opsi dompet
$uid = $_SESSION['user_id'];
$dompet_options = "";
$q_dompet = mysqli_query($conn, "SELECT * FROM dompet WHERE (tipe = 'Pribadi' AND user_id = $uid) OR tipe = 'Bisnis' ORDER BY tipe, nama_dompet");
while($d = mysqli_fetch_assoc($q_dompet)) {
    $selected = ($d['id'] == $old['id_dompet']) ? 'selected' : '';
    $dompet_options .= "<option value=\"{$d['id']}\" $selected>{$d['nama_dompet']} ({$d['tipe']})</option>";
}

// Persiapkan opsi kategori
$kat_pemasukan = "";
$kat_pengeluaran = "";
$q_kat = mysqli_query($conn, "SELECT * FROM kategori WHERE tipe = 'Pribadi' ORDER BY jenis, nama_kategori");
while($k = mysqli_fetch_assoc($q_kat)) {
    $sel = ($k['id'] == $old['id_kategori']) ? 'selected' : '';
    if($k['jenis'] == 'Pemasukan') {
        $kat_pemasukan .= "<option value=\"{$k['id']}\" $sel>{$k['nama_kategori']}</option>";
    } else {
        $kat_pengeluaran .= "<option value=\"{$k['id']}\" $sel>{$k['nama_kategori']}</option>";
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Catat Umum - Aula Cell</title>
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <meta name="theme-color" content="#2563eb">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ["Nunito", "sans-serif"], },
                    colors: { primary: "#2563eb", secondary: "#3b82f6", bglight: "#f8fafc", }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f172a; color: #334155; -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 1rem); }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        .header-curve { border-bottom-left-radius: 3rem; border-bottom-right-radius: 3rem; box-shadow: 0 10px 30px -10px rgba(37, 99, 235, 0.4); }
        .glass-input { background: rgba(255,255,255,0.9); border: 1px solid rgba(226, 232, 240, 0.8); backdrop-filter: blur(10px); }
        .glass-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
        
        .modal-enter { animation: popIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
        @keyframes popIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 m-0 p-0 overflow-hidden">
    <div class="fixed inset-0 w-full max-w-[28rem] mx-auto bg-slate-50 shadow-2xl overflow-hidden flex flex-col">
        
        <div class="flex-1 overflow-y-auto pb-32 overflow-x-hidden pb-24 flex flex-col relative z-0">
            <!-- Header Curve -->
            <div class="bg-gradient-to-br from-blue-700 via-blue-600 to-blue-500 header-curve pt-12 pb-14 px-6 relative text-white flex-none overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
                <div class="flex items-center gap-4 relative z-10">
                    <a href="riwayat_pribadi.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-md active:scale-95 transition-transform"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight">Edit Transaksi</h1>
                        <p class="text-blue-100 text-xs font-bold mt-1 opacity-80">Ubah data transaksi harianmu</p>
                    </div>
                </div>
            </div>
            
            <main class="px-5 pt-8 flex-1 flex flex-col relative z-10 -mt-6 pb-10">
                
                <?php if($sukses_msg): ?>
                    <div class="mb-6 w-full p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-check text-xl"></i>
                        </div>
                        <p class="text-sm font-bold leading-tight"><?= $sukses_msg ?></p>
                    </div>
                <?php endif; ?>
                <?php if($error_msg): ?>
                    <div class="mb-6 w-full p-4 rounded-2xl bg-red-50 border border-red-100 text-red-600 flex items-center gap-4 shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-exclamation text-xl"></i>
                        </div>
                        <p class="text-sm font-bold leading-tight"><?= $error_msg ?></p>
                    </div>
                <?php endif; ?>

                <?php 
                $is_in = $old['jenis'] == 'Pemasukan';
                $color = $is_in ? 'emerald' : 'red';
                $icon = $is_in ? 'fa-arrow-down' : 'fa-arrow-up';
                $kat_options = $is_in ? $kat_pemasukan : $kat_pengeluaran;
                ?>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 relative">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-8 h-8 bg-<?= $color ?>-100 rounded-full flex items-center justify-center">
                            <i class="fa-solid <?= $icon ?> text-<?= $color ?>-500 text-xs"></i>
                        </div>
                        <h3 class="text-sm font-black text-slate-800">Edit <?= $old['jenis'] ?></h3>
                    </div>
                    <form action="" method="POST" class="flex flex-col gap-3">
                        <input type="hidden" name="jenis" value="<?= $old['jenis'] ?>">
                        <div>
                            <label class="block text-[9px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Tanggal</label>
                            <input type="date" name="tanggal" required value="<?= date('Y-m-d', strtotime($old['tanggal'])) ?>" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-<?= $color ?>-500/20 focus:border-<?= $color ?>-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Nominal (Rp)</label>
                            <input type="hidden" name="nominal" id="nominalEdit" value="<?= $old['nominal'] ?>">
                            <input type="text" inputmode="numeric" required placeholder="10.000" value="<?= number_format($old['nominal'],0,',','.') ?>" oninput="formatNominal(this, 'nominalEdit')" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm font-bold focus:outline-none focus:ring-2 focus:ring-<?= $color ?>-500/20 focus:border-<?= $color ?>-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Kategori</label>
                            <select name="id_kategori" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-<?= $color ?>-500/20 focus:border-<?= $color ?>-500">
                                <?= $kat_options ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Dompet</label>
                            <select name="id_dompet" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-<?= $color ?>-500/20 focus:border-<?= $color ?>-500">
                                <?= $dompet_options ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[9px] font-extrabold text-slate-400 uppercase tracking-widest mb-1">Keterangan</label>
                            <input type="text" name="keterangan" required value="<?= $old['keterangan'] ?>" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold focus:outline-none focus:ring-2 focus:ring-<?= $color ?>-500/20 focus:border-<?= $color ?>-500">
                        </div>
                        <button type="submit" name="submit" class="w-full mt-1 bg-gradient-to-r from-<?= $color ?>-500 to-<?= $color == 'emerald' ? 'teal' : 'rose' ?>-500 text-white font-black py-2.5 rounded-xl shadow-lg shadow-<?= $color ?>-500/25 active:scale-95 transition-transform text-sm">
                            <i class="fa-solid fa-check mr-1"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </main>
        </div>
        
        <!-- Navigation Bottom -->
        <?php include 'footer.php'; ?>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
            document.body.style.overflow = '';
        }
        function formatNominal(el, hiddenId) {
            let raw = el.value.replace(/\D/g, '');
            document.getElementById(hiddenId).value = raw;
            el.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    </script>
</body>
</html>
