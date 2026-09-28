<?php
require '../koneksi.php';

$sukses_msg = "";
$error_msg = "";

// Jika form disubmit
if(isset($_POST['submit'])) {
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis']);
    $id_dompet = (int)$_POST['id_dompet'];
    $nominal = (int)$_POST['nominal'];
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
    
    // Mulai transaksi database
    mysqli_begin_transaction($conn);
    try {
        // 1. Insert ke tabel transaksi_umum
        $q_insert = "INSERT INTO transaksi_umum 
                    (tanggal, jenis, id_dompet, nominal, keterangan) 
                    VALUES 
                    ('$tanggal', '$jenis', $id_dompet, $nominal, '$keterangan')";
        
        if(!mysqli_query($conn, $q_insert)) {
            throw new Exception("Gagal input transaksi: " . mysqli_error($conn));
        }
        
        // 2. Jika sukses, update saldo dompet
        if($jenis == 'Pemasukan') {
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + $nominal WHERE id = $id_dompet");
        } else { // Pengeluaran
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo - $nominal WHERE id = $id_dompet");
        }
        
        mysqli_commit($conn);
        $sukses_msg = "Transaksi $jenis sebesar Rp " . number_format($nominal,0,',','.') . " berhasil dicatat!";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error_msg = $e->getMessage();
    }
}

// Persiapkan opsi dompet
$dompet_options = "";
$q_dompet = mysqli_query($conn, "SELECT * FROM dompet ORDER BY tipe, nama_dompet");
while($d = mysqli_fetch_assoc($q_dompet)) {
    $selected = ($d['nama_dompet'] == 'Kas Pribadi') ? 'selected' : '';
    $dompet_options .= "<option value=\"{$d['id']}\" $selected>{$d['nama_dompet']} ({$d['tipe']})</option>";
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
        
        .modal-enter { animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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
                    <a href="index.php" class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-md active:scale-95 transition-transform"><i class="fa-solid fa-arrow-left"></i></a>
                    <div>
                        <h1 class="text-2xl font-black tracking-tight">Keuangan Pribadi</h1>
                        <p class="text-blue-100 text-xs font-bold mt-1 opacity-80">Catat transaksi harianmu</p>
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

                <div class="flex flex-col gap-5 mt-2">
                    
                    <!-- Uang Masuk Button -->
                    <div class="relative w-full">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-400 blur-xl opacity-20 rounded-3xl translate-y-2"></div>
                        <button onclick="openModal('modalPemasukan')" class="relative w-full bg-white/90 backdrop-blur-xl border border-white p-5 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-center gap-5 hover:bg-white transition-all active:scale-[0.97] group">
                            <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-[1.25rem] shadow-lg shadow-emerald-500/30 flex items-center justify-center text-white text-xl group-hover:-translate-y-1 transition-transform">
                                <i class="fa-solid fa-arrow-down"></i>
                            </div>
                            <div class="text-left flex-1">
                                <h3 class="text-lg font-black text-slate-800">Uang Masuk</h3>
                                <p class="text-[11px] font-bold text-slate-400 mt-0.5">Terima gaji, tabungan, dll</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-emerald-50 group-hover:text-emerald-500 transition-colors">
                                <i class="fa-solid fa-chevron-right text-sm"></i>
                            </div>
                        </button>
                    </div>

                    <!-- Uang Keluar Button -->
                    <div class="relative w-full mt-2">
                        <div class="absolute inset-0 bg-gradient-to-r from-red-400 to-rose-400 blur-xl opacity-20 rounded-3xl translate-y-2"></div>
                        <button onclick="openModal('modalPengeluaran')" class="relative w-full bg-white/90 backdrop-blur-xl border border-white p-5 rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex items-center gap-5 hover:bg-white transition-all active:scale-[0.97] group">
                            <div class="w-14 h-14 bg-gradient-to-br from-red-400 to-red-500 rounded-[1.25rem] shadow-lg shadow-red-500/30 flex items-center justify-center text-white text-xl group-hover:-translate-y-1 transition-transform">
                                <i class="fa-solid fa-arrow-up"></i>
                            </div>
                            <div class="text-left flex-1">
                                <h3 class="text-lg font-black text-slate-800">Uang Keluar</h3>
                                <p class="text-[11px] font-bold text-slate-400 mt-0.5">Belanja, bensin, jajan</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-red-50 group-hover:text-red-500 transition-colors">
                                <i class="fa-solid fa-chevron-right text-sm"></i>
                            </div>
                        </button>
                    </div>

                </div>
            </main>
        </div>
        
        <!-- Navigation Bottom -->
        <?php include 'footer.php'; ?>
</body>
</html>
