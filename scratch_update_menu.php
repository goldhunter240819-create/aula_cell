<?php
$files = [
    'index.php',
    'jual_pulsa.php',
    'kategori.php',
    'laporan_bisnis.php',
    'buku_hutang.php',
    'transaksi_umum.php',
    'laporan_pribadi.php',
    'pengaturan.php'
];

$search = <<<'EOD'
            <!-- Bisnis Dropdown -->
            <details class="group" <?= ($current_page == "jual_pulsa.php" || $current_page == "laporan_bisnis.php") ? "open" : "" ?>>
                <summary class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl text-blue-200 hover:bg-white/10 hover:text-white transition-colors cursor-pointer select-none">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-store w-4 text-center"></i> Bisnis
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
                </summary>
                <div class="flex flex-col gap-1 pl-4 pr-2 py-1 mt-1 border-l-2 border-white/10 ml-6">
                    <a href="jual_pulsa.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == "jual_pulsa.php") ? "bg-white/20 text-white font-bold" : "text-blue-200 hover:bg-white/10 hover:text-white" ?>">
                        <i class="fa-solid fa-pen-to-square w-4 mr-1 text-center"></i> Pencatatan
                    </a>
EOD;

$replace = <<<'EOD'
            <!-- Bisnis Dropdown -->
            <details class="group" <?= ($current_page == "jual_pulsa.php" || $current_page == "laporan_bisnis.php" || $current_page == "transaksi_bisnis_umum.php") ? "open" : "" ?>>
                <summary class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl text-blue-200 hover:bg-white/10 hover:text-white transition-colors cursor-pointer select-none">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-store w-4 text-center"></i> Bisnis
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180"></i>
                </summary>
                <div class="flex flex-col gap-1 pl-4 pr-2 py-1 mt-1 border-l-2 border-white/10 ml-6">
                    <a href="jual_pulsa.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == "jual_pulsa.php") ? "bg-white/20 text-white font-bold" : "text-blue-200 hover:bg-white/10 hover:text-white" ?>">
                        <i class="fa-solid fa-pen-to-square w-4 mr-1 text-center"></i> Jual Beli Pulsa
                    </a>
                    <a href="transaksi_bisnis_umum.php" class="text-sm py-2 px-3 rounded-lg transition-colors <?= ($current_page == "transaksi_bisnis_umum.php") ? "bg-white/20 text-white font-bold" : "text-blue-200 hover:bg-white/10 hover:text-white" ?>">
                        <i class="fa-solid fa-briefcase w-4 mr-1 text-center"></i> Operasional Bisnis
                    </a>
EOD;

foreach($files as $file) {
    if(file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace($search, $replace, $content);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
