<?php
$files = glob('apk/*.php');

foreach ($files as $file) {
    if (basename($file) == 'footer.php') continue;
    
    $content = file_get_contents($file);
    
    // Replace the main container div (which is the first one after body)
    // We will look for <div class="w-full h-[100dvh]... "> or similar
    $content = preg_replace(
        '/<div class="w-full[^"]*md:max-w-\[400px\][^"]*"/s',
        '<div class="w-full max-w-[400px] h-[100dvh] bg-slate-50 relative shadow-2xl overflow-hidden flex flex-col"',
        $content
    );
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
