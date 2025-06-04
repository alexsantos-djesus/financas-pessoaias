<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$id = $_POST['id'] ?? null;
$nome = $_POST['nome'] ?? '';
$tipo = $_POST['tipo'] ?? '';

if (!$id || empty($nome) || !in_array($tipo, ['receita', 'despesa'])) {
    echo json_encode(['error' => 'Dados inválidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE categorias SET nome = ?, tipo = ? WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$nome, $tipo, $id, $usuario_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => 'Categoria atualizada com sucesso!']);
    } else {
        echo json_encode(['error' => 'Nenhuma alteração foi feita.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Erro ao salvar categoria.']);
}
