<?php

class Session extends Model
{
    private function baseSelect(): string
    {
        return 'SELECT s.*,
                   l.name AS location_name, l.city AS location_city,
                   g.name AS game_name, g.thumbnail_url AS game_thumb,
                   u.username AS creator_name,
                   (SELECT COUNT(*) FROM participations p WHERE p.session_id = s.id) AS player_count
            FROM sessions s
            JOIN locations l ON l.id = s.location_id
            LEFT JOIN games g ON g.id = s.game_id
            JOIN users u ON u.id = s.creator_id';
    }

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
        $stmt = $this->db->prepare($this->baseSelect() . ' WHERE s.id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function gamesInUpcoming(): array
    {
        $sql = 'SELECT DISTINCT g.id, g.name
            FROM sessions s
            JOIN games g ON g.id = s.game_id
            WHERE s.scheduled_at >= NOW() AND s.status = \'open\'
            ORDER BY g.name';
        return $this->db->query($sql)->fetchAll();
    }

    public function filtered(array $f): array
    {
        $sql = $this->baseSelect() . ' WHERE s.scheduled_at >= NOW() AND s.status = \'open\'';

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

        if (!empty($f['game_id'])) {
            $sql .= ' AND s.game_id = :game_id';
            $params['game_id'] = (int) $f['game_id'];
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

    public function forUser(int $userId): array
    {
        $sql = $this->baseSelect()
            . ' WHERE s.scheduled_at >= NOW() AND s.status IN (\'open\', \'cancelled\')
            AND (s.creator_id = :uid1
                 OR EXISTS (SELECT 1 FROM participations p
                            WHERE p.session_id = s.id AND p.user_id = :uid2 AND p.status = \'approved\'))
            ORDER BY s.scheduled_at ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['uid1' => $userId, 'uid2' => $userId]);
        return $stmt->fetchAll();
    }

    public function countHostedBy(int $userId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM sessions WHERE creator_id = :uid');
        $stmt->execute(['uid' => $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['open', 'cancelled', 'finished'], true)) {
            return; // neznámý stav ignoruj
        }
        $stmt = $this->db->prepare('UPDATE sessions SET status = :s WHERE id = :id');
        $stmt->execute(['s' => $status, 'id' => $id]);
    }
}