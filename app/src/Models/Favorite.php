<?php

class Favorite extends Model
{
    public function add(int $userId, int $gameId): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO user_favorite_games (user_id, game_id) VALUES (:u, :g)'
        );
        $stmt->execute(['u' => $userId, 'g' => $gameId]);
    }

    public function remove(int $userId, int $gameId): void
    {
        $stmt = $this->db->prepare(
            'DELETE FROM user_favorite_games WHERE user_id = :u AND game_id = :g'
        );
        $stmt->execute(['u' => $userId, 'g' => $gameId]);
    }

    public function isFavorite(int $userId, int $gameId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM user_favorite_games WHERE user_id = :u AND game_id = :g'
        );
        $stmt->execute(['u' => $userId, 'g' => $gameId]);
        return $stmt->fetchColumn() !== false;
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT g.*
             FROM user_favorite_games f
             JOIN games g ON g.id = f.game_id
             WHERE f.user_id = :u
             ORDER BY g.name'
        );
        $stmt->execute(['u' => $userId]);
        return $stmt->fetchAll();
    }
}