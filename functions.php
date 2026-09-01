<?php

// 名前のバリデーション
function validateName(string $name): string
{
    if (trim($name) === '') {
        return '名前を入力してください';
    }

    if (mb_strlen($name) > 50) {
        return '名前は50文字以内で入力してください';
    }

    return '';
}

// メールアドレスのバリデーション
function validateEmail(string $email): string
{
    if (trim($email) === '') {
        return 'メールアドレスを入力してください';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return '正しいメールアドレスの形式で入力してください';
    }

    return '';
}

// パスワードのバリデーション
function validatePassword(string $password): string
{
    if (trim($password) === '') {
        return 'パスワードを入力してください';
    }

    if (mb_strlen($password) < 8) {
        return 'パスワードは8文字以上で入力してください';
    }

    return '';
}