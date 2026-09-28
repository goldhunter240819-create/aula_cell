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
<body class="font-sans antialiased bg-slate-900 flex justify-center items-center h-[100dvh] overflow-hidden">
    <div class="w-full h-[100dvh] md:max-w-[400px] md:h-[95dvh] md:max-h-[850px] bg-slate-50 relative md:shadow-2xl md:rounded-[2.5rem] overflow-hidden flex flex-col md:border-8 md:border-slate-800">
        
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
        <div class="absolute bottom-0 left-0 right-0 w-full z-50 bg-white/90 backdrop-blur-lg border-t border-slate-100 relative shadow-[0_-10px_30px_rgba(0,0,0,0.03)]">
            <div class="flex justify-around items-end px-2 pb-safe pt-2">
                <a href="index.php" class="flex flex-col items-center gap-1 text-slate-400 hover:text-primary w-16 pb-2">
                    <i class="fa-solid fa-house text-lg"></i>
                    <span class="text-[10px] font-bold">Beranda</span>
                </a>
                <a href="laporan_bisnis.php" class="flex flex-col items-center gap-1 text-slate-400 hover:text-primary transition-colors w-16 pb-2">
                    <i class="fa-solid fa-store text-lg"></i>
                    <span class="text-[10px] font-bold">Bisnis</span>
                </a>
                <div class="relative flex flex-col items-center justify-end w-16 h-full z-20">
                    <button onclick="document.getElementById('catatModal').classList.remove('hidden')" class="absolute -top-12 w-16 h-16 bg-primary rounded-full flex items-center justify-center text-white shadow-[0_8px_20px_rgba(37,99,235,0.4)] active:scale-95 transition-transform ring-[6px] ring-white focus:outline-none">
                        <i class="fa-solid fa-plus text-xl"></i>
                    </button>
                    <span class="text-[10px] font-bold text-slate-500 pb-2">Catat</span>
                </div>
                <a href="laporan_pribadi.php" class="flex flex-col items-center gap-1 text-primary transition-colors w-16 pb-2">
                    <i class="fa-solid fa-user text-lg"></i>
                    <span class="text-[10px] font-bold">Pribadi</span>
                </a>
                <a href="pengaturan.php" class="flex flex-col items-center gap-1 text-slate-400 hover:text-primary transition-colors w-16 pb-2">
                    <i class="fa-solid fa-gear text-lg"></i>
                    <span class="text-[10px] font-bold">Profil</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Catat (dari navigation bawah) -->
    <div id="catatModal" class="hidden fixed inset-0 z-[100] flex items-end justify-center sm:items-center">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('catatModal').classList.add('hidden')"></div>
        <div class="bg-white w-full md:w-[400px] rounded-t-[2.5rem] md:rounded-[2.5rem] p-8 relative transform transition-transform shadow-[0_-20px_40px_rgba(0,0,0,0.1)] pb-safe modal-enter">
            <div class="absolute top-4 left-1/2 -translate-x-1/2 w-16 h-1.5 bg-slate-200 rounded-full"></div>
            
            <div class="text-center mt-2 mb-8">
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Mau Catat Apa?</h3>
                <p class="text-xs font-bold text-slate-400 mt-2">Pilih jenis transaksi hari ini</p>
            </div>
            
            <div class="flex flex-col gap-4">
                <a href="jual_pulsa.php" class="bg-slate-50 border border-slate-100 rounded-[2rem] p-5 flex items-center gap-5 hover:bg-blue-50 hover:border-blue-100 transition-all active:scale-[0.98] group">
                    <div class="w-14 h-14 rounded-[1.25rem] bg-white text-blue-500 flex items-center justify-center text-xl shadow-sm group-hover:bg-blue-500 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-black text-slate-800 text-base">Bisnis / Konter</h4>
                        <p class="text-[11px] font-bold text-slate-400 mt-0.5">Jual pulsa, topup, dll</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 group-hover:text-blue-500 transition-colors"></i>
                </a>
                
                <a href="transaksi_umum.php" class="bg-slate-50 border border-slate-100 rounded-[2rem] p-5 flex items-center gap-5 hover:bg-emerald-50 hover:border-emerald-100 transition-all active:scale-[0.98] group">
                    <div class="w-14 h-14 rounded-[1.25rem] bg-white text-emerald-500 flex items-center justify-center text-xl shadow-sm group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-black text-slate-800 text-base">Uang Pribadi</h4>
                        <p class="text-[11px] font-bold text-slate-400 mt-0.5">Uang jajan, bensin, dll</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300 group-hover:text-emerald-500 transition-colors"></i>
                </a>
            </div>
            
            <button onclick="document.getElementById('catatModal').classList.add('hidden')" class="w-full mt-8 py-4 bg-slate-100 text-slate-500 font-black rounded-full active:scale-95 transition-transform text-sm hover:bg-slate-200">
                Tutup
            </button>
        </div>
    </div>

    <!-- Modal Pemasukan -->
    <div id="modalPemasukan" class="hidden fixed inset-0 z-[110] flex items-end justify-center sm:items-center">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" onclick="closeModal('modalPemasukan')"></div>
        <div class="bg-white w-full md:w-[400px] rounded-t-[2.5rem] md:rounded-[2.5rem] p-8 relative transform transition-transform shadow-[0_-20px_50px_rgba(16,185,129,0.15)] pb-safe modal-enter max-h-[90vh] flex flex-col">
            <div class="absolute top-4 left-1/2 -translate-x-1/2 w-16 h-1.5 bg-slate-200 rounded-full shrink-0"></div>
            
            <div class="flex items-center gap-5 mb-8 mt-2 shrink-0">
                <div class="w-16 h-16 bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-[1.5rem] shadow-lg shadow-emerald-500/30 flex items-center justify-center text-white text-2xl">
                    <i class="fa-solid fa-arrow-down"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">Pemasukan</h3>
                    <p class="text-xs font-bold text-emerald-500 mt-1">Hore! Dapat duit masuk 🎉</p>
                </div>
            </div>

            <div class="overflow-y-auto flex-1 pb-4 scrollbar-hide -mx-2 px-2">
                <form method="POST" action="" class="flex flex-col gap-5">
                    <input type="hidden" name="jenis" value="Pemasukan">
                    
                    <div class="relative">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 ml-1">Tanggal</label>
                        <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>" class="w-full px-4 py-4 glass-input rounded-2xl text-sm font-bold text-slate-700 transition-all">
                    </div>
                    <div class="relative">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 ml-1">Nominal (Rp)</label>
                        <input type="number" name="nominal" required placeholder="50.000" class="w-full px-4 py-4 glass-input rounded-2xl text-sm font-black text-slate-800 transition-all placeholder:font-medium placeholder:text-slate-300">
                    </div>

                    <div class="relative">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 ml-1">Simpan Di Mana?</label>
                        <div class="relative">
                            <select name="id_dompet" required class="w-full px-4 py-4 glass-input rounded-2xl text-sm font-bold text-slate-700 transition-all appearance-none cursor-pointer">
                                <?= $dompet_options ?>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 ml-1">Catatan</label>
                        <textarea name="keterangan" rows="2" placeholder="Cth: Dikasih nenek, Gaji bulanan..." class="w-full px-4 py-4 glass-input rounded-2xl text-sm font-bold text-slate-700 transition-all placeholder:font-medium placeholder:text-slate-300"></textarea>
                    </div>
                    
                    <div class="flex gap-3 mt-4 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modalPemasukan')" class="w-1/3 py-4 bg-slate-50 text-slate-400 font-black rounded-2xl active:scale-95 transition-transform text-sm hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="submit" name="submit" class="w-2/3 bg-gradient-to-r from-emerald-500 to-emerald-400 text-white font-black py-4 rounded-2xl shadow-lg shadow-emerald-500/40 active:scale-95 transition-transform text-sm">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Pengeluaran -->
    <div id="modalPengeluaran" class="hidden fixed inset-0 z-[110] flex items-end justify-center sm:items-center">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-md transition-opacity" onclick="closeModal('modalPengeluaran')"></div>
        <div class="bg-white w-full md:w-[400px] rounded-t-[2.5rem] md:rounded-[2.5rem] p-8 relative transform transition-transform shadow-[0_-20px_50px_rgba(239,68,68,0.15)] pb-safe modal-enter max-h-[90vh] flex flex-col">
            <div class="absolute top-4 left-1/2 -translate-x-1/2 w-16 h-1.5 bg-slate-200 rounded-full shrink-0"></div>
            
            <div class="flex items-center gap-5 mb-8 mt-2 shrink-0">
                <div class="w-16 h-16 bg-gradient-to-br from-red-400 to-red-500 rounded-[1.5rem] shadow-lg shadow-red-500/30 flex items-center justify-center text-white text-2xl">
                    <i class="fa-solid fa-arrow-up"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">Pengeluaran</h3>
                    <p class="text-xs font-bold text-red-500 mt-1">Yah, duit keluar lagi 💸</p>
                </div>
            </div>

            <div class="overflow-y-auto flex-1 pb-4 scrollbar-hide -mx-2 px-2">
                <form method="POST" action="" class="flex flex-col gap-5">
                    <input type="hidden" name="jenis" value="Pengeluaran">
                    
                    <div class="relative">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 ml-1">Tanggal</label>
                        <input type="date" name="tanggal" required value="<?= date('Y-m-d') ?>" class="w-full px-4 py-4 glass-input rounded-2xl text-sm font-bold text-slate-700 transition-all">
                    </div>
                    <div class="relative">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 ml-1">Nominal (Rp)</label>
                        <input type="number" name="nominal" required placeholder="50.000" class="w-full px-4 py-4 glass-input rounded-2xl text-sm font-black text-slate-800 transition-all placeholder:font-medium placeholder:text-slate-300">
                    </div>

                    <div class="relative">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 ml-1">Bayar Pakai</label>
                        <div class="relative">
                            <select name="id_dompet" required class="w-full px-4 py-4 glass-input rounded-2xl text-sm font-bold text-slate-700 transition-all appearance-none cursor-pointer">
                                <?= $dompet_options ?>
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <div class="relative">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 ml-1">Catatan</label>
                        <textarea name="keterangan" rows="2" placeholder="Cth: Beli cilok, Bensin motor..." class="w-full px-4 py-4 glass-input rounded-2xl text-sm font-bold text-slate-700 transition-all placeholder:font-medium placeholder:text-slate-300"></textarea>
                    </div>
                    
                    <div class="flex gap-3 mt-4 pt-4 border-t border-slate-100">
                        <button type="button" onclick="closeModal('modalPengeluaran')" class="w-1/3 py-4 bg-slate-50 text-slate-400 font-black rounded-2xl active:scale-95 transition-transform text-sm hover:bg-slate-100">
                            Batal
                        </button>
                        <button type="submit" name="submit" class="w-2/3 bg-gradient-to-r from-red-500 to-red-400 text-white font-black py-4 rounded-2xl shadow-lg shadow-red-500/40 active:scale-95 transition-transform text-sm">
                            Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
        }
        function closeModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('hidden');
        }
    </script>
</body>
</html>
