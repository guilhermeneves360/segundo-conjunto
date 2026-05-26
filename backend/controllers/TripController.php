<?php

require_once __DIR__ . '/../models/Trip.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class TripController
{
    private $tripModel;

    public function __construct(PDO $pdo)
    {
        $this->tripModel = new Trip($pdo);
    }

    public function index(array $user)
    {
        if ($user['type'] === 'admin') {
            Response::json(['success' => true, 'trips' => $this->tripModel->getAll()]);
        }

        Response::json(['success' => true, 'trips' => $this->tripModel->getByUser($user['sub'])]);
    }

    public function exportAll()
    {
        return $this->tripModel->getAll();
    }

    public function store(array $data, array $user)
    {
        $missing = Validator::validateRequired($data, ['destination', 'start_date', 'end_date', 'budget']);
        if ($missing) {
            Response::error('Campos obrigatórios ausentes: ' . implode(', ', $missing), 422);
        }

        $id = $this->tripModel->create([
            'user_id' => $user['sub'],
            'destination' => Validator::sanitizeString($data['destination']),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'budget' => Validator::sanitizeFloat($data['budget'])
        ]);

        Response::json(['success' => true, 'trip_id' => $id], 201);
    }

    public function update(int $id, array $data, array $user)
    {
        $trip = $this->tripModel->findById($id);
        if (!$trip) {
            Response::error('Viagem não encontrada.', 404);
        }
        if ($user['type'] !== 'admin' && $trip['user_id'] !== $user['sub']) {
            Response::error('Ação não autorizada.', 403);
        }

        $this->tripModel->update([
            'id' => $id,
            'destination' => Validator::sanitizeString($data['destination']),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'budget' => Validator::sanitizeFloat($data['budget'])
        ]);

        Response::json(['success' => true, 'message' => 'Viagem atualizada.']);
    }

    public function destroy(int $id, array $user)
    {
        $trip = $this->tripModel->findById($id);
        if (!$trip) {
            Response::error('Viagem não encontrada.', 404);
        }
        if ($user['type'] !== 'admin' && $trip['user_id'] !== $user['sub']) {
            Response::error('Ação não autorizada.', 403);
        }

        $this->tripModel->delete($id);
        Response::json(['success' => true, 'message' => 'Viagem removida.']);
    }
}
