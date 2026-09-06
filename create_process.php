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

    $memoModel = new Memo($pdo);
    $imageForDb = $imagePath !== '' ? $imagePath : null;
    $urlForDb = $url !== '' ? $url : null;

    $memoId = $memoModel->create($_SESSION['user_id'], $title, $content, $urlForDb, $imageForDb);

    $tagNames = Tag::parseNames($tagsInput);
    if (!empty($tagNames)) {
        $tag = new Tag($pdo);
        $tagIds = $tag->findOrCreateIds($tagNames);
        $memoModel->syncTags($memoId, $tagIds);
    }

    unset($_SESSION['old']);
    header('Location: index.php');
    exit;
}
