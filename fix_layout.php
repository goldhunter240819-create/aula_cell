<?php
$files = glob('apk/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Change body and main container from min-h-screen to h-[100dvh]
    // 1. Find body tag
    $content = preg_replace('/<body class="([^"]*)min-h-screen([^"]*)">/', '<body class="$1h-[100dvh] overflow-hidden$2">', $content);
    
    // 2. Find container div
    $content = preg_replace('/<div class="([^"]*)min-h-screen([^"]*)">/', '<div class="$1h-[100dvh]$2">', $content);
    
    // Restore the padding-bottom of pb-safe back to normal so it's not ridiculously thick on desktop
    $content = preg_replace(
        '/\.pb-safe\s*\{\s*padding-bottom:\s*calc\(env\(safe-area-inset-bottom,\s*0px\)\s*\+\s*2rem\);\s*\}/',
        '.pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 1rem); }',
        $content
    );
    
    // Also change fixed bottom nav for safety if they ever change the container back to scrolling
    // Actually, making it h-[100dvh] and flex-col with mt-auto is exactly what keeps it at the bottom.
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
