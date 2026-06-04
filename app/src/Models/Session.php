<?php

class Session extends Model
{
    public function create(array $data): int {
        $sql = 'INSERT INTO sessions
                (creator_id, location_id, game_id, title, scheduled_at, max_players, is_private, description)
                VALUES
                (:creator_id, :location_id, :game_id, :title, :scheduled_at, :max_players, :is_private, :description)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'creator_id' => $data['creator_id'],
            'location_id' => $data['location_id'],
            'game_id'      => $data['game_id'] ?: null,
            'title' => $data['title'],
            'scheduled_at' => $data['scheduled_at'],
            'max_players' => $data['max_players'],
            'is_private'   => $data['is_private'] ?? 0,
            'description'  => $data['description'] ?: null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT s.*,
                       l.name AS location_name, l.city AS location_city,
                       g.name AS game_name, g.thumbnail_url AS game_thumb,
                       u.username AS creator_name,
                       (SELECT COUNT(*) FROM participations p WHERE p.session_id = s.id) AS player_count
                FROM sessions s
                JOIN locations l ON l.id = s.location_id
                LEFT JOIN games g ON g.id = s.game_id
                JOIN users u ON u.id = s.creator_id
                WHERE s.id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function upcoming(): array
    {
        $sql = 'SELECT s.*,
                       l.name AS location_name, l.city AS location_city,
                       g.name AS game_name, g.thumbnail_url AS game_thumb,
                       u.username AS creator_name,
                       (SELECT COUNT(*) FROM participations p WHERE p.session_id = s.id) AS player_count
                FROM sessions s
                JOIN locations l ON l.id = s.location_id
                LEFT JOIN games g ON g.id = s.game_id
                JOIN users u ON u.id = s.creator_id
                WHERE s.scheduled_at >= NOW() AND s.status = \'open\'
                ORDER BY s.scheduled_at ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function filtered(array $f): array
    {
        $sql = 'SELECT s.*,
                   l.name AS location_name, l.city AS location_city,
                   g.name AS game_name, g.thumbnail_url AS game_thumb,
                   u.username AS creator_name,
                   (SELECT COUNT(*) FROM participations p WHERE p.session_id = s.id) AS player_count
            FROM sessions s
            JOIN locations l ON l.id = s.location_id
            LEFT JOIN games g ON g.id = s.game_id
            JOIN users u ON u.id = s.creator_id
            WHERE s.scheduled_at >= NOW() AND s.status = \'open\'';

        $params = [];

        // filtr: lokace
        if (!empty($f['location_id'])) {
            $sql .= ' AND s.location_id = :location_id';
            $params['location_id'] = (int) $f['location_id'];
        }

        // filtr: město
        if (!empty($f['city'])) {
            $sql .= ' AND l.city = :city';
            $params['city'] = $f['city'];
        }

        // filtr: jen s volnými místy (HAVING – pracuje s aliasem player_count)
        if (!empty($f['free_only'])) {
            $sql .= ' HAVING (s.max_players IS NULL OR player_count < s.max_players)';
        }

        $sql .= ' ORDER BY s.scheduled_at ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE sessions SET
                    location_id = :location_id, game_id = :game_id, title = :title,
                    scheduled_at = :scheduled_at, max_players = :max_players,
                    is_private = :is_private, description = :description
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'           => $id,
            'location_id'  => $data['location_id'],
            'game_id'      => $data['game_id'] ?: null,
            'title'        => $data['title'],
            'scheduled_at' => $data['scheduled_at'],
            'max_players'  => $data['max_players'],
            'is_private'   => $data['is_private'] ?? 0,
            'description'  => $data['description'] ?: null,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM sessions WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}