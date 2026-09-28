<?php
$files = glob('apk/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Fix body tag to ensure it NEVER scrolls
    // Match <body class="...">
    $content = preg_replace('/<body class="[^"]*"/s', '<body class="font-sans antialiased bg-slate-900 flex justify-center items-center h-[100dvh] overflow-hidden md:p-4"', $content);
    
    // Fix the main mobile container to strictly use h-[100dvh] on mobile and flex-col with overflow-hidden
    // Match <div class="w-full ..."> right after body
    $content = preg_replace('/<div class="w-full md:max-w-\[400px\][^"]*"/s', '<div class="w-full md:max-w-[400px] md:h-[800px] h-[100dvh] bg-slate-50 relative md:shadow-2xl md:rounded-[2.5rem] overflow-hidden flex flex-col md:border-8 md:border-slate-800"', $content);
    
    // Ensure the scrollable area is flex-1 overflow-y-auto
    // It's already flex-1 overflow-y-auto in most files, but let's make sure.
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
