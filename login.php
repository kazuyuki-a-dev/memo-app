<?php
session_start();

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">

        <h1>ログイン</h1>

        <?php if ($error !== ''): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="login_process.php" method="post">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email">

            <label for="password">パスワード</label>
            <input type="password" name="password" id="password">
            <button type="submit">ログイン</button>
        </form>

        <a href="register.php">アカウントをお持ちでない方はこちら</a>
    </div>
</body>

</html>