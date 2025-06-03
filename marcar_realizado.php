<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$transacao_id = $_GET['id'] ?? null;

if (!$transacao_id) {
    header("Location: dashboard.php");
    exit;
}

// Atualiza o status da transação para "realizado"
$stmt = $pdo->prepare("
    UPDATE transacoes
    SET status = 'realizado'
    WHERE id = ? AND usuario_id = ?
");
$stmt->execute([$transacao_id, $_SESSION['usuario_id']]);

header("Location: dashboard.php");
exit;
?>