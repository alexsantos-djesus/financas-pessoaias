<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID da transação não fornecido']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM transacoes WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $usuario_id]);
$transacao = $stmt->fetch();

if ($transacao) {
    echo json_encode($transacao);
} else {
    echo json_encode(['error' => 'Transação não encontrada']);
}
?>