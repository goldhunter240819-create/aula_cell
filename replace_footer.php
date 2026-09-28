<?php
$files = glob('apk/*.php');

foreach ($files as $file) {
    if (basename($file) == 'footer.php') continue;
    
    $content = file_get_contents($file);
    
    // Find the start of the footer
    // Usually it's: <div class="absolute bottom-0 left-0 right-0 w-full z-50 bg-white border-t border-slate-200 relative shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">
    // OR similar bg-white/90 classes.
    // Let's use a regex to match from the absolute bottom-0 div until the end of the file.
    
    // Some files might have already had this removed or different structure.
    $pattern = '/<div class="absolute bottom-0 left-0 right-0 w-full z-50.*<\/html>/is';
    
    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, "<?php include 'footer.php'; ?>\n</body>\n</html>", $content);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    } else {
        echo "Skipped $file (Pattern not found)\n";
    }
}
?>
