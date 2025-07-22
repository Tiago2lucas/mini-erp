<?php
class Pedido {
    private $conn;
    private $table = 'pedidos'; // Nome da sua tabela de pedidos

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para criar um novo pedido no banco de dados
   public function criar($subtotal, $frete, $total, $status_inicial = 'Pendente') { 
        // A query SQL agora NÃO inclui 'data_pedido' na lista de colunas ou nos VALUES.
        // A coluna 'data_pedido' (configurada com CURRENT_TIMESTAMP() no banco)
        // será preenchida automaticamente pelo MySQL.
        $query = "INSERT INTO " . $this->table . " (subtotal, frete, total, status) VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);

        // A string de tipos agora tem 4 caracteres ('ddds') para os 4 parâmetros restantes.
        // d = double (para subtotal, frete, total)
        // s = string (para status_inicial)
        $stmt->bind_param("ddds", $subtotal, $frete, $total, $status_inicial);

        $success = $stmt->execute();
        $inserted_id = $this->conn->insert_id;
        $stmt->close();

        if ($success) {
            return $inserted_id;
        }
        return false;
    }
public function atualizarStatus($pedido_id, $novo_status) {
    $query = "UPDATE " . $this->table . " SET status = ? WHERE id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("si", $novo_status, $pedido_id); // 's' para string (status), 'i' para int (id)
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}



 // NOVO MÉTODO: Buscar todos os pedidos
    public function buscarTodos() {
        $query = "SELECT id, subtotal, frete, total, data_pedido FROM " . $this->table . " ORDER BY data_pedido DESC";
        $result = $this->conn->query($query);
        $pedidos = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $pedidos[] = $row;
            }
        }
        return $pedidos;
    }

    // NOVO MÉTODO: Buscar um pedido pelo ID
    public function buscarPorId($id) {
        $query = "SELECT id, subtotal, frete, total, data_pedido FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedido = $result->fetch_assoc();
        $stmt->close();
        return $pedido;
    }



}
?>