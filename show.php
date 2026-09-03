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

$sql = 'SELECT m.id, m.title, m.content, m.url, m.image, m.created_at, m.updated_at,
               GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ", ") AS tag_names
        FROM memos m
        LEFT JOIN memo_tag mt ON mt.memo_id = m.id
        LEFT JOIN tags t ON t.id = mt.tag_id
        WHERE m.id = :id AND m.user_id = :user_id
        GROUP BY m.id';

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'id' => $id,
    'user_id' => $_SESSION['user_id'],
]);
$memo = $stmt->fetch();

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