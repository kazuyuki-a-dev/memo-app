<?php
require_once __DIR__ . '/db.php';

try {
    $pdo = getPdo();
    echo "DB接続成功";
} catch (PDOException $e) {
    echo "DB接続失敗: " . $e->getMessage();
}
