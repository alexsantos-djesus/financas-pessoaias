<?php
session_start();

// Verifica se o usuário está logado
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finanças Pessoais</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 500px;
            margin-top: 100px;
        }
    </style>
</head>
<body>
    <div class="container text-center">
        <h1 class="mb-4">Bem-vindo ao Sistema de Finanças Pessoais</h1>
        <p class="lead">Gerencie suas receitas e despesas de forma simples e eficiente.</p>

        <!-- Botões de Login e Registro -->
        <a href="login.php" class="btn btn-primary btn-lg me-2">Login</a>
        <a href="registro.php" class="btn btn-outline-primary btn-lg">Registrar</a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>