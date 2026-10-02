<?php
require "../koneksi.php";

$bulan_ini = date("Y-m");

// Saldo Pribadi
$uid = $_SESSION['user_id'];
$q_saldo_pribadi = mysqli_query($conn, "SELECT SUM(saldo) as total FROM dompet WHERE tipe = 'Pribadi' AND user_id = $uid");
$saldo_pribadi = mysqli_fetch_assoc($q_saldo_pribadi)["total"] ?? 0;

// Detail Dompet Pribadi
$q_detail_pribadi = mysqli_query($conn, "SELECT nama_dompet, saldo FROM dompet WHERE tipe = 'Pribadi' AND user_id = $uid ORDER BY id ASC");
$detail_pribadi = [];
while($row = mysqli_fetch_assoc($q_detail_pribadi)) {
    $detail_pribadi[] = $row;
}

// Saldo Bisnis (Total)
$q_saldo_bisnis = mysqli_query($conn, "SELECT SUM(saldo) as total FROM dompet WHERE tipe = 'Bisnis'");
$saldo_bisnis = mysqli_fetch_assoc($q_saldo_bisnis)["total"] ?? 0;

// Detail Dompet Bisnis
$q_detail_bisnis = mysqli_query($conn, "SELECT nama_dompet, saldo FROM dompet WHERE tipe = 'Bisnis' ORDER BY id ASC");
$detail_bisnis = [];
while($row = mysqli_fetch_assoc($q_detail_bisnis)) {
    $detail_bisnis[] = $row;
}

// Total Hutang (Uang di luar)
$q_hutang = mysqli_query($conn, "SELECT SUM(harga_jual) as total FROM transaksi_penjualan WHERE status_pembayaran = 'Hutang'");
$total_hutang = ($q_hutang) ? (mysqli_fetch_assoc($q_hutang)['total'] ?? 0) : 0;

