<?php
$config = require __DIR__ . '/../config.php';
date_default_timezone_set($config['timezone']);
session_start();

/* ---------- 目录确保 ---------- */
if (!is_dir($config['storage']['dl_dir'])) {
    @mkdir($config['storage']['dl_dir'], 0755, true);
}
if (!is_dir(dirname($config['storage']['files_json']))) {
    @mkdir(dirname($config['storage']['files_json']), 0755, true);
}
if (!file_exists($config['storage']['files_json'])) {
    file_put_contents($config['storage']['files_json'], json_encode([],
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

/* ---------- Auth 存储（data/auth.json） ---------- */
function auth_path(): string {
    return __DIR__ . '/../data/auth.json';
}
function ensure_auth_migrated() {
    $path = auth_path();
    if (!file_exists(dirname($path))) {
        @mkdir(dirname($path), 0755, true);
    }
    if (!file_exists($path)) {
        // 首次迁移：从 config.php 读取
        $cfg = $GLOBALS['config'];
        $user = (string)($cfg['admin_user'] ?? 'admin');
        $pass = (string)($cfg['admin_pass'] ?? 'admin123');
        $data = [
            'user'       => $user,
            'pass_hash'  => password_hash($pass, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }
}
function load_auth(): array {
    ensure_auth_migrated();
    $json = @file_get_contents(auth_path());
    $arr  = json_decode($json, true);
    return is_array($arr) ? $arr : [];
}
function save_auth(string $user, string $pass_hash) {
    $data = [
        'user'       => $user,
        'pass_hash'  => $pass_hash,
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    file_put_contents(auth_path(), json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}
function check_login_credential(string $user, string $pass): bool {
    $auth = load_auth();
    return isset($auth['user'], $auth['pass_hash'])
        && hash_equals($auth['user'], $user)
        && password_verify($pass, $auth['pass_hash']);
}
function current_admin_username(): string {
    $auth = load_auth();
    return (string)($auth['user'] ?? 'admin');
}

/* ---------- Base URL 自动识别（支持反代/Cloudflare） ---------- */
function detect_base_url_from_request(): string {
    $proto = null;
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $proto = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
    } elseif (!empty($_SERVER['HTTP_CF_VISITOR'])) {
        $cf = json_decode($_SERVER['HTTP_CF_VISITOR'], true);
        if (!empty($cf['scheme'])) $proto = strtolower($cf['scheme']);
    } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $proto = 'https';
    }
    if (!$proto) $proto = 'http';

    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    if (strpos($host, ':') === false) {
        $port = (int)($_SERVER['SERVER_PORT'] ?? 80);
        $is_default = ($proto === 'http' && $port === 80) || ($proto === 'https' && $port === 443);
        if (!$is_default) $host .= ':' . $port;
    }
    return $proto . '://' . $host;
}
function base_url(): string {
    static $cached = null;
    if ($cached !== null) return $cached;
    $cfg = $GLOBALS['config']['base_url'] ?? null;
    $cached = is_string($cfg) && trim($cfg) !== '' ? rtrim($cfg, '/') : rtrim(detect_base_url_from_request(), '/');
    return $cached;
}
function site_url(string $path = ''): string { return base_url() . '/' . ltrim($path, '/'); }

/* ---------- CSRF ---------- */
function csrf_token() { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_check($token) { return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token ?? ''); }

/* ---------- 登录 ---------- */
function is_logged_in() { return !empty($_SESSION['admin_logged_in']); }
function require_login() { if (!is_logged_in()) { header('Location: /admin/login.php'); exit; } }

/* ---------- JSON 读写（带锁） ---------- */
function load_files($config) {
    $p = $config['storage']['files_json'];
    $json = @file_get_contents($p);
    $arr  = json_decode($json, true);
    return is_array($arr) ? $arr : [];
}
function save_files($config, $arr) {
    $p = $config['storage']['files_json'];
    file_put_contents($p, json_encode(array_values($arr),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}

/* ---------- 链接生成（容错） ---------- */
function build_public_link($file_name) : string {
    if (!is_string($file_name) || $file_name === '') return '#';
    return site_url('dl/' . rawurlencode($file_name));
}
function build_hidden_link($config, $file_name) : string {
    if (!is_string($file_name) || $file_name === '') return '#';
    $real = build_public_link($file_name);
    if ($real === '#') return '#';
    $b64  = base64_encode($real);
    $b64_url = rawurlencode($b64); // 避免 / + 破坏路径
    return site_url('dl/secretfile/' . $b64_url);
}

/* ---------- 下载计数 ---------- */
function increment_download($config, $file_name) {
    $files = load_files($config);
    $changed = false;
    foreach ($files as &$f) {
        if (($f['name'] ?? '') === $file_name) {
            if (!isset($f['downloads'])) $f['downloads'] = 0;
            $f['downloads'] = (int)$f['downloads'] + 1;
            $f['last_download_at'] = date('Y-m-d H:i:s');
            $changed = true;
            break;
        }
    }
    if ($changed) save_files($config, $files);
}

/* ---------- 小工具 ---------- */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
