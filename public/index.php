<?php

// 1. Inicia a sessão (para o carrinho, etc.)
session_start();

// 2. Inclui o arquivo de conexão com o banco de dados
require_once dirname(__DIR__) . '/config/conexao.php'; // LINHA 7 // LINHA 7 // __DIR__ garante o caminho correto

// 3. Define a rota padrão e a ação padrão
$rota = $_GET['rota'] ?? 'produtos'; // Se não houver rota, vai para 'produtos'
$acao = $_GET['acao'] ?? 'listar';   // Se não houver ação, vai para 'listar'

// 4. Mapeamento das rotas para os Controllers
switch ($rota) {
    case 'produtos':
        // Inclui o ProdutoController
        require_once __DIR__ . '/../controllers/ProdutoController.php';
        $controller = new ProdutoController($conn); // Passa a conexão para o Controller

        // Chama a ação correspondente no Controller
        if (method_exists($controller, $acao)) {
            $controller->$acao();
        } else {
            echo "Ação não encontrada para produtos.";
        }
        break;

    // Futuras rotas virão aqui: 'pedidos', 'cupons', etc.
    /*
    case 'pedidos':
        require_once __DIR__ . '/../controllers/PedidoController.php';
        $controller = new PedidoController($conn);
        if (method_exists($controller, $acao)) {
            $controller->$acao();
        } else {
            echo "Ação não encontrada para pedidos.";
        }
        break;
    */

    default:
        echo "Rota não encontrada.";
        break;
}

// A conexão será fechada automaticamente ao final do script
// Ou você pode fechar explicitamente: $conn->close();

?>