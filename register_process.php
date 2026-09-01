<?php
session_start();
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $error = validateName($name);
    if ($error === '') {
        $error = validateEmail($email);
    }
    if ($error === '') {
        $error = validatePassword($password);
    }

    if ($error !== '') {
        $_SESSION['error'] = $error;
        header('Location: register.php');
        exit;
    }

    $pdo = getPdo();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
    ]);

    header('Location: login.php');
    exit;
}

header('Location: register.php');
exit;