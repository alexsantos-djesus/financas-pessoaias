<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensagem'] = ['tipo' => 'error', 'texto' => 'Você precisa estar logado.'];
    header("Location: dashboard.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        $_SESSION['mensagem'] = ['tipo' => 'error', 'texto' => 'ID da transação não fornecido.'];
        header("Location: dashboard.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM transacoes WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$id, $usuario_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['mensagem'] = ['tipo' => 'success', 'texto' => 'Transação excluída com sucesso.'];
        } else {
            $_SESSION['mensagem'] = ['tipo' => 'error', 'texto' => 'Não foi possível excluir a transação.'];
        }
    } catch (Exception $e) {
        $_SESSION['mensagem'] = ['tipo' => 'error', 'texto' => 'Erro ao excluir transação: ' . $e->getMessage()];
    }
} else {
    $_SESSION['mensagem'] = ['tipo' => 'error', 'texto' => 'Acesso inválido.'];
}

header("Location: dashboard.php");
exit;
