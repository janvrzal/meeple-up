<?php

class CatalogSource implements GameSource
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function search(string $query): array
    {
        $stmt = $this->db->prepare(
            'SELECT bgg_id, name, year_published
             FROM bgg_catalog
             WHERE name LIKE :q AND is_expansion = 0
             ORDER BY rank ASC
             LIMIT 20'
        );
        $stmt->execute(['q' => $query . '%']);
        return $stmt->fetchAll();
    }

    public function fetch(int $bggId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT bgg_id, name, year_published FROM bgg_catalog WHERE bgg_id = :id'
        );
        $stmt->execute(['id' => $bggId]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        // Sjednocený tvar dat hry — detaily katalog nemá, takže null.
        return [
            'bgg_id'         => (int) $row['bgg_id'],
            'name'           => $row['name'],
            'year_published' => $row['year_published'] !== null ? (int) $row['year_published'] : null,
            'min_players'    => null,
            'max_players'    => null,
            'playing_time'   => null,
            'thumbnail_url'  => null,
            'description'    => null,
        ];
    }
}