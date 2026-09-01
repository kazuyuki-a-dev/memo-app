<?php
session_start();

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $pdo = getPdo();

    $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = :email');
    $stmt->execute([
        'email' => $email,
    ]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['error'] = 'メールアドレスまたはパスワードが正しくありません';
        header('Location: login.php');
        exit;
    }
}
header('Location: login.php');
exit;
