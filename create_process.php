<?php
session_start();

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $url = $_POST['url'] ?? '';
    $tagsInput = $_POST['tags'] ?? '';
    $_SESSION['old'] = [
        'title' => $title,
        'content' => $content,
        'url' => $url,
        'tags' => $tagsInput,
    ];

    $error = validateTitle($title);
    if ($error === '') {
        $error = validateContent($content);
    }
    if ($error === '') {
        $error = validateUrl($url);
    }

    if ($error !== '') {
        $_SESSION['error'] = $error;
        header('Location: create.php');
        exit;
    }

    try {
        $imagePath = handleImageUpload($_FILES['image'] ?? []);
    } catch (RuntimeException $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: create.php');
        exit;
    }

    $pdo = getPdo();

    $stmt = $pdo->prepare('INSERT INTO memos (user_id, title, content, url, image) VALUES (:user_id, :title, :content, :url, :image)');
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'title' => $title,
        'content' => $content,
        'url' => $url,
        'image' => $imagePath,
    ]);
    $memoId = (int) $pdo->lastInsertId();

    $tagNames = parseTagNames($tagsInput);
    if (!empty($tagNames)) {
        $tagIds = findOrCreateTagIds($pdo, $tagNames);

        $linkStmt = $pdo->prepare('INSERT INTO memo_tag (memo_id, tag_id) VALUES (:memo_id, :tag_id)');
        foreach ($tagIds as $tagId) {
            $linkStmt->execute([
                'memo_id' => $memoId,
                'tag_id' => $tagId,
            ]);
        }
    }
    unset($_SESSION['old']);
    header('Location: index.php');
    exit;
}
