<?php
$files = glob('apk/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Update pb-safe definition to be much taller
    $content = preg_replace(
        '/\.pb-safe\s*\{\s*padding-bottom:\s*max\(env\(safe-area-inset-bottom\),\s*1\.5rem\);\s*\}/',
        '.pb-safe { padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 2rem); }',
        $content
    );
    
    // Also add extra bottom padding to main tag so content doesn't hide behind the taller nav
    $content = preg_replace('/<main class="([^"]+)">/', '<main class="$1 pb-10">', $content);
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
