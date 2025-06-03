<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

// Processa requisição AJAX (não usada aqui, mas pode ser usada futuramente)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tratado no JavaScript via AJAX
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Transação</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-box {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        @media (max-width: 576px) {
            .form-box {
                margin: 20px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5 my-5">
        <h1 class="text-center mb-4">Adicionar Transação</h1>

        <!-- Botão Voltar -->
        <div class="mb-4 text-center">
            <a href="dashboard.php" class="btn btn-secondary btn-sm">Voltar</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="form-box">
            <form id="addTransacaoForm" class="row g-3">
                <div class="col-md-12">
                    <label for="descricao" class="form-label">Descrição</label>
                    <input type="text" class="form-control" name="descricao" placeholder="Ex: Salário" required>
                </div>
                <div class="col-md-6">
                    <label for="valor" class="form-label">Valor</label>
                    <input type="number" step="0.01" class="form-control" name="valor" placeholder="Ex: 1000.00" required>
                </div>
                <div class="col-md-6">
                    <label for="data" class="form-label">Data</label>
                    <input type="date" class="form-control" name="data" required>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="previsto">Previsto</option>
                        <option value="realizado">Realizado</option>
                        <option value="pendente">Pendente</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-select" name="tipo" required>
                        <option value="receita">Receita</option>
                        <option value="despesa">Despesa</option>
                    </select>
                </div>
                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS e jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#addTransacaoForm').on('submit', function (e) {
                e.preventDefault();

                const formData = {
                    descricao: $('input[name="descricao"]').val(),
                    valor: $('input[name="valor"]').val(),
                    data: $('input[name="data"]').val(),
                    status: $('select[name="status"]').val(),
                    tipo: $('select[name="tipo"]').val()
                };

                $.ajax({
                    url: 'processar_adicao_ajax.php',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        const result = JSON.parse(response);

                        if (result.success) {
                            // Adiciona a nova transação na tabela do dashboard
                            const newRow = `
                                <tr id="transacao-${formData.id}" class="${formData.status === 'realizado' ? 'status-realizado' : ''}">
                                    <td>${formData.descricao}</td>
                                    <td>R$ ${parseFloat(formData.valor).toFixed(2).replace('.', ',')}</td>
                                    <td>${formData.data}</td>
                                    <td>${formData.status.charAt(0).toUpperCase() + formData.status.slice(1)}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-transacao" data-id="${formData.id}">Editar</button>
                                        <a href="excluir_transacao.php?id=${formData.id}" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                                    </td>
                                </tr>
                            `;

                            $('#transacoes-table').append(newRow);

                            // Limpar o formulário
                            $('#addTransacaoForm')[0].reset();

                            alert(result.success);
                        } else {
                            alert(result.error || 'Erro ao adicionar a transação.');
                        }
                    },
                    error: function () {
                        alert('Erro ao comunicar com o servidor.');
                    }
                });
            });
        });
    </script>
</body>
</html>