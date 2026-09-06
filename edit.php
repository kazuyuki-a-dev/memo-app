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
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メモ編集</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <h1>メモ編集</h1>

    <?php if ($error !== ''): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form action="edit_process.php" method="post" enctype="multipart/form-data">
        <label for="title">タイトル</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($memo['title'] ?? ''); ?>">
        <label for="content">本文</label>
        <textarea name="content" id="content" rows="8"><?php echo htmlspecialchars($memo['content'] ?? ''); ?></textarea>
        <label for="url">参照URL(任意)</label>
        <input type="text" name="url" id="url" value="<?php echo htmlspecialchars($memo['url'] ?? ''); ?>">
        <label for="image">画像(任意・jpg/png/gif、2MBまで)</label>

        <div class="memo-current-image">
            <p id="image-preview-label"><?php echo !empty($memo['image']) ? '現在の画像:' : ''; ?></p>
            <img id="image-preview"
                src="<?php echo !empty($memo['image']) ? htmlspecialchars($memo['image']) : ''; ?>"
                alt=""
                style="<?php echo empty($memo['image']) ? 'display:none;' : ''; ?>">
        </div>

        <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif" onchange="previewImage(this)">
        <label for="tags">タグ(任意・カンマ区切りで複数指定可)</label>
        <input type="text" name="tags" id="tags" placeholder="例: 仕事, アイデア" value="<?php echo htmlspecialchars($memo['tag_names'] ?? ''); ?>">
        <input type="hidden" name="id" value="<?php echo (int) $memo['id']; ?>">
        <button type="submit">更新する</button>
    </form>
    <a href="show.php?id=<?php echo (int) $memo['id']; ?>">詳細に戻る</a>
    <a href="index.php">一覧に戻る</a>
    <script>
        function previewImage(input) {
            const preview = document.getElementById('image-preview');
            const label = document.getElementById('image-preview-label');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    label.textContent = '選択中の画像(プレビュー):';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>