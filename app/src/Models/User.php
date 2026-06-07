<?php

class User extends Model
{
    public function create(string $userName, string $email, string $password, ?string $city = null) : int{
        $peppered = hash_hmac('sha256', $password, PEPPER);
        $hash = password_hash($peppered, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO users (username, email, password_hash, city) 
                VALUES (:username, :email, :hash, :city)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'username' => $userName,
            'email' => $email,
            'hash' => $hash,
            'city' => $city ?: null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByEmail(string $email) : ?array{
        $smtm = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $smtm->execute(['email' => $email]);
        $user = $smtm->fetch();

        return $user ?: null;
    }

    public function findByUsername(string $username) : ?array{
        $smtm = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $smtm->execute(['username' => $username]);
        $user = $smtm->fetch();

        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function updateCity(int $id, ?string $city): void
    {
        $stmt = $this->db->prepare('UPDATE users SET city = :city WHERE id = :id');
        $stmt->execute(['city' => $city ?: null, 'id' => $id]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => $hash, 'id' => $id]);
    }
}