<?php

class Game extends Model
{
    public function findByBggId(int $bggId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM games WHERE bgg_id = :bgg');
        $stmt->execute(['bgg' => $bggId]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM games WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $d): int
    {
        $sql = 'INSERT INTO games
                (bgg_id, name, year_published, min_players, max_players, playing_time, thumbnail_url, description)
                VALUES
                (:bgg_id, :name, :year_published, :min_players, :max_players, :playing_time, :thumbnail_url, :description)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'bgg_id'         => $d['bgg_id'],
            'name'           => $d['name'],
            'year_published' => $d['year_published'],
            'min_players'    => $d['min_players'],
            'max_players'    => $d['max_players'],
            'playing_time'   => $d['playing_time'],
            'thumbnail_url'  => $d['thumbnail_url'],
            'description'    => $d['description'],
        ]);
        return (int) $this->db->lastInsertId();
    }
}