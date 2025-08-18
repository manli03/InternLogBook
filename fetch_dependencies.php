<?php
$urls = [
'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js' => 'js/alpine.min.js',
'https://cdnjs.cloudflare.com/ajax/libs/turn.js/3/turn.min.js' => 'js/turn.min.js'
];

foreach ($urls as $url => $path) {
$content = file_get_contents($url);
if ($content === false) {
echo "Failed to download: $url\n";
continue;
}

if (file_put_contents($path, $content) === false) {
echo "Failed to save: $path\n";
continue;
}

echo "Successfully downloaded and saved: $path\n";
}