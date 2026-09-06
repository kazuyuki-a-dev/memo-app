<?php
session_start();

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false) {
        header('Location: index.php');
        exit;
    }

    $pdo = getPdo();

    // idとuser_idの両方を条件にすることで、他人のメモを削除できないようにする
    $stmt = $pdo->prepare('DELETE FROM memos WHERE id = :id AND user_id = :user_id');
    $stmt->execute([
        'id' => $id,
        'user_id' => $_SESSION['user_id'],
    ]);

    header('Location: index.php');
    exit;
}

header('Location: index.php');
exit;
