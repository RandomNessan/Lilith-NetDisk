<?php
require __DIR__ . '/../lib/common.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf'] ?? '')) {
    http_response_code(400); exit('Bad Request');
}
if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    exit('文件上传失败');
}

$origName = $_FILES['file']['name'];
$tmpPath  = $_FILES['file']['tmp_name'];
$baseName = basename($origName);
$dst = rtrim($config['storage']['dl_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $baseName;

if (!@move_uploaded_file($tmpPath, $dst)) {
    exit('保存失败，请检查 /dl/ 目录写权限');
}

$files = load_files($config);
$stat  = @stat($dst);
$size  = $stat ? (int)$stat['size'] : 0;

$human = function($bytes){
    $u = ['B','KB','MB','GB','TB']; $i=0;
    while($bytes>=1024 && $i<count($u)-1){ $bytes/=1024; $i++; }
    return sprintf('%.2f %s', $bytes, $u[$i]);
};

$record = [
    'name'             => $baseName,
    'remark'           => trim($_POST['remark'] ?? ''),
    'size'             => $size,
    'size_human'       => $human($size),
    'created_at'       => date('Y-m-d H:i:s'),
    'downloads'        => 0,
    'last_download_at' => null,
];

$existsIdx = null;
foreach ($files as $idx => $f) {
    if (($f['name'] ?? '') === $baseName) { $existsIdx = $idx; break; }
}
if ($existsIdx !== null) {
    // 如覆盖同名文件，保留下载统计
    $record['downloads']        = isset($files[$existsIdx]['downloads']) ? (int)$files[$existsIdx]['downloads'] : 0;
    $record['last_download_at'] = $files[$existsIdx]['last_download_at'] ?? null;
    $files[$existsIdx] = $record;
} else {
    $files[] = $record;
}
save_files($config, $files);

header('Location: /admin/index.php');
