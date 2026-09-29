<?php
$files = glob("*.php");
$style = "
    <style>
        body { background-color: #e2e8f0; color: #1e293b; }
        .glass-card { background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>";

foreach($files as $file) {
    $content = file_get_contents($file);
    if(strpos($content, '<!-- Sidebar -->') !== false) {
        // Update padding inside aside
        $content = preg_replace(
            '/<aside class="w-full md:w-64 bg-gradient-to-b from-blue-900 to-primary text-white p-5 flex flex-col gap-6 rounded-3xl md:h-\[calc\(100vh-3rem\)\] sticky top-6 z-20 shadow-2xl overflow-y-auto border border-blue-800\/50">/', 
            '<aside class="w-full md:w-64 bg-gradient-to-b from-blue-900 to-primary text-white p-4 flex flex-col gap-4 rounded-3xl md:h-[calc(100vh-3rem)] sticky top-6 z-20 shadow-2xl overflow-y-auto border border-blue-800/50 no-scrollbar">', 
            $content
        );

        // Update nav gap
        $content = preg_replace(
            '/<nav class="flex-1 flex flex-col gap-1 mt-2 text-sm">/',
            '<nav class="flex-1 flex flex-col gap-1 mt-2 text-sm">',
            $content
        );

        // Update main links padding px-4 py-3 -> px-3 py-2
        $content = str_replace('px-3 py-2 rounded-xl transition-colors', 'px-3 py-2 rounded-xl transition-colors', $content);
        
        // Update summary padding
        $content = str_replace('px-3 py-2 rounded-xl text-blue-200 hover:bg-white/10 hover:text-white transition-colors cursor-pointer select-none', 'px-3 py-2 rounded-xl text-blue-200 hover:bg-white/10 hover:text-white transition-colors cursor-pointer select-none', $content);
        
        // Update sub-links padding py-2 px-3 -> py-1.5 px-3
        $content = str_replace('text-sm py-1.5 px-3 rounded-lg', 'text-sm py-1.5 px-3 rounded-lg', $content);
        
        // Sub-menu margin top mt-1 -> mt-0.5
        $content = str_replace('mt-0.5 border-l-2', 'mt-0.5 border-l-2', $content);
        
        // Add scrollbar style
        $content = preg_replace('/<style>\s*body \{ background-color: #e2e8f0; color: #1e293b; \}\s*\.glass-card \{ background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba\(0, 0, 0, 0\.05\); \}\s*<\/style>/', $style, $content);
        
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
?>
