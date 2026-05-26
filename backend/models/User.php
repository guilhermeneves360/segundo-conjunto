<?php

class User
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email)
    {
        $stmt = $this->pdo->prepare('SELECT id, nome AS name, email, senha AS password, tipo AS type, reset_token AS password_reset_token, reset_expira_em AS password_reset_expires, created_at FROM utilizadores WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT id, nome AS name, email, tipo AS type, created_at FROM utilizadores WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO utilizadores (nome, email, senha, tipo, reset_token, reset_expira_em, created_at) VALUES (:name, :email, :password, :type, :token, :expires, NOW())');
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'type' => $data['type'],
            'token' => null,
            'expires' => null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateResetToken(int $id, ?string $token, ?string $expires)
    {
        $stmt = $this->pdo->prepare('UPDATE utilizadores SET reset_token = :token, reset_expira_em = :expires WHERE id = :id');
        $stmt->execute(['token' => $token, 'expires' => $expires, 'id' => $id]);
    }

    public function findByResetToken(string $token)
    {
        $stmt = $this->pdo->prepare('SELECT id, nome AS name, email, senha AS password, tipo AS type, reset_token AS password_reset_token, reset_expira_em AS password_reset_expires FROM utilizadores WHERE reset_token = :token LIMIT 1');
        $stmt->execute(['token' => $token]);
        return $stmt->fetch();
    }

    public function updatePassword(int $id, string $password)
    {
        $stmt = $this->pdo->prepare('UPDATE utilizadores SET senha = :password, reset_token = NULL, reset_expira_em = NULL WHERE id = :id');
        $stmt->execute(['password' => $password, 'id' => $id]);
    }

    public function listAll()
    {
        $stmt = $this->pdo->query('SELECT id, nome AS name, email, tipo AS type, created_at FROM utilizadores ORDER BY created_at DESC');
        return $stmt->fetchAll();
    }

    public function updateType(int $id, string $type)
    {
        $stmt = $this->pdo->prepare('UPDATE utilizadores SET tipo = :type WHERE id = :id');
        return $stmt->execute(['type' => $type, 'id' => $id]);
    }

    public function delete(int $id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM utilizadores WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
