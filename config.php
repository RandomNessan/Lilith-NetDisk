<?php
return [
    // 设为 null：自动从请求中识别协议与域名（支持反代/Cloudflare）
    'base_url'    => null,

    // 首次运行会从这里迁移到 data/auth.json（随后仅用 auth.json）
    'admin_user'  => 'admin',
    'admin_pass'  => 'admin123',

    // 时区
    'timezone'    => 'Asia/Seoul',

    // 存储路径
    'storage' => [
        'files_json' => __DIR__ . '/data/files.json',
        'dl_dir'     => __DIR__ . '/dl',   // 实际文件存放目录
    ],
];
