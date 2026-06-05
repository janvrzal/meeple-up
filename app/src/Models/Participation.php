<?php

class Participation extends Model
{
    public function find(int $userId, int $sessionId): ?array{
        $stmt = $this->db->prepare(
            'SELECT * FROM participations WHERE user_id = :u AND session_id = :s'
        );
        $stmt->execute(['u' => $userId, 's' => $sessionId]);
        return $stmt->fetch() ?: null;
    }

    public function join(int $userId, int $sessionId, string $status = 'approved'): void{
        $stmt = $this->db->prepare(
            'INSERT INTO participations (user_id, session_id, status)
             VALUES (:u, :s, :status)'
        );
        $stmt->execute(['u' => $userId, 's' => $sessionId, 'status' => $status]);
    }

    public function leave(int $userId, int $sessionId): void{
        $stmt = $this->db->prepare(
            'DELETE FROM participations WHERE user_id = :u AND session_id = :s'
        );
        $stmt->execute(['u' => $userId, 's' => $sessionId]);
    }

    public function forSession(int $sessionId): array{
        $stmt = $this->db->prepare(
            'SELECT p.*, u.username
             FROM participations p
             JOIN users u ON u.id = p.user_id
             WHERE p.session_id = :s
             ORDER BY p.created_at ASC'
        );
        $stmt->execute(['s' => $sessionId]);
        return $stmt->fetchAll();
    }

    public function setStatus(int $userId, int $sessionId, string $status): void{
        $stmt = $this->db->prepare(
            'UPDATE participations SET status = :status
             WHERE user_id = :u AND session_id = :s'
        );
        $stmt->execute(['status' => $status, 'u' => $userId, 's' => $sessionId]);
    }

    public function pendingForHost(int $userId): array
    {
        $sql = 'SELECT p.user_id, p.session_id, u.username, s.title
            FROM participations p
            JOIN sessions s ON s.id = p.session_id
            JOIN users u    ON u.id = p.user_id
            WHERE s.creator_id = :uid AND p.status = \'pending\'
            ORDER BY p.created_at ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function countApprovedFor(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM participations WHERE user_id = :uid AND status = \'approved\''
        );
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }
}