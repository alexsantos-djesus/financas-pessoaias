<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID da categoria não fornecido']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $_SESSION['usuario_id']]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if ($categoria) {
    echo json_encode($categoria);
} else {
    echo json_encode(['error' => 'Categoria não encontrada']);
}
