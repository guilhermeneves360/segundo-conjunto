<?php

require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Trip.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class ReservationController
{
    private $reservationModel;
    private $tripModel;

    public function __construct(PDO $pdo)
    {
        $this->reservationModel = new Reservation($pdo);
        $this->tripModel = new Trip($pdo);
    }

    public function store(array $data, array $user)
    {
        $missing = Validator::validateRequired($data, ['trip_id', 'type', 'details']);
        if ($missing) {
            Response::error('Campos obrigatórios ausentes: ' . implode(', ', $missing), 422);
        }

        $trip = $this->tripModel->findById((int) $data['trip_id']);
        if (!$trip || ($user['type'] !== 'admin' && $trip['user_id'] !== $user['sub'])) {
            Response::error('Viagem não encontrada ou não autorizada.', 403);
        }

        $id = $this->reservationModel->create([
            'trip_id' => (int) $data['trip_id'],
            'type' => Validator::sanitizeString($data['type']),
            'details' => Validator::sanitizeString($data['details'])
        ]);

        Response::json(['success' => true, 'reservation_id' => $id], 201);
    }

    public function index(int $tripId, array $user)
    {
        $trip = $this->tripModel->findById($tripId);
        if (!$trip || ($user['type'] !== 'admin' && $trip['user_id'] !== $user['sub'])) {
            Response::error('Viagem não encontrada ou não autorizada.', 403);
        }

        $items = $this->reservationModel->getByTrip($tripId);
        Response::json(['success' => true, 'reservations' => $items]);
    }
}
