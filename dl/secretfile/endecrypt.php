<?php
// 解析 Base64 并 302 跳转，同时统计下载数
require __DIR__ . '/../../lib/common.php';

// 1) 优先读 ?b64=xxxxx（Nginx rewrite 推荐）
$b64 = $_GET['b64'] ?? null;

// 2) 没有 ?b64= 时，从请求路径尾段取
if (!$b64) {
    $path = $_SERVER['REQUEST_URI'] ?? '';
    $b64  = substr($path, strrpos($path, '/') + 1);
}

$b64 = urldecode((string)$b64);

// 解码并校验
$url = base64_decode($b64, true);
if (!$url || !preg_match('~^https?://~i', $url)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Invalid link");
}

// 统计（仅统计本站清单内的文件）
$parsed_path = parse_url($url, PHP_URL_PATH);
$file_name   = $parsed_path ? basename($parsed_path) : null;
if ($file_name) {
    increment_download($config, $file_name);
}

// 302 跳转到真实文件
header('Location: ' . $url, true, 302);
exit;
