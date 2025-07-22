<?php
class ItemPedido {
    private $conn;
    private $table = 'itens_pedido'; // Nome da sua tabela de itens de pedido

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para adicionar um item a um pedido específico
    public function criar($pedido_id, $produto_id, $estoque_id, $nome_produto, $variacao_produto, $preco_unitario, $quantidade, $subtotal_item) {
        $query = "INSERT INTO " . $this->table . " (pedido_id, produto_id, estoque_id, nome_produto, variacao_produto, preco_unitario, quantidade, subtotal_item) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        $stmt->bind_param("iiissidd", $pedido_id, $produto_id, $estoque_id, $nome_produto, $variacao_produto, $preco_unitario, $quantidade, $subtotal_item);

        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

public function buscarItensPorPedidoId($pedido_id) {
        $query = "SELECT id, produto_id, estoque_id, nome_produto, variacao_produto, preco_unitario, quantidade, subtotal_item FROM " . $this->table . " WHERE pedido_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $itens = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $itens[] = $row;
            }
        }
        $stmt->close();
        return $itens;
    }


}
?>