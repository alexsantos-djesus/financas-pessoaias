$(document).ready(function () {
    // Carregar categorias via AJAX
    $('#btn-carregar-categorias').on('click', function () {
        const area = $('#area-categorias');

        if (area.is(':visible')) {
            area.hide();
            return;
        }

        $.ajax({
            url: 'categorias/categorias_ajax.php',
            method: 'GET',
            success: function (response) {
                $('#conteudo-categorias').html(response);
                area.show();
            },
            error: function () {
                alert('Erro ao carregar categorias.');
            }
        });
    });

    // Adicionar categoria via AJAX
    $(document).on('submit', '#addCategoriaForm', function (e) {
        e.preventDefault();

        const formData = {
            nome: $('#nome_add').val(),
            tipo: $('#tipo_add').val()
        };

        $.ajax({
            url: 'categorias/criar_categoria_ajax.php',
            method: 'POST',
            data: formData,
            success: function (response) {
                const result = JSON.parse(response);
                if (result.success) {
                    const newRow = `
                        <tr id="categoria-row-${result.id}">
                            <td>${result.id}</td>
                            <td>${formData.nome}</td>
                            <td>${formData.tipo === 'receita' ? 'Receita' : 'Despesa'}</td>
                            <td>
                                <button class='btn btn-sm btn-primary me-2 edit-categoria' data-id='${result.id}' data-bs-toggle='modal' data-bs-target='#editCategoriaModal'>Editar</button>
                                <button class='btn btn-sm btn-danger delete-categoria' data-id='${result.id}'>Excluir</button>
                            </td>
                        </tr>
                    `;
                    $('#categoria-table').append(newRow);
                    $('#nome_add').val('');
                    $('#tipo_add').val('');
                    alert(result.success);
                } else {
                    alert(result.error || 'Erro ao adicionar categoria.');
                }
            },
            error: function () {
                alert('Erro ao comunicar com o servidor.');
            }
        });
    });

    // Abrir modal de edição
    $(document).on('click', '.edit-categoria', function () {
        const categoriaId = $(this).data('id');
        $.ajax({
            url: 'categorias/get_categoria_ajax.php',
            method: 'GET',
            data: { id: categoriaId },
            success: function (response) {
                const cat = JSON.parse(response);
                $('#categoria_id_edit').val(cat.id);
                $('#categoria_nome_edit').val(cat.nome);
                $('#categoria_tipo_edit').val(cat.tipo);
            },
            error: function () {
                alert('Erro ao carregar dados da categoria.');
            }
        });
    });

    // Enviar formulário de edição
    $(document).on('submit', '#editCategoriaForm', function (e) {
        e.preventDefault();

        const formData = {
            id: $('#categoria_id_edit').val(),
            nome: $('#categoria_nome_edit').val(),
            tipo: $('#categoria_tipo_edit').val()
        };

        $.ajax({
            url: 'categorias/editar_categoria_ajax.php',
            method: 'POST',
            data: formData,
            success: function (response) {
                const result = JSON.parse(response);
                if (result.success) {
                    $(`#categoria-row-${formData.id} td:eq(1)`).text(formData.nome);
                    $(`#categoria-row-${formData.id} td:eq(2)`).text(formData.tipo === 'receita' ? 'Receita' : 'Despesa');
                    $('#editCategoriaModal').modal('hide');
                    alert(result.success);
                } else {
                    alert(result.error || 'Erro ao atualizar categoria.');
                }
            },
            error: function () {
                alert('Erro ao comunicar com o servidor.');
            }
        });
    });

    // Excluir categoria
    $(document).on('click', '.delete-categoria', function () {
        const categoriaId = $(this).data('id');

        if (!confirm("Tem certeza que deseja excluir esta categoria?")) return;

        $.ajax({
            url: 'categorias/excluir_categoria_ajax.php',
            method: 'POST',
            data: { id: categoriaId },
            success: function (response) {
                const result = JSON.parse(response);
                if (result.success) {
                    $(`#categoria-row-${categoriaId}`).fadeOut(300, function () {
                        $(this).remove();
                    });
                    alert(result.success);
                } else {
                    alert(result.error || 'Erro ao excluir categoria.');
                }
            },
            error: function () {
                alert('Erro ao comunicar com o servidor.');
            }
        });
    });
});