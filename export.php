<?php
session_start();

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

requireLogin();

$keyword = trim($_GET['keyword'] ?? '');

$pdo = getPdo();
$memoModel = new Memo($pdo);
$memos = $memoModel->findAllByUserForExport($_SESSION['user_id'], $keyword);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="memos.csv"');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, ['タイトル', '本文', 'URL', 'タグ', '作成日時', '更新日時'], ',', '"', '');

foreach ($memos as $memo) {
    fputcsv($output, [
        $memo['title'],
        $memo['content'],
        $memo['url'],
        $memo['tag_names'],
        $memo['created_at'],
        $memo['updated_at'],
    ], ',', '"', '');
}

fclose($output);
exit;
