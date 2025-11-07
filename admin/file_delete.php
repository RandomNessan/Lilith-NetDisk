<?php
require __DIR__ . '/../lib/common.php';
require_login();

$name = $_GET['name'] ?? '';
$name = basename($name);
if ($name === '') { exit('缺少文件名'); }

$files = load_files($config);
$new   = [];
$deleted = false;

foreach ($files as $f) {
    if (($f['name']??'') === $name) {
        $path = rtrim($config['storage']['dl_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
        if (is_file($path)) @unlink($path);
        $deleted = true;
        continue;
    }
    $new[] = $f;
}
if ($deleted) save_files($config, $new);

header('Location: /admin/index.php');
