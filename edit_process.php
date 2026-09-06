<?php
session_start();

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // hiddenフィールドで送られてきたidを受け取る(POSTなので$_POSTから)
    $id = $_POST['id'] ?? null;
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false) {
        header('Location: index.php');
        exit;
    }

    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $url = $_POST['url'] ?? '';
    $tagsInput = $_POST['tags'] ?? '';

    $error = validateTitle($title);
    if ($error === '') {
        $error = validateContent($content);
    }
    if ($error === '') {
        $error = validateUrl($url);
    }

    if ($error !== '') {
        $_SESSION['error'] = $error;
        header('Location: edit.php?id=' . $id);
        exit;
    }

    $pdo = getPdo();
    $memoModel = new Memo($pdo);

    // 自分のメモかどうかを確認しながら、現在の画像パスを取得する
    $currentMemo = $memoModel->findById($id, $_SESSION['user_id']);
    if (!$currentMemo) {
        header('Location: index.php');
        exit;
    }

    // 画像: 新しいファイルが選ばれていれば差し替え、選ばれていなければ今の画像パスを維持する
    $imagePath = $currentMemo['image'];
    try {
        $newImagePath = handleImageUpload($_FILES['image'] ?? []);
        if ($newImagePath !== '') {
            $imagePath = $newImagePath;
        }
    } catch (RuntimeException $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: edit.php?id=' . $id);
        exit;
    }

    $urlForDb = $url !== '' ? $url : null;
    $memoModel->update($id, $_SESSION['user_id'], $title, $content, $urlForDb, $imagePath);

    $tagNames = Tag::parseNames($tagsInput);
    $tagIds = [];
    if (!empty($tagNames)) {
        $tag = new Tag($pdo);
        $tagIds = $tag->findOrCreateIds($tagNames);
    }
    $memoModel->syncTags($id, $tagIds);

    header('Location: show.php?id=' . $id);
    exit;
}
header('Location: index.php');
exit;
