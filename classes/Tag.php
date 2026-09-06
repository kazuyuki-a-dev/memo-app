<?php
class Tag
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findOrCreateIds(array $tagNames): array
    {
        $tagIds = [];

        $selectStmt = $this->pdo->prepare('SELECT id FROM tags WHERE name = :name');
        $insertStmt = $this->pdo->prepare('INSERT INTO tags (name) VALUES (:name)');

        foreach ($tagNames as $name) {
            $selectStmt->execute(['name' => $name]);
            $tag = $selectStmt->fetch();

            if ($tag) {
                $tagIds[] = (int) $tag['id'];
            } else {
                $insertStmt->execute(['name' => $name]);
                $tagIds[] = (int) $this->pdo->lastInsertId();
            }
        }

        return $tagIds;
    }

    public static function parseNames(string $tagsInput): array
    {
        $names = explode(',', $tagsInput);
        $names = array_map('trim', $names);
        $names = array_filter($names, fn($name) => $name !== '');
        $names = array_unique($names);

        return array_values($names);
    }
}