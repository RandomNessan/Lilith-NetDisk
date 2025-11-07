<?php
require __DIR__ . '/../lib/common.php';
session_destroy();
header('Location: /admin/login.php');
