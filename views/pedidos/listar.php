<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listagem de Pedidos</title>
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

    .alert {
        margin-top: 20px;
    }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="mb-4">Listagem de Pedidos</h2>

        <?php
        // Mensagens de feedback
        if (isset($_GET['erro']) && $_GET['erro'] == 'pedido_nao_encontrado') {
            echo '<div class="alert alert-danger" role="alert">Pedido não encontrado.</div>';
        }
        if (isset($_GET['erro']) && $_GET['erro'] == 'id_invalido') {
            echo '<div class="alert alert-danger" role="alert">ID de pedido inválido.</div>';
        }
        ?>

        <?php if (empty($pedidos)): ?>
        <div class="alert alert-info" role="alert">
            Nenhum pedido encontrado.
        </div>
        <?php else: ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID do Pedido</th>
                    <th>Data do Pedido</th>
                    <th>Subtotal</th>
                    <th>Frete</th>
                    <th>Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td><?= htmlspecialchars($pedido['id']) ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['data_pedido']))) ?></td>
                    <td>R$ <?= number_format($pedido['subtotal'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($pedido['frete'], 2, ',', '.') ?></td>
                    <td>R$ <?= number_format($pedido['total'], 2, ',', '.') ?></td>
                    <td>
                        <a href="/mini-erp/public/index.php?rota=pedidos&acao=verDetalhes&id=<?= htmlspecialchars($pedido['id']) ?>"
                            class="btn btn-info btn-sm">Ver Detalhes</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <a href="/mini-erp/public/index.php?rota=produtos&acao=listar" class="btn btn-secondary mt-3">Voltar para
            Produtos</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>