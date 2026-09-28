<?php
$dirs = ['.', 'apk'];
$search = 'aulalogo.png';
$replace = 'aulalogo.png';

foreach ($dirs as $dir) {
    $files = array_merge(glob("$dir/*.php"), glob("$dir/*.json"), glob("$dir/*.js"));
    foreach ($files as $file) {
        $content = file_get_contents($file);
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, $replace, $content);
            file_put_contents($file, $content);
            echo "Updated $file\n";
        }
    }
}
?>
