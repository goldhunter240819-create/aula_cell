<?php
$files = glob('apk/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Fix the body tag to ensure it's a true flex container that centers content
    $content = preg_replace('/<body class="[^"]*"/s', '<body class="font-sans antialiased bg-slate-900 flex justify-center items-center h-[100dvh] overflow-hidden"', $content);
    
    // Fix the container to be perfectly floating on desktop (Mismifhda concept)
    // Mobile: w-full h-[100dvh] rounded-none
    // Desktop: w-[400px] h-[90dvh] max-h-[800px] rounded-[2.5rem]
    $content = preg_replace(
        '/<div class="w-full md:max-w-\[400px\][^"]*"/s',
        '<div class="w-full h-[100dvh] md:w-[400px] md:h-[90dvh] md:max-h-[800px] bg-slate-50 relative md:shadow-2xl md:rounded-[2.5rem] overflow-hidden flex flex-col md:border-8 md:border-slate-800"',
        $content
    );
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}
?>
