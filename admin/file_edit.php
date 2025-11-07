<?php
require __DIR__ . '/../lib/common.php';
require_login();

$name = $_GET['name'] ?? '';
$name = basename($name);
if ($name === '') { exit('缺少文件名'); }

$files = load_files($config);
$key = null;
foreach ($files as $i=>$f) if (($f['name']??'') === $name) { $key=$i; break; }
if ($key === null) exit('未找到该文件');

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) exit('CSRF invalid');
    $files[$key]['remark'] = trim($_POST['remark'] ?? '');
    save_files($config,$files);
    header('Location: /admin/index.php'); exit;
}

$f = $files[$key];
?>
<!doctype html>
<html lang="zh-CN">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>修改信息</title>
<style>
body{font-family:system-ui,Segoe UI,Roboto,Helvetica,Arial;background:#0d1025;color:#e8ecff;margin:0}
.container{max-width:720px;margin:40px auto;padding:0 16px}
.card{background:#14183a;border:1px solid #2a3070;border-radius:14px;padding:16px}
input[type=text]{width:100%;padding:10px;border-radius:8px;border:1px solid #2a3070;background:#0e1233;color:#fff}
.btn{padding:8px 12px;border-radius:8px;border:1px solid #3b48a3;background:#222a66;color:#fff;text-decoration:none}
.meta{font-size:12px;color:#baccff;margin-bottom:10px}
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <h2>修改备注：<?=e($f['name'])?></h2>
    <div class="meta">
      下载数：<?=e($f['downloads'] ?? 0)?>，
      最后下载：<?=e($f['last_download_at'] ?? '-')?>
    </div>
    <form method="post">
      <input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
      <label>备注</label>
      <input type="text" name="remark" value="<?=e($f['remark'] ?? '')?>">
      <p style="margin-top:12px">
        <button class="btn" type="submit">保存</button>
        <a class="btn" href="/admin/index.php">返回</a>
      </p>
    </form>
  </div>
</div>
</body>
</html>
