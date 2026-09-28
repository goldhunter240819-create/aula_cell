<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
        <!-- Clean Bottom Nav (Mifhda Style floating) -->
        <div class="w-full z-50 mt-auto bg-white border-t border-slate-200 relative shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
            <div class="flex justify-around items-end px-2 pb-2 pt-2">
                
                <a href="index.php" class="flex flex-col items-center gap-1 <?= $current_page == 'index.php' ? 'text-primary' : 'text-slate-400 hover:text-primary' ?> transition-colors w-16 pb-2">
                    <i class="fa-solid fa-house text-lg"></i>
                    <span class="text-[10px] font-bold">Beranda</span>
                </a>
                
                <a href="laporan_bisnis.php" class="flex flex-col items-center gap-1 <?= $current_page == 'laporan_bisnis.php' ? 'text-primary' : 'text-slate-400 hover:text-primary' ?> transition-colors w-16 pb-2">
                    <i class="fa-solid fa-store text-lg"></i>
                    <span class="text-[10px] font-bold">Bisnis</span>
                </a>
                
                <!-- Floating Center Button -->
                <div class="relative flex flex-col items-center justify-end w-16 h-full z-20">
                    <button onclick="document.getElementById('catatModal').classList.remove('hidden')" class="absolute -top-12 w-16 h-16 bg-primary rounded-full flex items-center justify-center text-white shadow-[0_8px_20px_rgba(37,99,235,0.4)] active:scale-95 transition-transform ring-[8px] ring-bglight focus:outline-none">
                        <i class="fa-solid fa-plus text-xl"></i>
                    </button>
                    <span class="text-[10px] font-bold text-slate-500 pb-2">Catat</span>
                </div>
                
                <a href="laporan_pribadi.php" class="flex flex-col items-center gap-1 <?= $current_page == 'laporan_pribadi.php' ? 'text-primary' : 'text-slate-400 hover:text-primary' ?> transition-colors w-16 pb-2">
                    <i class="fa-solid fa-user text-lg"></i>
                    <span class="text-[10px] font-bold">Pribadi</span>
                </a>
                
                <a href="pengaturan.php" class="flex flex-col items-center gap-1 <?= $current_page == 'pengaturan.php' ? 'text-primary' : 'text-slate-400 hover:text-primary' ?> transition-colors w-16 pb-2">
                    <i class="fa-solid fa-gear text-lg"></i>
                    <span class="text-[10px] font-bold">Profil</span>
                </a>
            </div>
        </div>
        
    </div>
    
    <!-- Modal Catat -->
    <div id="catatModal" class="hidden fixed inset-0 z-[100] flex items-end justify-center sm:items-center">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('catatModal').classList.add('hidden')"></div>
        
        <!-- Modal Panel -->
        <div class="bg-white w-full md:w-[400px] rounded-t-[2rem] md:rounded-[2rem] p-6 relative transform transition-transform shadow-2xl pb-safe border-t border-slate-100">
            <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-6"></div>
            
            <h3 class="text-xl font-black text-slate-800 text-center mb-2">Mau Catat Apa Nih?</h3>
            <p class="text-xs font-bold text-slate-400 text-center mb-6">Pilih jenis transaksi yang mau dicatat hari ini</p>
            
            <div class="flex flex-col gap-3">
                <a href="jual_pulsa.php" class="bg-blue-50/50 border border-blue-100 rounded-2xl p-4 flex items-center gap-4 hover:bg-blue-50 transition-colors active:scale-95">
                    <div class="w-12 h-12 rounded-full bg-blue-100 text-primary flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-extrabold text-slate-800 text-sm">Jual Beli Konter</h4>
                        <p class="text-[10px] font-bold text-slate-500 mt-0.5">Jual pulsa, topup, dll</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300"></i>
                </a>

                <a href="transaksi_bisnis_umum.php" class="bg-purple-50/50 border border-purple-100 rounded-2xl p-4 flex items-center gap-4 hover:bg-purple-50 transition-colors active:scale-95">
                    <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-500 flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-extrabold text-slate-800 text-sm">Operasional Bisnis</h4>
                        <p class="text-[10px] font-bold text-slate-500 mt-0.5">Bayar listrik, modal, dll</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300"></i>
                </a>
                
                <a href="transaksi_umum.php" class="bg-emerald-50/50 border border-emerald-100 rounded-2xl p-4 flex items-center gap-4 hover:bg-emerald-50 transition-colors active:scale-95">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-500 flex items-center justify-center text-lg shadow-sm">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-extrabold text-slate-800 text-sm">Transaksi Pribadi</h4>
                        <p class="text-[10px] font-bold text-slate-500 mt-0.5">Uang jajan, bensin, & tabungan</p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-slate-300"></i>
                </a>
            </div>
            
            <button onclick="document.getElementById('catatModal').classList.add('hidden')" class="w-full mt-6 py-4 bg-slate-100 text-slate-500 font-extrabold rounded-[1.25rem] active:scale-95 transition-transform text-sm hover:bg-slate-200">
                Batal
            </button>
        </div>
    </div>