// Data User (Foto Profil)
$user_id = $_SESSION['user_id'];
$q_u = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id = '$user_id'");
$r_u = mysqli_fetch_assoc($q_u);
$foto = $r_u['foto_profil'] ?? '';
$foto_url = empty($foto) ? "../aulalogo.png" : "../uploads/" . $foto;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <title>Aula Cell - Mobile</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ["Nunito", "sans-serif"], },
                    colors: {
                        primary: "#2563eb", // Royal blue
                        secondary: "#3b82f6", 
                        bglight: "#f1f5f9",
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f172a; color: #334155; -webkit-tap-highlight-color: transparent; }
        .pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 1rem); }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        
        /* The header curve */
        .header-curve {
            border-bottom-left-radius: 2.5rem;
            border-bottom-right-radius: 2.5rem;
            box-shadow: 0 4px 20px -2px rgba(37, 99, 235, 0.3);
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 m-0 p-0 overflow-hidden">

    <!-- Mobile Frame -->
    <div class="fixed inset-0 w-full max-w-[28rem] mx-auto bg-slate-50 shadow-2xl overflow-hidden flex flex-col">
        
        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto pb-32 overflow-x-hidden pb-24">
            
            <!-- Clean Header (Mifhda style but Aula Cell colors) -->
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 header-curve pt-10 pb-16 px-6 relative text-white">
                
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2 mb-2 bg-white/20 w-max px-3 py-1 rounded-full text-xs font-bold backdrop-blur-sm">
                            <i class="fa-solid fa-store text-yellow-300"></i>
                            <span>Aula Cell</span>
                        </div>
                        <p class="text-sm text-blue-100 font-semibold">Selamat Datang,</p>
                        <h1 class="text-2xl font-extrabold tracking-tight">Owner <?= explode(' ', trim($_SESSION['nama_lengkap']))[0] ?></h1>
                    </div>
                    
                    <a href="pengaturan.php" class="w-12 h-12 rounded-full border-2 border-white/40 overflow-hidden bg-white/20 flex items-center justify-center shadow-inner">
                        <img src="<?= $foto_url ?>" class="w-full h-full object-cover">
                    </a>
                </div>
                
                <!-- Date Bar -->
                <div class="absolute -bottom-5 left-6 right-6 bg-white rounded-2xl p-4 shadow-lg flex justify-between items-center text-slate-700 border border-slate-100 z-10">
                    <div class="flex items-center gap-2 font-bold text-sm">
                        <i class="fa-regular fa-calendar text-primary"></i> <?= date('d M Y') ?>
                    </div>
                    <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-500 bg-emerald-50 px-2 py-1 rounded-md">
                        <i class="fa-solid fa-shield-halved"></i> Aman
                    </div>
                </div>
            </div>

            <!-- Main Body -->
            <main class="px-5 pt-12 pb-10">
                
                <div class="flex items-center gap-2 font-extrabold text-slate-800 mb-4">
                    <i class="fa-solid fa-wallet text-amber-500"></i>
                    <h2 class="text-lg">Ringkasan Saldo</h2>
                </div>
                
                <!-- Enhanced Minimalist Vertical Cards -->
                <div class="grid grid-cols-1 gap-4 mb-8">
                    <!-- Card Bisnis -->
                    <div class="bg-white rounded-[1.25rem] p-5 shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-slate-200 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-primary"></div>
                        
                        <!-- Header Card Bisnis -->
                        <div class="flex justify-between items-center mb-4 pl-2">
                            <div>
                                <p class="text-slate-500 text-[11px] font-extrabold uppercase tracking-widest mb-1">Total Modal Bisnis</p>
                                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Rp <?= number_format($saldo_bisnis, 0, ',', '.') ?></h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center">
                                <i class="fa-solid fa-store text-primary text-xl"></i>
                            </div>
                        </div>

                        <!-- Breakdown Saldo & Hutang -->
                        <div class="pl-2 pt-4 border-t border-slate-100 flex flex-col gap-2.5">
                            <?php foreach($detail_bisnis as $db): ?>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                    <span class="text-xs font-bold text-slate-600"><?= $db['nama_dompet'] ?></span>
                                </div>
                                <span class="text-xs font-black text-slate-800">Rp <?= number_format($db['saldo'], 0, ',', '.') ?></span>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="flex justify-between items-center mt-1 pt-2.5 border-t border-slate-50 border-dashed">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                                    <span class="text-xs font-bold text-rose-600">Uang di Luar (Piutang)</span>
                                </div>
                                <span class="text-xs font-black text-rose-600">Rp <?= number_format($total_hutang, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card Pribadi -->
                    <div class="bg-white rounded-[1.25rem] p-5 shadow-[0_8px_30px_rgba(0,0,0,0.04)] border border-slate-200 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500"></div>
                        
                        <!-- Header Card Pribadi -->
                        <div class="flex justify-between items-center mb-4 pl-2">
                            <div>
                                <p class="text-slate-500 text-[11px] font-extrabold uppercase tracking-widest mb-1">Total Uang Pribadi</p>
                                <h3 class="text-2xl font-black text-slate-800 tracking-tight">Rp <?= number_format($saldo_pribadi, 0, ',', '.') ?></h3>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center relative z-10">
                                <i class="fa-solid fa-user text-emerald-500 text-xl"></i>
                            </div>
                        </div>

                        <!-- Breakdown Saldo Pribadi -->
                        <div class="pl-2 pt-4 border-t border-slate-100 flex flex-col gap-2.5">
                            <?php foreach($detail_pribadi as $dp): ?>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                    <span class="text-xs font-bold text-slate-600"><?= $dp['nama_dompet'] ?></span>
                                </div>
                                <span class="text-xs font-black text-slate-800">Rp <?= number_format($dp['saldo'], 0, ',', '.') ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Quote Romantis / Motivasi -->
                <div class="mt-2 mb-8 bg-gradient-to-br from-blue-50/80 to-indigo-50/50 rounded-2xl p-4 border border-blue-100/50 flex gap-3 items-start shadow-sm">
                    <i class="fa-solid fa-quote-left text-blue-300 text-lg mt-0.5"></i>
                    <div>
                        <p class="text-xs font-semibold text-slate-600 italic leading-relaxed">
                            "Kita usahakan semuanya pelan-pelan ya. Apapun badainya, kita pasti bisa lewati sama-sama."
                        </p>
                        <p class="text-[10px] font-extrabold text-blue-500 mt-2 tracking-wide uppercase">&hearts; Semangat terus, Sayang!</p>
                    </div>
                </div>

            </main>
        </div>

        <!-- Clean Bottom Nav (Mifhda Style floating) -->
        <?php include 'footer.php'; ?>
</body>
</html>