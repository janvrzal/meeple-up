<?php

class Comment extends Model
{
    public function forSession(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*, u.username
             FROM comments c
             JOIN users u ON u.id = c.user_id
             WHERE c.session_id = :s
             ORDER BY c.created_at ASC'
        );
        $stmt->execute(['s' => $sessionId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM comments WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $sessionId, int $userId, string $body): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (session_id, user_id, body)
             VALUES (:s, :u, :body)'
        );
        $stmt->execute(['s' => $sessionId, 'u' => $userId, 'body' => $body]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}