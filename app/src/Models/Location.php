<?php

class Location extends Model
{
    public function all(): array{
        $stmt = $this->db->query('SELECT * FROM locations ORDER BY city, name');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array{
        $stmt = $this->db->prepare('SELECT * FROM locations WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $city, ?string $address = null): int{
        $sql = 'INSERT INTO locations (name, address, city) VALUES (:name, :address, :city)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'address' => $address ?: null,
            'city' => $city,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function cities(): array
    {
        $rows = $this->db->query(
            'SELECT DISTINCT city FROM locations WHERE city != \'\' ORDER BY city'
        )->fetchAll();
        return array_column($rows, 'city');
    }
}