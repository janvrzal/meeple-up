<?php

class User extends Model
{
    public function create(string $userName, string $email, string $password) : int{
        $peppered = hash_hmac('sha256', $password, PEPPER);
        $hash = password_hash($peppered, PASSWORD_DEFAULT);

        $sql = 'INSERT INTO users (username, email, password_hash) 
                VALUES (:username, :email, :hash)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'username' => $userName,
            'email' => $email,
            'hash' => $hash
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
}