<?php
$files = glob('apk/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Fix the container div to be responsive in height on desktop
    $content = preg_replace(
        '/<div class="w-full md:max-w-\[400px\] md:h-\[800px\] h-\[100dvh\] bg-slate-50 relative md:shadow-2xl md:rounded-\[2\.5rem\] overflow-hidden flex flex-col md:border-8 md:border-slate-800"/',
        '<div class="w-full h-[100dvh] md:max-w-[400px] md:h-[95dvh] md:max-h-[850px] bg-slate-50 relative md:shadow-2xl md:rounded-[2.5rem] overflow-hidden flex flex-col md:border-8 md:border-slate-800"',
        $content
    );
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
