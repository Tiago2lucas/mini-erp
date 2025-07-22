<?php

// 1. Inicia a sessão (para o carrinho, etc.)
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (isset($_SESSION['user_timezone'])) {
    date_default_timezone_set($_SESSION['user_timezone']);
}
// 2. Inclui o arquivo de conexão com o banco de dados
require_once dirname(__DIR__) . '/config/conexao.php'; // LINHA 7 // LINHA 7 // __DIR__ garante o caminho correto
require_once dirname(__DIR__) . '/controllers/ProdutoController.php';
require_once dirname(__DIR__) . '/controllers/CarrinhoController.php';
require_once dirname(__DIR__) . '/controllers/PedidoController.php';

// Instancia os controllers
$produtoController = new ProdutoController($conn);
$carrinhoController = new CarrinhoController($conn);
$pedidoController = new PedidoController($conn);



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

case 'carrinho':
        if (method_exists($carrinhoController, $acao)) {
            $carrinhoController->$acao();
        } else {
            echo "Ação não encontrada para o carrinho.";
        }
        break;

case 'pedidos': // NOVO BLOCO DE ROTAS PARA PEDIDOS
    if (method_exists($pedidoController, $acao)) {
        $pedidoController->$acao();
    } else {
        echo "Ação não encontrada para pedidos.";
    }
    break;
    

    default:
        echo "Rota não encontrada.";
        break;
}

// A conexão será fechada automaticamente ao final do script
// Ou você pode fechar explicitamente: $conn->close();

$conn->close();
?>