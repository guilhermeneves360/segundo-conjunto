<?php

class Itinerary
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(array $data)
    {
        $stmt = $this->pdo->prepare('INSERT INTO itinerarios (viagem_id, data_atividade, hora, descricao) VALUES (:trip_id, :activity_date, :activity_time, :description)');
        $stmt->execute($data);
        return $this->pdo->lastInsertId();
    }

    public function getByTrip(int $tripId)
    {
        $stmt = $this->pdo->prepare('SELECT id, viagem_id AS trip_id, data_atividade AS activity_date, hora AS activity_time, descricao AS description, created_at FROM itinerarios WHERE viagem_id = :trip_id ORDER BY data_atividade, hora');
        $stmt->execute(['trip_id' => $tripId]);
        return $stmt->fetchAll();
    }

    public function delete(int $id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM itinerarios WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
