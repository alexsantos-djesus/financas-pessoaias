<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Função para calcular subtotal por tipo
function getSubtotal($pdo, $usuario_id, $tipo)
{
    $stmt = $pdo->prepare("SELECT SUM(valor) AS total FROM transacoes WHERE usuario_id = ? AND status != 'previsto' AND tipo = ?");
    $stmt->execute([$usuario_id, $tipo]);
    return $stmt->fetch()['total'] ?? 0;
}

$saldo_receitas = getSubtotal($pdo, $usuario_id, 'receita');
$saldo_despesas = getSubtotal($pdo, $usuario_id, 'despesa');
$saldo_total = $saldo_receitas - $saldo_despesas;

$message = '';
if (isset($_SESSION['mensagem'])) {
    $message = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Finanças Pessoais</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .status-realizado {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pendente {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-previsto {
            background-color: #f8d7da;
            color: #721c24;
        }

        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }

        .form-box {
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 576px) {
            .form-box {
                margin: 10px;
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container py-5 my-5 text-center">
        <h1 class="mb-4">Dashboard - Finanças Pessoais</h1>

        <!-- Botão Sair -->
        <div class="mb-4 d-flex justify-content-end align-items-center w-100">
            <a href="logout.php" class="btn btn-danger btn-sm">Sair</a>
        </div>

        <!-- Mensagem de Feedback -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message['tipo']); ?>" role="alert">
                <?php echo htmlspecialchars($message['texto']); ?>
            </div>
        <?php endif; ?>

        <!-- Resumo Geral -->
        <div class="row mb-4 justify-content-center">
            <div class="col-md-4 col-sm-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Saldo Total</h5>
                        <p class="card-text display-6">R$ <?= number_format($saldo_total, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Receitas</h5>
                        <p class="card-text display-6">R$ <?= number_format($saldo_receitas, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="card shadow-sm mb-3">
                    <div class="card-body text-center">
                        <h5 class="card-title">Despesas</h5>
                        <p class="card-text display-6">R$ <?= number_format($saldo_despesas, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Transações -->
        <div class="row justify-content-center">
            <div class="col-md-12">
                <h2 class="mb-3">Transações</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">Descrição</th>
                                <th scope="col">Valor</th>
                                <th scope="col">Data</th>
                                <th scope="col">Status</th>
                                <th scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="transacoes-table">
                            <?php
                            $stmt = $pdo->prepare("
                                    SELECT t.*, c.nome AS categoria_nome 
                                    FROM transacoes t
                                    LEFT JOIN categorias c ON t.categoria_id = c.id
                                    WHERE t.usuario_id = ?
                                ");
                            $stmt->execute([$usuario_id]);

                            while ($transacao = $stmt->fetch()) {
                                $status_class = '';
                                if ($transacao['status'] === 'realizado') {
                                    $status_class = 'status-realizado';
                                } elseif ($transacao['status'] === 'pendente') {
                                    $status_class = 'status-pendente';
                                } else {
                                    $status_class = 'status-previsto';
                                }

                                echo "<tr id='transacao-" . $transacao['id'] . "' class='" . $status_class . "'>";
                                echo "<td>" . htmlspecialchars($transacao['descricao']) . "</td>";
                                echo "<td>R$ " . number_format($transacao['valor'], 2, ',', '.') . "</td>";
                                echo "<td>" . htmlspecialchars($transacao['data']) . "</td>";
                                echo "<td>" . htmlspecialchars($transacao['categoria_nome'] ?? 'Sem categoria') . "</td>";
                                echo "<td>" . ($transacao['status'] === 'realizado' ? 'Realizado ✅' : ($transacao['status'] === 'pendente' ? 'Pendente ⚠️' : 'Previsto ⏰')) . "</td>";
                                echo "<td>
                                        <button class='btn btn-sm btn-primary edit-transacao' data-id='{$transacao['id']}'>Editar</button>
                                        <a href='excluir_transacao.php?id={$transacao['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Tem certeza que deseja excluir?\")'>Excluir</a>
                                      </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Botão Adicionar Transação -->
        <div class="text-center mt-4">
            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addTransacaoModal">
                Adicionar Transação
            </button>
            <button type="button" class="btn btn-outline-primary btn-lg" id="btn-carregar-categorias">
                Gerenciar Categorias
            </button>

            <div id="area-categorias" style="display: none;">
                <div id="conteudo-categorias"></div>
            </div>
        </div>
    </div>

    <!-- Modal para Adicionar Transação -->
    <div class="modal fade" id="addTransacaoModal" tabindex="-1" aria-labelledby="addTransacaoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTransacaoModalLabel">Adicionar Transação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form id="addTransacaoForm">
                    <input type="hidden" id="add_transacao_id" name="id">

                    <div class="modal-body form-box">
                        <div class="mb-3">
                            <label for="descricao_add" class="form-label">Descrição</label>
                            <input type="text" class="form-control" id="descricao_add" name="descricao" placeholder="Ex: Salário" required>
                        </div>
                        <div class="mb-3">
                            <label for="valor_add" class="form-label">Valor</label>
                            <input type="number" step="0.01" class="form-control" id="valor_add" name="valor" placeholder="Ex: 1000.00" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_add" class="form-label">Data</label>
                            <input type="date" class="form-control" id="data_add" name="data" required>
                        </div>
                        <div class="mb-3">
                            <label for="status_add" class="form-label">Status</label>
                            <select class="form-select" id="status_add" name="status" required>
                                <option value="previsto">Previsto</option>
                                <option value="realizado">Realizado</option>
                                <option value="pendente">Pendente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_add" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo_add" name="tipo" required>
                                <option value="receita">Receita</option>
                                <option value="despesa">Despesa</option>
                            </select>
                        </div>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE usuario_id = ?");
                        $stmt->execute([$_SESSION['usuario_id']]);
                        $categorias = $stmt->fetchAll();
                        ?>
                        <div class="mb-3">
                            <label for="categoria_add" class="form-label">Categoria</label>
                            <select class="form-select" id="categoria_add" name="categoria_id">
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Transação -->
    <div class="modal fade" id="editTransacaoModal" tabindex="-1" aria-labelledby="editTransacaoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTransacaoModalLabel">Editar Transação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form id="editTransacaoForm">
                    <input type="hidden" id="edit_transacao_id" name="id">

                    <div class="modal-body form-box">
                        <div class="mb-3">
                            <label for="descricao_edit" class="form-label">Descrição</label>
                            <input type="text" class="form-control" id="descricao_edit" name="descricao" required>
                        </div>
                        <div class="mb-3">
                            <label for="valor_edit" class="form-label">Valor</label>
                            <input type="number" step="0.01" class="form-control" id="valor_edit" name="valor" required>
                        </div>
                        <div class="mb-3">
                            <label for="data_edit" class="form-label">Data</label>
                            <input type="date" class="form-control" id="data_edit" name="data" required>
                        </div>
                        <div class="mb-3">
                            <label for="status_edit" class="form-label">Status</label>
                            <select class="form-select" id="status_edit" name="status" required>
                                <option value="previsto">Previsto</option>
                                <option value="realizado">Realizado</option>
                                <option value="pendente">Pendente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_edit" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo_edit" name="tipo" required>
                                <option value="receita">Receita</option>
                                <option value="despesa">Despesa</option>
                            </select>
                        </div>
                        <?php
                        $stmt = $pdo->prepare("SELECT * FROM categorias WHERE usuario_id = ?");
                        $stmt->execute([$_SESSION['usuario_id']]);
                        $categorias_edit = $stmt->fetchAll();
                        ?>
                        <div class="mb-3">
                            <label for="categoria_edit" class="form-label">Categoria</label>
                            <select class="form-select" id="categoria_edit" name="categoria_id">
                                <option value="">Selecione uma categoria</option>
                                <?php foreach ($categorias_edit as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Abrir modal de edição
            $('.edit-transacao').on('click', function() {
                const transacaoId = $(this).data('id');

                $.ajax({
                    url: 'get_transacao.php',
                    type: 'GET',
                    data: {
                        id: transacaoId
                    },
                    success: function(response) {
                        const transacao = JSON.parse(response);

                        $('#edit_transacao_id').val(transacao.id);
                        $('#descricao_edit').val(transacao.descricao);
                        $('#valor_edit').val(transacao.valor);
                        $('#data_edit').val(transacao.data);
                        $('#status_edit').val(transacao.status);
                        $('#tipo_edit').val(transacao.tipo); // Campo Tipo incluso

                        $('#editTransacaoModal').modal('show');
                    },
                    error: function() {
                        alert('Erro ao carregar dados da transação.');
                    }
                });
            });

            // Enviar formulário de edição via AJAX
            $('#editTransacaoForm').on('submit', function(e) {
                e.preventDefault();

                const formData = {
                    id: $('#edit_transacao_id').val(),
                    descricao: $('#descricao_edit').val(),
                    valor: $('#valor_edit').val(),
                    data: $('#data_edit').val(),
                    status: $('#status_edit').val(),
                    tipo: $('#tipo_edit').val()
                };

                $.ajax({
                    url: 'editar_transacao.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        const result = JSON.parse(response);

                        if (result.success) {
                            const row = $('#transacao-' + formData.id);
                            row.find('td:eq(0)').text(formData.descricao);
                            row.find('td:eq(1)').text('R$ ' + parseFloat(formData.valor).toFixed(2).replace('.', ','));
                            row.find('td:eq(2)').text(formData.data);
                            row.find('td:eq(3)').text(formData.status === 'realizado' ? 'Realizado ✅' : formData.status === 'pendente' ? 'Pendente ⚠️' : 'Previsto ⏰');

                            row.removeClass('status-realizado status-pendente status-previsto');

                            if (formData.status === 'realizado') {
                                row.addClass('status-realizado');
                            } else if (formData.status === 'pendente') {
                                row.addClass('status-pendente');
                            } else {
                                row.addClass('status-previsto');
                            }

                            $('#editTransacaoModal').modal('hide');
                            $('#editTransacaoForm')[0].reset();
                            alert(result.success);
                        } else {
                            alert(result.error || 'Erro desconhecido ao atualizar transação.');
                        }
                    },
                    error: function() {
                        alert('Erro ao comunicar com o servidor.');
                    }
                });
            });

            // Enviar formulário de adição via AJAX
            $('#addTransacaoForm').on('submit', function(e) {
                e.preventDefault();

                const categoria_id = $('#categoria_add').val(); // Pega o valor do select

                const formData = {
                    descricao: $('#descricao_add').val(),
                    valor: $('#valor_add').val(),
                    data: $('#data_add').val(),
                    status: $('#status_add').val(),
                    tipo: $('#tipo_add').val(),
                    categoria_id: categoria_id // Adiciona a categoria
                };

                $.ajax({
                    url: 'processar_adicao_ajax.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        const result = JSON.parse(response);

                        if (result.success) {
                            const newRow = `
                                <tr id="transacao-${result.id}" class="${formData.status === 'realizado' ? 'status-realizado' : ''}">
                                    <td>${formData.descricao}</td>
                                    <td>R$ ${parseFloat(formData.valor).toFixed(2).replace('.', ',')}</td>
                                    <td>${formData.data}</td>
                                    <td>${formData.status === 'realizado' ? 'Realizado ✅' : formData.status === 'pendente' ? 'Pendente ⚠️' : 'Previsto ⏰'}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-transacao" data-id="${result.id}">Editar</button>
                                        <a href="excluir_transacao.php?id=${result.id}" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
                                    </td>
                                </tr>
                            `;

                            $('#transacoes-table').append(newRow);

                            // Re-inicializa o evento do botão Editar para a nova transação
                            $(newRow).find('.edit-transacao').on('click', function() {
                                const transacaoId = $(this).data('id');

                                $.ajax({
                                    url: 'get_transacao.php',
                                    type: 'GET',
                                    data: {
                                        id: transacaoId
                                    },
                                    success: function(response) {
                                        const transacao = JSON.parse(response);

                                        $('#transacao_id_edit').val(transacao.id);
                                        $('#descricao_edit').val(transacao.descricao);
                                        $('#valor_edit').val(transacao.valor);
                                        $('#data_edit').val(transacao.data);
                                        $('#status_edit').val(transacao.status);
                                        $('#tipo_edit').val(transacao.tipo); // Campo Tipo incluso

                                        $('#editTransacaoModal').modal('show');
                                    },
                                    error: function() {
                                        alert('Erro ao carregar dados da transação.');
                                    }
                                });
                            });

                            $('#addTransacaoModal').modal('hide');
                            $('#addTransacaoForm')[0].reset();
                            alert(result.success);
                        } else {
                            alert(result.error || 'Erro ao adicionar a transação.');
                        }
                    },
                    error: function() {
                        alert('Erro ao comunicar com o servidor.');
                    }
                });
            });
        });
    </script>

    <!-- Script para carregar categorias via AJAX -->
    <script>
        $(document).ready(function() {
            $('#btn-carregar-categorias').on('click', function() {
                const area = $('#area-categorias');

                if (area.is(':visible')) {
                    area.hide();
                    return;
                }

                $.ajax({
                    url: 'categorias/categorias_ajax.php',
                    method: 'GET',
                    success: function(response) {
                        $('#area-categorias').html(response);
                        area.show();
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao carregar categorias.');
                        console.error(xhr.responseText); // Mostra detalhes no console
                    }
                });
            });
        });
    </script>
</body>

</html>