<?php
$files = glob('apk/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Update viewport meta tag
    $content = preg_replace(
        '/<meta name="viewport" content="([^"]+)"/',
        '<meta name="viewport" content="$1, viewport-fit=cover"',
        $content
    );
    
    // Fix duplicate viewport-fit=cover if it was already there
    $content = str_replace(', viewport-fit=cover, viewport-fit=cover', ', viewport-fit=cover', $content);
    
    // Update pb-safe definition
    $content = preg_replace(
        '/\.pb-safe\s*\{\s*padding-bottom:\s*env\(safe-area-inset-bottom,\s*1rem\);\s*\}/',
        '.pb-safe { padding-bottom: max(env(safe-area-inset-bottom), 1.5rem); }',
        $content
    );
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
