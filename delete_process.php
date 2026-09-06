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

    $memoModel = new Memo($pdo);
    $memoModel->delete($id, $_SESSION['user_id']);

    header('Location: index.php');
    exit;
}

header('Location: index.php');
exit;
