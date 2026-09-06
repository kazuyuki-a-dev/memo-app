<?php
session_start();

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';
requireLogin();

$id = $_GET['id'] ?? null;

$id = filter_var($id, FILTER_VALIDATE_INT);
if ($id === false) {
    header('Location: index.php');
    exit;
}
$pdo = getPdo();

$memoModel = new Memo($pdo);
$memo = $memoModel->findById($id, $_SESSION['user_id']);

if (!$memo) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>詳細画面</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1><?php echo htmlspecialchars($memo['title']); ?></h1>

    <p><?php echo nl2br(htmlspecialchars($memo['content'])); ?></p>

    <?php if (!empty($memo['image'])): ?>
        <div class="memo-detail-image">
            <img src="<?php echo htmlspecialchars($memo['image']); ?>" alt="">
        </div>
    <?php endif; ?>

    <?php if (!empty($memo['url'])): ?>
        <p>
            参照URL:
            <a href="<?php echo htmlspecialchars($memo['url']); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo htmlspecialchars($memo['url']); ?>
            </a>
        </p>
    <?php endif; ?>

    <?php if (!empty($memo['tag_names'])): ?>
        <div class="memo-tags">
            <?php foreach (explode(',', $memo['tag_names']) as $tagName): ?>
                <span class="tag-badge"><?php echo htmlspecialchars(trim($tagName)); ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p class="memo-meta">
        作成日時: <?php echo htmlspecialchars($memo['created_at']); ?><br>
        更新日時: <?php echo htmlspecialchars($memo['updated_at']); ?>
    </p>
    <div class="memo-actions">
        <a href="edit.php?id=<?php echo (int) $memo['id']; ?>">編集</a>
        <form action="delete_process.php" method="post" onsubmit="return confirm('このメモを削除しますか？');">
            <input type="hidden" name="id" value="<?php echo (int) $memo['id']; ?>">
            <button type="submit">削除</button>
        </form>
    </div>

    <a href="index.php">一覧に戻る</a>
</body>

</html>