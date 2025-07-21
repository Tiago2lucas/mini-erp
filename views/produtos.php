<?php
// A variável $produtos (para listagem) e $produto_para_edicao (para preencher o form)
// são passadas para esta View pelo ProdutoController.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Cadastro de Produtos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      .variacao-row { margin-bottom: 10px; }
    </style>
</head>
<body class="container mt-5">

    <h2 class="mb-4">
        <?php if (isset($produto_para_edicao) && !empty($produto_para_edicao)): ?>
            Editando Produto: <?= htmlspecialchars($produto_para_edicao['nome']) ?>
        <?php else: ?>
            Cadastro de Produto
        <?php endif; ?>
    </h2>

<?php
    // Mensagens de feedback (sucesso/erro)
    if (isset($_GET['ok'])) {
        $msg_sucesso = "Produto salvo com sucesso!";
        if ($_GET['ok'] == '2') {
            $msg_sucesso = "Produto atualizado com sucesso!";
        } elseif ($_GET['ok'] == '3') { // Adicionado para exclusão
            $msg_sucesso = "Produto excluído com sucesso!";
        }
        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($msg_sucesso) . '</div>';
    }
    if (isset($_GET['erro'])) {
        $msg_erro = "Ocorreu um erro ao salvar o produto.";
        if ($_GET['erro'] == 'dados_invalidos') {
            $msg_erro = "Nome e preço são obrigatórios e o preço deve ser um número válido.";
        } elseif ($_GET['erro'] == 'falha_cadastro') {
            $msg_erro = "Falha interna ao cadastrar o produto no banco de dados.";
        } elseif ($_GET['erro'] == 'falha_atualizacao') { // Adicionado para falha na atualização
            $msg_erro = "Falha interna ao atualizar o produto no banco de dados.";
        } elseif ($_GET['erro'] == 'falha_exclusao') { // Adicionado para falha na exclusão
            $msg_erro = "Falha interna ao excluir o produto do banco de dados.";
        }
        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($msg_erro) . '</div>';
    }
    ?>




    <form method="POST" action="/mini-erp/public/index.php?rota=produtos&acao=<?= isset($produto_para_edicao) && !empty($produto_para_edicao) ? 'atualizar' : 'salvar' ?>" class="mb-5">
        <input type="hidden" name="id" value="<?= isset($produto_para_edicao['id']) ? htmlspecialchars($produto_para_edicao['id']) : '' ?>">

        <div class="mb-3">
            <label class="form-label">Nome:</label>
            <input type="text" name="nome" class="form-control" required
                   value="<?= isset($produto_para_edicao['nome']) ? htmlspecialchars($produto_para_edicao['nome']) : '' ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Preço (R$):</label>
            <input type="number" step="0.01" name="preco" class="form-control" required
                   value="<?= isset($produto_para_edicao['preco']) ? htmlspecialchars($produto_para_edicao['preco']) : '' ?>">
        </div>

        <hr>

        <h5>Variações e Estoque</h5>
        <div id="variacoes-wrapper">
            <?php if (isset($produto_para_edicao['estoques']) && !empty($produto_para_edicao['estoques'])): ?>
                <?php foreach ($produto_para_edicao['estoques'] as $estoque): ?>
                    <div class="row variacao-row">
                        <div class="col-md-6">
                            <input type="text" name="variacao[]" class="form-control" placeholder="Ex: Tamanho M"
                                   value="<?= htmlspecialchars($estoque['variacao']) ?>">
                            <input type="hidden" name="estoque_id[]" value="<?= htmlspecialchars($estoque['id']) ?>">
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="quantidade[]" class="form-control" placeholder="Qtd"
                                   value="<?= htmlspecialchars($estoque['quantidade']) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remover-variacao">X</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="row variacao-row">
                    <div class="col-md-6">
                        <input type="text" name="variacao[]" class="form-control" placeholder="Ex: Tamanho M">
                        <input type="hidden" name="estoque_id[]" value=""> </div>
                    <div class="col-md-4">
                        <input type="number" name="quantidade[]" class="form-control" placeholder="Qtd">
                    </div>
                    <div class="col-md-2">
                        </div>
                </div>
            <?php endif; ?>
        </div>

        <button type="button" id="add-variacao" class="btn btn-outline-primary btn-sm mb-3">+ Adicionar variação</button>
        <br>

        <button type="submit" class="btn btn-success">
            <?php if (isset($produto_para_edicao) && !empty($produto_para_edicao)): ?>
                Atualizar Produto
            <?php else: ?>
                Salvar Produto
            <?php endif; ?>
        </button>
    </form>

    <hr>

    <h3>Produtos Cadastrados</h3>

    <?php if (empty($produtos)): // $produtos é passado pelo ProdutoController ?>
        <p>Nenhum produto cadastrado ainda.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço (R$)</th>
                    <th>Variação</th>
                    <th>Estoque</th>
                    <th>Ações</th>
                </tr>
            </thead>

<tbody>
            <?php foreach ($produtos as $id => $p): ?>
                <?php if (empty($p['estoques'])): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td><?= number_format($p['preco'], 2, ',', '.') ?></td>
                        <td>—</td>
                        <td>—</td>
                        <td>
                            <a href="/mini-erp/public/index.php?rota=carrinho&acao=adicionar&id=<?= $p['id'] ?>" class="btn btn-info btn-sm">Comprar</a>
                            <a href="/mini-erp/public/index.php?rota=produtos&acao=editar&id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="/mini-erp/public/index.php?rota=produtos&acao=excluir&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este produto e todas as suas variações de estoque?');">Excluir</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($p['estoques'] as $linha): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nome']) ?></td>
                            <td><?= number_format($p['preco'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($linha['variacao']) ?></td>
                            <td><?= (int)$linha['quantidade'] ?></td>
                            <td>
                                <a href="/mini-erp/public/index.php?rota=carrinho&acao=adicionar&id=<?= $p['id'] ?>" class="btn btn-info btn-sm">Comprar</a>
                                <a href="/mini-erp/public/index.php?rota=produtos&acao=editar&id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                                <a href="/mini-erp/public/index.php?rota=produtos&acao=excluir&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este produto e todas as suas variações de estoque?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>


    <?php endif; ?>

    <script>
    document.getElementById('add-variacao').addEventListener('click', function() {
        const wrapper = document.getElementById('variacoes-wrapper');
        const row = document.createElement('div');
        row.className = 'row variacao-row';
        row.innerHTML = `
            <div class="col-md-6">
                <input type="text" name="variacao[]" class="form-control" placeholder="Ex: Cor Azul">
                <input type="hidden" name="estoque_id[]" value=""> </div>
            <div class="col-md-4">
                <input type="number" name="quantidade[]" class="form-control" placeholder="Qtd">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remover-variacao">X</button>
            </div>
        `;
        wrapper.appendChild(row);
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remover-variacao')) {
            e.target.closest('.variacao-row').remove();
        }
    });
    </script>

</body>
</html>