<?php

class Expense
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(array $data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO despesas (viagem_id, categoria, valor, descricao) VALUES (:trip_id, :category, :amount, :description)');
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function getByTrip(int $tripId)
    {
        $stmt = $this->pdo->prepare('SELECT id, viagem_id AS trip_id, categoria AS category, valor AS amount, descricao AS description, created_at FROM despesas WHERE viagem_id = :trip_id ORDER BY id DESC');
        $stmt->execute(['trip_id' => $tripId]);
        return $stmt->fetchAll();
    }

    public function getTotalByTrip(int $tripId)
    {
        $stmt = $this->pdo->prepare('SELECT SUM(valor) AS total FROM despesas WHERE viagem_id = :trip_id');
        $stmt->execute(['trip_id' => $tripId]);
        return $stmt->fetchColumn() ?: 0;
    }

    public function findById(int $id)
    {
        $stmt = $this->pdo->prepare('SELECT id, viagem_id AS trip_id, categoria AS category, valor AS amount, descricao AS description FROM despesas WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function delete(int $id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM despesas WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
