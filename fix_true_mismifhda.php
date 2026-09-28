<?php
$files = glob('apk/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // 1. Fix body tag: remove md:p-4, change background color slightly
    $content = preg_replace('/<body class="[^"]*"/s', '<body class="font-sans antialiased bg-slate-900 flex justify-center h-[100dvh] overflow-hidden"', $content);
    
    // 2. Fix the main mobile container: remove all the 'md:rounded', 'md:border', 'md:h-[90dvh]', 'md:max-h-[800px]', 'md:shadow-2xl'
    // Make it simple: w-full max-w-md h-[100dvh] bg-slate-50 relative shadow-xl overflow-hidden flex flex-col
    $content = preg_replace(
        '/<div class="w-full h-\[100dvh\] md:w-\[400px\] md:h-\[90dvh\] md:max-h-\[800px\] bg-slate-50 relative md:shadow-2xl md:rounded-\[2\.5rem\] overflow-hidden flex flex-col md:border-8 md:border-slate-800"/s',
        '<div class="w-full max-w-md h-[100dvh] bg-slate-50 relative shadow-2xl overflow-hidden flex flex-col"',
        $content
    );
    
    // Also handle any files that might not have been matched by the previous regex
    $content = preg_replace(
        '/<div class="w-full md:max-w-\[400px\][^"]*"/s',
        '<div class="w-full max-w-md h-[100dvh] bg-slate-50 relative shadow-2xl overflow-hidden flex flex-col"',
        $content
    );
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
