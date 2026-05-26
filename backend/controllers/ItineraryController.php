<?php

require_once __DIR__ . '/../models/Itinerary.php';
require_once __DIR__ . '/../models/Trip.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class ItineraryController
{
    private $itineraryModel;
    private $tripModel;

    public function __construct(PDO $pdo)
    {
        $this->itineraryModel = new Itinerary($pdo);
        $this->tripModel = new Trip($pdo);
    }

    public function store(array $data, array $user)
    {
        $missing = Validator::validateRequired($data, ['trip_id', 'activity_date', 'activity_time', 'description']);
        if ($missing) {
            Response::error('Campos obrigatórios ausentes: ' . implode(', ', $missing), 422);
        }

        $trip = $this->tripModel->findById((int)$data['trip_id']);
        if (!$trip || ($user['type'] !== 'admin' && $trip['user_id'] !== $user['sub'])) {
            Response::error('Viagem não encontrada ou não autorizada.', 403);
        }

        $id = $this->itineraryModel->create([
            'trip_id' => (int)$data['trip_id'],
            'activity_date' => $data['activity_date'],
            'activity_time' => $data['activity_time'],
            'description' => Validator::sanitizeString($data['description'])
        ]);

        Response::json(['success' => true, 'itinerary_id' => $id], 201);
    }

    public function index(int $tripId, array $user)
    {
        $trip = $this->tripModel->findById($tripId);
        if (!$trip || ($user['type'] !== 'admin' && $trip['user_id'] !== $user['sub'])) {
            Response::error('Viagem não encontrada ou não autorizada.', 403);
        }

        $items = $this->itineraryModel->getByTrip($tripId);
        Response::json(['success' => true, 'itinerary' => $items]);
    }
}
