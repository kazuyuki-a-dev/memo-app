<?php
session_start();

require_once __DIR__ . '/functions.php';

requireLogin();

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規メモ作成</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>新規メモ作成</h1>

    <?php if ($error !== ''): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form action="create_process.php" method="post" enctype="multipart/form-data">
        <label for="title">タイトル</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($old['title'] ?? ''); ?>">
        <label for="content">本文</label>
        <textarea name="content" id="content" rows="8"><?php echo htmlspecialchars($old['content'] ?? ''); ?></textarea>
        <label for="url">参照URL(任意)</label>
        <input type="text" name="url" id="url" value="<?php echo htmlspecialchars($old['url'] ?? ''); ?>">
        <label for="image">画像(任意・jpg/png/gif,2MBまで)</label>
        <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif">
        <label for="tags">タグ(任意・カンマ区切りで複数指定可)</label>
        <input type="text" name="tags" id="tags" placeholder="例: 仕事, アイディア" value="<?php echo htmlspecialchars($old['tags'] ?? ''); ?>">
        <button type="submit">作成する</button>
    </form>
    <a href="index.php">一覧に戻る</a>
</body>

</html>