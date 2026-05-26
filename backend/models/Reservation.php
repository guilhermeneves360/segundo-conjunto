<?php

class Reservation
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(array $data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO reservas (viagem_id, tipo, detalhes) VALUES (:trip_id, :type, :details)');
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function getByTrip(int $tripId)
    {
        $stmt = $this->pdo->prepare('SELECT id, viagem_id AS trip_id, tipo AS type, detalhes AS details, created_at FROM reservas WHERE viagem_id = :trip_id ORDER BY id DESC');
        $stmt->execute(['trip_id' => $tripId]);
        return $stmt->fetchAll();
    }

    public function delete(int $id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM reservas WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
