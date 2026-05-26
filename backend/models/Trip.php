<?php

class Trip
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(array $data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO viagens (usuario_id, destino, data_ida, data_volta, orcamento, created_at) VALUES (:user_id, :destination, :start_date, :end_date, :budget, NOW())');
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function getByUser(int $userId)
    {
        $stmt = $this->pdo->prepare('SELECT id, usuario_id AS user_id, destino AS destination, data_ida AS start_date, data_volta AS end_date, orcamento AS budget, created_at FROM viagens WHERE usuario_id = :user_id ORDER BY data_ida DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getAll()
    {
        $stmt = $this->pdo->query('SELECT viagens.id, viagens.usuario_id AS user_id, viagens.destino AS destination, viagens.data_ida AS start_date, viagens.data_volta AS end_date, viagens.orcamento AS budget, viagens.created_at, utilizadores.nome AS owner_name FROM viagens JOIN utilizadores ON viagens.usuario_id = utilizadores.id ORDER BY viagens.data_ida DESC');
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT id, usuario_id AS user_id, destino AS destination, data_ida AS start_date, data_volta AS end_date, orcamento AS budget, created_at FROM viagens WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update(array $data)
    {
        $stmt = $this->pdo->prepare('UPDATE viagens SET destino = :destination, data_ida = :start_date, data_volta = :end_date, orcamento = :budget WHERE id = :id');
        return $stmt->execute($data);
    }

    public function delete(int $id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM viagens WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function countByUser(?int $userId = null)
    {
        if ($userId) {
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM viagens WHERE usuario_id = :user_id');
            $stmt->execute(['user_id' => $userId]);
            return (int)$stmt->fetchColumn();
        }
        return (int)$this->pdo->query('SELECT COUNT(*) FROM viagens')->fetchColumn();
    }
}
