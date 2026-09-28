<?php
$files = glob('apk/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // 1. Change the footer class to absolute bottom-0
    $content = str_replace(
        'w-full z-50 mt-auto',
        'absolute bottom-0 left-0 right-0 w-full z-50',
        $content
    );
    
    // 2. Add extra padding bottom to the scrolling container so content doesn't hide behind absolute footer
    $content = preg_replace('/<div class="flex-1 overflow-y-auto([^"]*)">/', '<div class="flex-1 overflow-y-auto pb-32$1">', $content);
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
