<?php
session_start();

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

// 未ログインならログイン画面へ
requireLogin();

$pdo = getPdo();

$sql = 'SELECT m.id, m.title, m.content, m.url, m.image, m.created_at, m.updated_at,
            GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ", ") AS tag_names
        FROM memos m
        LEFT JOIN memo_tag mt ON mt.memo_id = m.id
        LEFT JOIN tags t ON t.id = mt.tag_id
        WHERE m.user_id = :user_id
        GROUP BY m.id
        ORDER BY m.updated_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute([
    'user_id' => $_SESSION['user_id'],
]);
$memos = $stmt->fetchAll();

// 本文プレビュー用に短く切り詰める
function previewContent(string $content, int $length = 60): string
{
    if (mb_strlen($content) <= $length) {
        return $content;
    }
    return mb_substr($content, 0, $length) . '…';
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メモ一覧</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">

        <div class="header">
            <h1>メモ一覧</h1>
            <div class="header-actions">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?>さん</span>
                <a href="logout.php">ログアウト</a>
            </div>
        </div>

        <a href="create.php" class="btn-new">＋ 新規メモ作成</a>

        <?php if (empty($memos)): ?>
            <p class="empty-message">まだメモがありません。</p>
        <?php else: ?>
            <ul class="memo-list">
                <?php foreach ($memos as $memo): ?>
                    <li class="memo-item">
                        <?php if (!empty($memo['image'])): ?>
                            <div class="memo-thumb">
                                <img src="<?php echo htmlspecialchars($memo['image']); ?>" alt="">
                            </div>
                        <?php endif; ?>

                        <div class="memo-title">
                            <a href="show.php?id=<?php echo (int) $memo['id']; ?>">
                                <?php echo htmlspecialchars($memo['title']); ?>
                            </a>
                        </div>

                        <div class="memo-preview">
                            <?php echo htmlspecialchars(previewContent($memo['content'])); ?>
                        </div>

                        <?php if (!empty($memo['tag_names'])): ?>
                            <div class="memo-tags">
                                <?php foreach (explode(',', $memo['tag_names']) as $tagName): ?>
                                    <span class="tag-badge"><?php echo htmlspecialchars(trim($tagName)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($memo['url'])): ?>
                            <div class="memo-url">
                                <a href="<?php echo htmlspecialchars($memo['url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo htmlspecialchars($memo['url']); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="memo-meta">
                            更新日時: <?php echo htmlspecialchars($memo['updated_at']); ?>
                        </div>
                        <div class="memo-actions">
                            <a href="edit.php?id=<?php echo (int) $memo['id']; ?>">編集</a>
                            <form action="delete_process.php" method="post" onsubmit="return confirm('このメモを削除しますか？');">
                                <input type="hidden" name="id" value="<?php echo (int) $memo['id']; ?>">
                                <button type="submit">削除</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

    </div>
</body>

</html>