<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Response.php';

class AdminController
{
    private $userModel;

    public function __construct(PDO $pdo)
    {
        $this->userModel = new User($pdo);
    }

    public function users()
    {
        $users = $this->userModel->listAll();
        Response::json(['success' => true, 'users' => $users]);
    }

    public function updateUser(int $id, array $data)
    {
        if (!in_array($data['type'] ?? '', ['admin', 'user'], true)) {
            Response::error('Tipo de utilizador inválido.', 422);
        }

        $this->userModel->updateType($id, $data['type']);
        Response::json(['success' => true, 'message' => 'Utilizador atualizado.']);
    }

    public function deleteUser(int $id, array $currentUser)
    {
        if ($id === (int)$currentUser['sub']) {
            Response::error('Não pode excluir o próprio utilizador.', 422);
        }

        $this->userModel->delete($id);
        Response::json(['success' => true, 'message' => 'Utilizador excluído.']);
    }
}
