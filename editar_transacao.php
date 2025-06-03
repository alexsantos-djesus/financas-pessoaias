<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID da transação não fornecido']);
    exit;
}

$descricao = $_POST['descricao'] ?? '';
$valor = $_POST['valor'] ?? '';
$data = $_POST['data'] ?? '';
$status = $_POST['status'] ?? '';
$tipo = $_POST['tipo'] ?? '';

try {
    $stmt = $pdo->prepare("
        UPDATE transacoes
        SET descricao = ?, valor = ?, data = ?, status = ?, tipo = ?
        WHERE id = ? AND usuario_id = ?
    ");
    $stmt->execute([$descricao, $valor, $data, $status, $tipo, $id, $usuario_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => 'Transação atualizada com sucesso!']);
    } else {
        echo json_encode(['error' => 'Nenhuma transação foi atualizada.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao salvar transação: ' . $e->getMessage()]);
}
?>