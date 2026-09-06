<?php
class Memo
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllByUser(int $userId, string $keyword, int $perPage, int $offset): array
    {
        $countSql = 'SELECT COUNT(DISTINCT m.id) AS total FROM memos m WHERE m.user_id = :user_id';

        $sql = 'SELECT m.id, m.title, m.content, m.url, m.image, m.created_at, m.updated_at,
                GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ", ") AS tag_names
            FROM memos m
            LEFT JOIN memo_tag mt ON mt.memo_id = m.id
            LEFT JOIN tags t ON t.id = mt.tag_id
            WHERE m.user_id = :user_id';

        $params = ['user_id' => $userId];

        if ($keyword !== '') {
            $condition = ' AND (m.title LIKE :keyword OR m.content LIKE :keyword)';
            $countSql .= $condition;
            $sql .= $condition;
            $params['keyword'] = '%' . $keyword . '%';
        }

        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalCount = (int) $countStmt->fetch()['total'];

        $sql .= " GROUP BY m.id ORDER BY m.updated_at DESC LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $memos = $stmt->fetchAll();

        return [
            'memos' => $memos,
            'totalCount' => $totalCount,
        ];
    }

    public function findById(int $id, int $userId): array|false
    {
        $sql = 'SELECT m.id, m.title, m.content, m.url, m.image, m.created_at, m.updated_at,
                GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ", ") AS tag_names
            FROM memos m
            LEFT JOIN memo_tag mt ON mt.memo_id = m.id
            LEFT JOIN tags t ON t.id = mt.tag_id
            WHERE m.id = :id AND m.user_id = :user_id
            GROUP BY m.id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        return $stmt->fetch();
    }

    public function create(int $userId, string $title, string $content, ?string $url, ?string $image): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO memos (user_id, title, content, url, image) VALUES (:user_id, :title, :content, :url, :image)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'content' => $content,
            'url' => $url,
            'image' => $image,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $userId, string $title, string $content, ?string $url, ?string $image): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE memos SET title = :title, content = :content, url = :url, image = :image WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'title' => $title,
            'content' => $content,
            'url' => $url,
            'image' => $image,
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM memos WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function syncTags(int $memoId, array $tagIds): void
    {
        // 一旦すべての紐付けを削除してから、新しい内容で作り直す
        $deleteStmt = $this->pdo->prepare('DELETE FROM memo_tag WHERE memo_id = :memo_id');
        $deleteStmt->execute(['memo_id' => $memoId]);

        if (empty($tagIds)) {
            return;
        }

        $linkStmt = $this->pdo->prepare('INSERT INTO memo_tag (memo_id, tag_id) VALUES (:memo_id, :tag_id)');
        foreach ($tagIds as $tagId) {
            $linkStmt->execute([
                'memo_id' => $memoId,
                'tag_id' => $tagId,
            ]);
        }
    }
    public function findAllByUserForExport(int $userId, string $keyword): array
    {
        $sql = 'SELECT m.title, m.content, m.url, m.created_at, m.updated_at,
                GROUP_CONCAT(t.name ORDER BY t.name SEPARATOR ", ") AS tag_names
            FROM memos m
            LEFT JOIN memo_tag mt ON mt.memo_id = m.id
            LEFT JOIN tags t ON t.id = mt.tag_id
            WHERE m.user_id = :user_id';

        $params = ['user_id' => $userId];

        if ($keyword !== '') {
            $sql .= ' AND (m.title LIKE :keyword OR m.content LIKE :keyword)';
            $params['keyword'] = '%' . $keyword . '%';
        }

        $sql .= ' GROUP BY m.id ORDER BY m.updated_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
