<?php
session_start();

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>会員登録</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h1>会員登録</h1>

        <?php if ($error !== ''): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="register_process.php" method="post">
            <label for="name">名前</label>
            <input type="text" name="name" id="name">

            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email">

            <label for="password">パスワード</label>
            <input type="password" name="password" id="password">

            <button type="submit">登録する</button>
        </form>

        <a href="login.php">すでにアカウントをお持ちの方はこちら</a>
    </div>
</body>

</html>