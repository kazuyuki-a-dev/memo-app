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

    // まず「自分のメモかどうか」を確認する(他人のメモを書き換えられないようにする安全装置)
    $checkStmt = $pdo->prepare('SELECT image FROM memos WHERE id = :id AND user_id = :user_id');
    $checkStmt->execute([
        'id' => $id,
        'user_id' => $_SESSION['user_id'],
    ]);
    $currentMemo = $checkStmt->fetch();

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

    $stmt = $pdo->prepare(
        'UPDATE memos SET title = :title, content = :content, url = :url, image = :image WHERE id = :id AND user_id = :user_id'
    );
    $stmt->execute([
        'title' => $title,
        'content' => $content,
        'url' => $url !== '' ? $url : null,
        'image' => $imagePath !== '' ? $imagePath : null,
        'id' => $id,
        'user_id' => $_SESSION['user_id'],
    ]);

    // タグは一旦すべて削除してから、新しい内容で作り直す
    $deleteTagsStmt = $pdo->prepare('DELETE FROM memo_tag WHERE memo_id = :memo_id');
    $deleteTagsStmt->execute(['memo_id' => $id]);

    $tagNames = parseTagNames($tagsInput);
    if (!empty($tagNames)) {
        $tagIds = findOrCreateTagIds($pdo, $tagNames);

        $linkStmt = $pdo->prepare('INSERT INTO memo_tag (memo_id, tag_id) VALUES (:memo_id, :tag_id)');
        foreach ($tagIds as $tagId) {
            $linkStmt->execute([
                'memo_id' => $id,
                'tag_id' => $tagId,
            ]);
        }
    }

    header('Location: show.php?id=' . $id);
    exit;
}

header('Location: index.php');
exit;
