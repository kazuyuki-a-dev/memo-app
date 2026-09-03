<?php

// 名前のバリデーション
function validateName(string $name): string
{
    if (trim($name) === '') {
        return '名前を入力してください';
    }

    if (mb_strlen($name) > 50) {
        return '名前は50文字以内で入力してください';
    }

    return '';
}

// メールアドレスのバリデーション
function validateEmail(string $email): string
{
    if (trim($email) === '') {
        return 'メールアドレスを入力してください';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return '正しいメールアドレスの形式で入力してください';
    }

    return '';
}

// パスワードのバリデーション
function validatePassword(string $password): string
{
    if (trim($password) === '') {
        return 'パスワードを入力してください';
    }

    if (mb_strlen($password) < 8) {
        return 'パスワードは8文字以上で入力してください';
    }

    return '';
}

function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function validateTitle(string $title): string
{
    if (trim($title) === '') {
        return 'タイトルを入力してください';
    }

    if (mb_strlen($title) > 100) {
        return 'タイトルは100文字以内で入力してください';
    }

    return '';
}

function validateContent(string $content): string
{
    if (trim($content) === '') {
        return '本文を入力してください';
    }

    return '';
}

function validateUrl(string $url): string
{
    if (trim($url) === '') {
        return '';
    }

    if (mb_strlen($url) > 255) {
        return 'URLは255文字以内で入力してください';
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return '正しいURLの形式で入力してください';
    }

    return '';
}

function handleImageUpload(array $file): string
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('画像のアップロードに失敗しました');
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];

    $mimeType = mime_content_type($file['tmp_name']);
    if (!isset($allowedTypes[$mimeType])) {
        throw new RuntimeException('画像はjpg・png・gif形式のみアップロードできます');
    }

    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file['size'] > $maxSize) {
        throw new RuntimeException('画像サイズは2MB以内にしてください');
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = uniqid('memo_', true) . '.' . $allowedTypes[$mimeType];
    $destination = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('画像の保存に失敗しました');
    }

    return 'uploads/' . $fileName;
}

function parseTagNames(string $tagsInput): array
{
    $names = explode(',', $tagsInput);
    $names = array_map('trim', $names);
    $names = array_filter($names, fn($name) => $name !== '');
    $names = array_unique($names);

    return array_values($names);
}

function findOrCreateTagIds(PDO $pdo, array $tagNames): array
{
    $tagIds = [];


    $selectStmt = $pdo->prepare('SELECT id FROM tags WHERE name = :name');
    $insertStmt = $pdo->prepare('INSERT INTO tags (name) VALUES (:name)');


    foreach ($tagNames as $name) {
        $selectStmt->execute(['name' => $name]);
        $tag = $selectStmt->fetch();

        if ($tag) {
            $tagIds[] = (int) $tag['id'];
        } else {
            $insertStmt->execute(['name' => $name]);
            $tagIds[] = (int) $pdo->lastInsertId();
        }
    }

    return $tagIds;
}