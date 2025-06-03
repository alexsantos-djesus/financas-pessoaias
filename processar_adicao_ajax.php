<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$descricao = $_POST['descricao'] ?? '';
$valor = $_POST['valor'] ?? '';
$data = $_POST['data'] ?? '';
$status = $_POST['status'] ?? '';
$tipo = $_POST['tipo'] ?? '';

if (empty($descricao) || empty($valor) || empty($data) || empty($status) || empty($tipo)) {
    echo json_encode(['error' => 'Todos os campos são obrigatórios.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO transacoes (usuario_id, descricao, valor, data, status, tipo)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$usuario_id, $descricao, $valor, $data, $status, $tipo]);

    if ($stmt->rowCount() > 0) {
        $id = $pdo->lastInsertId(); // Obter ID da nova transação

        echo json_encode([
            'success' => 'Transação adicionada com sucesso.',
            'id' => $id,
            'descricao' => $descricao,
            'valor' => number_format($valor, 2),
            'status' => $status,
            'tipo' => $tipo
        ]);
    } else {
        echo json_encode(['error' => 'Nenhuma transação foi adicionada.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao salvar transação: ' . $e->getMessage()]);
}
?>