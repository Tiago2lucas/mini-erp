<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Pedido #<?= htmlspecialchars($pedido['id']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
    body {
        background-color: #212529;
        /* Cor de fundo escura */
        color: #f8f9fa;
        /* Texto claro */
    }

    .container {
        margin-top: 50px;
        background-color: #343a40;
        /* Fundo do container um pouco mais claro */
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
    }

    .table {
        color: #f8f9fa;
        /* Texto da tabela claro */
    }

    .table thead th {
        border-bottom: 2px solid #6c757d;
        /* Borda dos cabeçalhos */
    }

    .btn-primary,
    .btn-secondary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover,
    .btn-secondary:hover {
        background-color: #0056b3;
        border-color: #004085;
    }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mb-4">Detalhes do Pedido #<?= htmlspecialchars($pedido['id']) ?></h2>

        <?php // NOVO BLOCO: Mensagens de feedback para atualização de status
if (isset($_GET['ok']) && $_GET['ok'] == 'status_atualizado') {
    echo '<div class="alert alert-success" role="alert">Status do pedido atualizado com sucesso!</div>';
}
if (isset($_GET['erro']) && $_GET['erro'] == 'falha_atualizar_status') {
    echo '<div class="alert alert-danger" role="alert">Erro ao atualizar o status do pedido.</div>';
}
if (isset($_GET['erro']) && $_GET['erro'] == 'dados_invalidos') {
    echo '<div class="alert alert-danger" role="alert">Dados inválidos para atualização de status.</div>';
}
?>





        <?php if (!empty($pedido)): ?>
        <div class="card bg-dark text-white mb-4">


            <div class="card-body">
                <h5 class="card-title">Informações do Pedido</h5>
                <p class="card-text"><strong>Data:</strong>
                    <?= htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['data_pedido']))) ?></p>
                <p class="card-text"><strong>Subtotal:</strong> R$
                    <?= number_format($pedido['subtotal'], 2, ',', '.') ?></p>
                <p class="card-text"><strong>Frete:</strong> R$ <?= number_format($pedido['frete'], 2, ',', '.') ?></p>
                <p class="card-text"><strong>Total Geral:</strong> R$
                    <?= number_format($pedido['total'], 2, ',', '.') ?></p>

                <hr>
                <form action="/mini-erp/public/index.php?rota=pedidos&acao=atualizarStatus" method="POST">
                    <input type="hidden" name="pedido_id" value="<?= htmlspecialchars($pedido['id']) ?>">
                    <div class="mb-3">
                        <label for="statusPedido" class="form-label">Status do Pedido:</label>
                        <select class="form-select bg-secondary text-white border-secondary" id="statusPedido"
                            name="novo_status" onchange="this.form.submit()">
                            <?php
                    $status_options = ['Pendente', 'Processando', 'Enviado', 'Entregue', 'Cancelado'];
                    foreach ($status_options as $option) {
                        $selected = ($pedido['status'] == $option) ? 'selected' : '';
                        echo "<option value=\"$option\" $selected>$option</option>";
                    }
                    ?>
                        </select>
                    </div>
                </form>
            </div>


        </div>

        <h4 class="mt-4 mb-3">Itens do Pedido</h4>
        <?php if (!empty($itens_pedido)): ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Variação</th>
                    <th>Preço Unitário</th>
                    <th>Quantidade</th>
                    <th>Subtotal Item</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens_pedido as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nome_produto']) ?></td>
                    <td><?= htmlspecialchars($item['variacao_produto'] ?? 'N/A') ?></td>
                    <td>R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                    <td><?= (int)$item['quantidade'] ?></td>
                    <td>R$ <?= number_format($item['subtotal_item'], 2, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="alert alert-warning" role="alert">
            Nenhum item encontrado para este pedido.
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="alert alert-danger" role="alert">
            Pedido não encontrado.
        </div>
        <?php endif; ?>

        <a href="/mini-erp/public/index.php?rota=pedidos&acao=listar" class="btn btn-secondary mt-3">Voltar para a
            Listagem de Pedidos</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>