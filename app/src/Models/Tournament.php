<?php

class Tournament extends Model
{
    public function create(int $creatorId, int $gameId, string $name, ?string $description): int
    {
        $sql = 'INSERT INTO tournaments (creator_id, game_id, name, description)
                VALUES (:creator_id, :game_id, :name, :description)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'creator_id'  => $creatorId,
            'game_id'     => $gameId,
            'name'        => $name,
            'description' => $description ?: null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT t.*, g.name AS game_name, u.username AS creator_name,
                       (SELECT COUNT(*) FROM sessions s WHERE s.tournament_id = t.id) AS session_count
                FROM tournaments t
                JOIN games g ON g.id = t.game_id
                JOIN users u ON u.id = t.creator_id
                WHERE t.id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        $sql = 'SELECT t.*, g.name AS game_name, u.username AS creator_name,
                       (SELECT COUNT(*) FROM sessions s WHERE s.tournament_id = t.id) AS session_count
                FROM tournaments t
                JOIN games g ON g.id = t.game_id
                JOIN users u ON u.id = t.creator_id
                ORDER BY t.created_at DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM tournaments WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function forUser(int $userId): array
    {
        $sql = 'SELECT DISTINCT t.*, g.name AS game_name, u.username AS creator_name,
                   (SELECT COUNT(*) FROM sessions s WHERE s.tournament_id = t.id) AS session_count
            FROM tournaments t
            JOIN games g ON g.id = t.game_id
            JOIN users u ON u.id = t.creator_id
            WHERE t.creator_id = :uid1
               OR EXISTS (SELECT 1 FROM sessions s
                          JOIN participations p ON p.session_id = s.id
                          WHERE s.tournament_id = t.id AND p.user_id = :uid2 AND p.status = \'approved\')
                OR EXISTS (SELECT 1 FROM tournament_participations tp
                          WHERE tp.tournament_id = t.id AND tp.user_id = :uid3)
            ORDER BY t.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid1' => $userId, 'uid2' => $userId, 'uid3' => $userId]);
        return $stmt->fetchAll();
    }
}