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

    <?php if (empty($carrinho)): ?>
        <div class="alert alert-info" role="alert">
            Seu carrinho está vazio. <a href="/mini-erp/public/index.php?rota=produtos&acao=listar" class="alert-link">Voltar para a lista de produtos</a>.
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
                            <?= (int)$item['quantidade'] ?>
                        </td>
                        <td>R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></td>
                        <td>
                            <a href="#" class="btn btn-danger btn-sm">Remover</a>
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
            <button type="button" class="btn btn-success">Finalizar Pedido</button> </div>

    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>