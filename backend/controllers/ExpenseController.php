<?php

require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../models/Trip.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class ExpenseController
{
    private $expenseModel;
    private $tripModel;

    public function __construct(PDO $pdo)
    {
        $this->expenseModel = new Expense($pdo);
        $this->tripModel = new Trip($pdo);
    }

    public function store(array $data, array $user)
    {
        $missing = Validator::validateRequired($data, ['trip_id', 'category', 'amount', 'description']);
        if ($missing) {
            Response::error('Campos obrigatórios ausentes: ' . implode(', ', $missing), 422);
        }

        $trip = $this->tripModel->findById((int)$data['trip_id']);
        if (!$trip || ($user['type'] !== 'admin' && $trip['user_id'] !== $user['sub'])) {
            Response::error('Viagem não encontrada ou não autorizada.', 403);
        }

        $id = $this->expenseModel->create([
            'trip_id' => (int)$data['trip_id'],
            'category' => Validator::sanitizeString($data['category']),
            'amount' => Validator::sanitizeFloat($data['amount']),
            'description' => Validator::sanitizeString($data['description'])
        ]);

        Response::json(['success' => true, 'expense_id' => $id], 201);
    }

    public function index(int $tripId, array $user)
    {
        $trip = $this->tripModel->findById($tripId);
        if (!$trip || ($user['type'] !== 'admin' && $trip['user_id'] !== $user['sub'])) {
            Response::error('Viagem não encontrada ou não autorizada.', 403);
        }

        $expenses = $this->expenseModel->getByTrip($tripId);
        $total = $this->expenseModel->getTotalByTrip($tripId);
        Response::json(['success' => true, 'expenses' => $expenses, 'total' => $total]);
    }

    public function exportCsv(int $tripId, array $user)
    {
        $trip = $this->tripModel->findById($tripId);
        if (!$trip || ($user['type'] !== 'admin' && $trip['user_id'] !== $user['sub'])) {
            Response::error('Viagem não encontrada ou não autorizada.', 403);
        }

        $expenses = $this->expenseModel->getByTrip($tripId);
        $rows = [];
        foreach ($expenses as $expense) {
            $rows[] = [
                $expense['id'],
                $expense['category'],
                $expense['amount'],
                $expense['description']
            ];
        }
        ExportService::csv($rows, ['ID', 'Categoria', 'Valor', 'Descrição'], 'despesas_viagem_' . $tripId . '.csv');
    }

    public function destroy(int $id, array $user)
    {
        $expense = $this->expenseModel->findById($id);
        if (!$expense) {
            Response::error('Despesa não encontrada.', 404);
        }

        $trip = $this->tripModel->findById((int)$expense['trip_id']);
        if (!$trip || ($user['type'] !== 'admin' && $trip['user_id'] !== $user['sub'])) {
            Response::error('Ação não autorizada.', 403);
        }

        $this->expenseModel->delete($id);
        Response::json(['success' => true, 'message' => 'Despesa removida.']);
    }
}
