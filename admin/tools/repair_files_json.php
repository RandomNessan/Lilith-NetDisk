<?php
require __DIR__ . '/../../lib/common.php';
require_login();

$files = load_files($config);
$dlDir = rtrim($config['storage']['dl_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
$new   = [];

$human = function($bytes){
    $u = ['B','KB','MB','GB','TB']; $i=0;
    while($bytes>=1024 && $i<count($u)-1){ $bytes/=1024; $i++; }
    return sprintf('%.2f %s', $bytes, $u[$i]);
};

$kept = 0; $dropped = 0;
foreach ($files as $f) {
    $name = $f['name'] ?? '';
    if (!is_string($name) || $name === '') { $dropped++; continue; }
    $path = $dlDir . $name;
    if (!is_file($path)) { $dropped++; continue; }

    $stat = @stat($path);
    $size = $stat ? (int)$stat['size'] : (int)($f['size'] ?? 0);

    $new[] = [
        'name'             => $name,
        'remark'           => (string)($f['remark'] ?? ''),
        'size'             => $size,
        'size_human'       => $human($size),
        'created_at'       => (string)($f['created_at'] ?? date('Y-m-d H:i:s')),
        'downloads'        => isset($f['downloads']) ? (int)$f['downloads'] : 0,
        'last_download_at' => $f['last_download_at'] ?? null,
    ];
    $kept++;
}

save_files($config, $new);

header('Content-Type: text/plain; charset=utf-8');
echo "repair done.\nkept: $kept\nremoved: $dropped\n";
