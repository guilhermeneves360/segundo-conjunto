<?php

$config = require __DIR__ . '/app.php';

try {
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
        $config['db']['host'],
        $config['db']['name'],
        $config['db']['charset']
    );

    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Conexão com banco de dados falhou.']);
    exit;
}
