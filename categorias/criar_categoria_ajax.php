<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nome = $_POST['nome'] ?? '';
$tipo = $_POST['tipo'] ?? '';

if (empty($nome) || !in_array($tipo, ['receita', 'despesa'])) {
    echo json_encode(['error' => 'Nome e tipo são obrigatórios.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO categorias (usuario_id, nome, tipo) VALUES (?, ?, ?)");
    $stmt->execute([$usuario_id, $nome, $tipo]);

    echo json_encode([
        'success' => 'Categoria adicionada com sucesso.',
        'id' => $pdo->lastInsertId(),
        'nome' => $nome,
        'tipo' => $tipo
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao salvar categoria: ' . $e->getMessage()]);
}
