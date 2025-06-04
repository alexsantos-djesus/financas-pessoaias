<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Carrega todas as categorias do usuário
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($categorias)) {
    echo json_encode(['error' => 'Nenhuma categoria encontrada.']);
    exit;
}

?>
<h3 class="mt-5 mb-3">Minhas Categorias</h3>

<form id="addCategoriaForm">
    <div class="mb-3">
        <label for="nome_add" class="form-label">Nome da Categoria</label>
        <input type="text" class="form-control" id="nome_add" name="nome" placeholder="Ex: Alimentação" required>
    </div>
    <div class="mb-3">
        <label for="tipo_add" class="form-label">Tipo</label>
        <select class="form-select" id="tipo_add" name="tipo" required>
            <option value="">Selecione o tipo</option>
            <option value="receita">Receita</option>
            <option value="despesa">Despesa</option>
        </select>
    </div>
    <button type="submit" class="btn btn-success w-100 mb-4">Adicionar Categoria</button>
</form>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="categoria-table">
            <?php foreach ($categorias as $cat): ?>
                <tr id="categoria-row-<?php echo $cat['id']; ?>">
                    <td><?= htmlspecialchars($cat['id']) ?></td>
                    <td><?= htmlspecialchars($cat['nome']) ?></td>
                    <td><?= ucfirst(htmlspecialchars($cat['tipo'])) ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary me-2 edit-categoria" data-id="<?= $cat['id'] ?>" data-bs-toggle="modal" data-bs-target="#editCategoriaModal">Editar</button>
                        <button class="btn btn-sm btn-danger delete-categoria" data-id="<?= $cat['id'] ?>">Excluir</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Editar Categoria -->
<div class="modal fade" id="editCategoriaModal" tabindex="-1" aria-labelledby="editCategoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoriaModalLabel">Editar Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoriaForm">
                <input type="hidden" id="categoria_id_edit" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoria_nome_edit" class="form-label">Nome da Categoria</label>
                        <input type="text" class="form-control" id="categoria_nome_edit" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoria_tipo_edit" class="form-label">Tipo</label>
                        <select class="form-select" id="categoria_tipo_edit" name="tipo" required>
                            <option value="receita">Receita</option>
                            <option value="despesa">Despesa</option>
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