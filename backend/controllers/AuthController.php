<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../utils/Validator.php';

class AuthController
{
    private $pdo;
    private $userModel;
    private $config;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        $this->config = require __DIR__ . '/../config/app.php';
    }

    private function generateToken(array $user)
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => $this->config['jwt']['issuer'],
            'aud' => $this->config['jwt']['audience'],
            'iat' => time(),
            'exp' => time() + $this->config['jwt']['expiration'],
            'sub' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'type' => $user['type']
        ]));
        $signature = hash_hmac('sha256', "$header.$payload", $this->config['jwt']['secret'], true);
        $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return "$header.$payload.$signature";
    }

    public function register(array $data)
    {
        $missing = Validator::validateRequired($data, ['name', 'email', 'password']);
        if ($missing) {
            Response::error('Campos obrigatórios ausentes: ' . implode(', ', $missing), 422);
        }

        $email = Validator::sanitizeEmail($data['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('Email inválido.', 422);
        }

        if ($this->userModel->findByEmail($email)) {
            Response::error('Email já está em uso.', 409);
        }

        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $userId = $this->userModel->create([
            'name' => Validator::sanitizeString($data['name']),
            'email' => $email,
            'password' => $password,
            'type' => 'user'
        ]);

        Response::json(['success' => true, 'message' => 'Usuário criado com sucesso.', 'user_id' => $userId], 201);
    }

    public function login(array $data)
    {
        $missing = Validator::validateRequired($data, ['email', 'password']);
        if ($missing) {
            Response::error('Campos obrigatórios ausentes: ' . implode(', ', $missing), 422);
        }

        $email = Validator::sanitizeEmail($data['email']);
        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            Response::error('Credenciais inválidas.', 401);
        }

        Response::json([
            'success' => true,
            'token' => $this->generateToken($user),
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'type' => $user['type']
            ]
        ]);
    }

    public function forgotPassword(array $data)
    {
        $missing = Validator::validateRequired($data, ['email']);
        if ($missing) {
            Response::error('Email é obrigatório.', 422);
        }

        $user = $this->userModel->findByEmail(Validator::sanitizeEmail($data['email']));
        if (!$user) {
            Response::error('Usuário não encontrado.', 404);
        }

        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', time() + 3600);
        $this->userModel->updateResetToken($user['id'], $token, $expires);

        Response::json(['success' => true, 'message' => 'Token de recuperação gerado.', 'reset_token' => $token]);
    }

    public function resetPassword(array $data)
    {
        $missing = Validator::validateRequired($data, ['token', 'password']);
        if ($missing) {
            Response::error('Campos obrigatórios ausentes: ' . implode(', ', $missing), 422);
        }

        $user = $this->userModel->findByResetToken($data['token']);
        if (!$user || strtotime($user['password_reset_expires']) < time()) {
            Response::error('Token inválido ou expirado.', 403);
        }

        $this->userModel->updatePassword($user['id'], password_hash($data['password'], PASSWORD_DEFAULT));
        Response::json(['success' => true, 'message' => 'Senha atualizada com sucesso.']);
    }
}
