<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title>Meu Carrinho</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .table img {
        max-width: 50px;
        height: auto;
        margin-right: 10px;
    }
    </style>
</head>

<body class="container mt-5">

    <h2 class="mb-4">Meu Carrinho</h2>



    <?php
    // Mensagens de feedback de erro de estoque
    if (isset($_GET['erro_estoque'])) {
        $item_nome = htmlspecialchars($_GET['item'] ?? 'item');
        $max_disponivel = htmlspecialchars($_GET['max'] ?? '0');
        $mensagem = "";

        if ($_GET['erro_estoque'] == 'insuficiente') {
            $mensagem = "Não há estoque suficiente para adicionar mais de <strong>" . $item_nome . "</strong> ao carrinho.";
        } elseif ($_GET['erro_estoque'] == 'max_atingido') {
            $mensagem = "A quantidade máxima disponível para <strong>" . $item_nome . "</strong> é " . $max_disponivel . ".";
        }
        echo '<div class="alert alert-warning" role="alert">' . $mensagem . '</div>';
    }
    ?>
    <?php // NOVO BLOCO: Mensagens para finalização do pedido (COLE TODO ESTE CÓDIGO AQUI!)
    if (isset($_GET['ok']) && $_GET['ok'] == 'pedido_finalizado') {
        echo '<div class="alert alert-success" role="alert">Pedido finalizado com sucesso!</div>';
    }
    if (isset($_GET['erro']) && $_GET['erro'] == 'falha_finalizar_pedido') {
        $msg_erro = htmlspecialchars($_GET['msg'] ?? 'Ocorreu um erro ao finalizar o pedido.');
        echo '<div class="alert alert-danger" role="alert">Erro ao finalizar pedido: ' . $msg_erro . '</div>';
    }
    if (isset($_GET['erro']) && $_GET['erro'] == 'carrinho_vazio') {
        echo '<div class="alert alert-warning" role="alert">Não é possível finalizar um pedido com o carrinho vazio.</div>';
    }
    ?>



    <?php if (empty($carrinho)): ?>
    <div class="alert alert-info" role="alert">
        Seu carrinho está vazio. <a href="/mini-erp/public/index.php?rota=produtos&acao=listar"
            class="alert-link">Voltar para a lista de produtos</a>.
    </div>
    <?php else: ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Variação</th>
                <th>Preço Unit.</th>
                <th>Quantidade</th>
                <th>Subtotal Item</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($carrinho as $chave => $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['nome']) ?></td>
                <td><?= htmlspecialchars($item['variacao'] ?? 'N/A') ?></td>
                <td>R$ <?= number_format($item['preco'], 2, ',', '.') ?></td>
                <td>


                    <form action="/mini-erp/public/index.php?rota=carrinho&acao=atualizarQuantidade" method="POST"
                        class="d-flex align-items-center">
                        <input type="hidden" name="chave_item" value="<?= htmlspecialchars($chave) ?>">
                        <input type="number" name="quantidade" value="<?= (int)$item['quantidade'] ?>" min="1"
                            class="form-control text-center me-2" style="width: 80px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Atualizar</button>
                    </form>
                </td>
                <td>R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></td>
                <td>

                    <form action="/mini-erp/public/index.php?rota=carrinho&acao=remover" method="POST">
                        <input type="hidden" name="chave_item" value="<?= htmlspecialchars($chave) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Remover</button>
                    </form>


                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>


        <tfoot>
            <tr>
                <th colspan="4" class="text-end">Subtotal do Pedido:</th>
                <th colspan="2">R$ <?= number_format($subtotal, 2, ',', '.') ?></th>
            </tr>
            <tr>
                <th colspan="4" class="text-end">Frete:</th>
                <th colspan="2">R$ <?= number_format($frete, 2, ',', '.') ?></th>
            </tr>
            <tr>
                <th colspan="4" class="text-end">Total Geral:</th>
                <th colspan="2">R$ <?= number_format($total_com_frete, 2, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="d-flex justify-content-between">
        <a href="/mini-erp/public/index.php?rota=produtos&acao=listar" class="btn btn-secondary">Continuar Comprando</a>
        <form action="/mini-erp/public/index.php?rota=carrinho&acao=finalizarPedido" method="POST">
            <button type="submit" class="btn btn-success">Finalizar Pedido</button>
        </form>
    </div>

    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>