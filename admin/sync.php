<?php
require __DIR__ . '/../lib/common.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Method Not Allowed");
}
if (!csrf_check($_POST['csrf'] ?? '')) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    exit("CSRF invalid");
}

$dlDir = rtrim($config['storage']['dl_dir'], '/');
if (!is_dir($dlDir)) {
    @mkdir($dlDir, 0755, true);
}

/** 人类可读尺寸 */
$human = function($bytes){
    $u=['B','KB','MB','GB','TB']; $i=0;
    while($bytes>=1024 && $i<count($u)-1){$bytes/=1024;$i++;}
    return sprintf('%.2f %s',$bytes,$u[$i]);
};

$existing = load_files($config);
$mapOld   = [];
foreach ($existing as $it) {
    $n = $it['name'] ?? null;
    if (is_string($n) && $n!=='') $mapOld[$n] = $it;
}

$newList = [];
$entries = @scandir($dlDir) ?: [];
foreach ($entries as $fn) {
    if ($fn === '.' || $fn === '..') continue;
    if ($fn === '.htaccess') continue;
    if ($fn === 'secretfile') continue; // 跳过子目录
    $full = $dlDir . '/' . $fn;

    // 只同步“文件”，忽略目录
    if (is_dir($full)) continue;
    if (!is_file($full)) continue;

    $size = @filesize($full);
    $mtime= @filemtime($full);
    $old  = $mapOld[$fn] ?? [];

    $new  = [
        'name'       => $fn,
        'size'       => (int)$size,
        'size_human' => $human((int)$size),
        // 如果已有 created_at 就保留；否则用文件 mtime
        'created_at' => isset($old['created_at']) && is_string($old['created_at']) && $old['created_at'] !== ''
                        ? $old['created_at']
                        : date('Y-m-d H:i:s', $mtime ?: time()),
        // 保留备注与下载统计
        'remark'           => (string)($old['remark'] ?? ''),
        'downloads'        => (int)($old['downloads'] ?? 0),
        'last_download_at' => isset($old['last_download_at']) ? (string)$old['last_download_at'] : null,
    ];

    $newList[] = $new;
}

// 可选：按文件名排序（也可以按时间）
// usort($newList, fn($a,$b)=>strcmp($a['name'],$b['name']));

// 保存
save_files($config, $newList);

// 回到列表并提示
header('Location: /admin/index.php?synced=1');
exit;
