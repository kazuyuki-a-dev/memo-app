<?php

function getPdo(): PDO
{
    $host = '127.0.0.1';
    $port = '3306';
    $dbname = 'memo_db';
    $username = 'memo_user';
    $password = 'password';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}
