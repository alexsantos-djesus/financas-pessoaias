<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID da categoria não fornecido']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM categorias WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => 'Categoria excluída com sucesso.']);
    } else {
        echo json_encode(['error' => 'Categoria não encontrada ou não pertence a você.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao excluir categoria.']);
}
