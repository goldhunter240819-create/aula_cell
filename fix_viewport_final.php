<?php
// Fix all apk/*.php files to use fixed positioning instead of h-[100dvh]
// This solves the address bar show/hide issue on mobile Chrome

$files = glob('apk/*.php');

foreach ($files as $file) {
    if (basename($file) == 'footer.php') continue;
    
    $content = file_get_contents($file);
    
    // 1. Fix body: remove h-[100dvh] and flex centering, just make it overflow-hidden with 0 margin
    $content = preg_replace(
        '/<body class="[^"]*"/',
        '<body class="font-sans antialiased bg-slate-900 m-0 p-0 overflow-hidden"',
        $content
    );
    
    // 2. Fix main container: use fixed inset-0 instead of h-[100dvh]
    // This ALWAYS fills exactly the visible viewport, no matter what the address bar does
    $content = preg_replace(
        '/<div class="w-full max-w-\[400px\] h-\[100dvh\] bg-slate-50 relative shadow-2xl overflow-hidden flex flex-col"/',
        '<div class="fixed inset-0 w-full max-w-[28rem] mx-auto bg-slate-50 shadow-2xl overflow-hidden flex flex-col"',
        $content
    );
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
