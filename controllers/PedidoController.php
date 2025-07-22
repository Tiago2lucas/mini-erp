<?php
// Inclua os models necessários
require_once dirname(__DIR__) . '/models/Pedido.php';
require_once dirname(__DIR__) . '/models/ItemPedido.php';

class PedidoController {
    private $pedidoModel;
    private $itemPedidoModel;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->pedidoModel = new Pedido($conn);
        $this->itemPedidoModel = new ItemPedido($conn);
    }

    // Método para listar todos os pedidos
    public function listar() {
        $pedidos = $this->pedidoModel->buscarTodos();
        // Inclui a view para listar os pedidos
        include dirname(__DIR__) . '/views/pedidos/listar.php';
    }

public function atualizarStatus() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pedido_id']) && isset($_POST['novo_status'])) {
        $pedido_id = (int)$_POST['pedido_id'];
        $novo_status = htmlspecialchars($_POST['novo_status']);

        $atualizado = $this->pedidoModel->atualizarStatus($pedido_id, $novo_status);

        if ($atualizado) {
            header("Location: /mini-erp/public/index.php?rota=pedidos&acao=verDetalhes&id=" . $pedido_id . "&ok=status_atualizado");
            exit;
        } else {
            header("Location: /mini-erp/public/index.php?rota=pedidos&acao=verDetalhes&id=" . $pedido_id . "&erro=falha_atualizar_status");
            exit;
        }
    } else {
        // Requisição inválida ou dados faltando
        header("Location: /mini-erp/public/index.php?rota=pedidos&acao=listar&erro=dados_invalidos");
        exit;
    }
}


    // Método para ver os detalhes de um pedido específico
    public function verDetalhes() {
        $id_pedido = $_GET['id'] ?? null;

        if ($id_pedido && is_numeric($id_pedido)) {
            $pedido = $this->pedidoModel->buscarPorId((int)$id_pedido);
            if ($pedido) {
                $itens_pedido = $this->itemPedidoModel->buscarItensPorPedidoId((int)$id_pedido);
                // Inclui a view para detalhes do pedido
                include dirname(__DIR__) . '/views/pedidos/detalhes.php';
            } else {
                // Pedido não encontrado
                header("Location: /mini-erp/public/index.php?rota=pedidos&acao=listar&erro=pedido_nao_encontrado");
                exit;
            }
        } else {
            // ID de pedido inválido
            header("Location: /mini-erp/public/index.php?rota=pedidos&acao=listar&erro=id_invalido");
            exit;
        }
    }
}
?>