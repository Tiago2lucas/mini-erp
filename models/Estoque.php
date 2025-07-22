<?php

class Estoque {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para inserir uma nova variação de estoque
    public function criar($produto_id, $variacao, $quantidade) {
        $query = "INSERT INTO estoque (produto_id, variacao, quantidade) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);

        // 'isi' significa: i=integer (produto_id), s=string (variacao), i=integer (quantidade)
        $stmt->bind_param("isi", $produto_id, $variacao, $quantidade);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
// ... (código existente da classe Estoque, incluindo o construtor e criar) ...

    // Método para buscar todas as variações de estoque de um produto específico
    public function buscarPorProdutoId($produto_id) {
        $estoques = [];
        $query = "SELECT id, variacao, quantidade FROM estoque WHERE produto_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $produto_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $estoques[] = $row;
            }
        }
        $stmt->close();
        return $estoques;
    }

    // Método para atualizar uma variação de estoque existente
    public function atualizar($id, $variacao, $quantidade) {
        $query = "UPDATE estoque SET variacao = ?, quantidade = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sii", $variacao, $quantidade, $id); // 's' string, 'i' integer, 'i' integer
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

 public function buscarPorId($id) {
        $query = "SELECT id, produto_id, variacao, quantidade FROM estoque WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $estoque_data = $result->fetch_assoc(); // Retorna null se não encontrar
        $stmt->close();
        return $estoque_data;
    }

    // Método para deletar uma variação de estoque
    public function deletar($id) {
        $query = "DELETE FROM estoque WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

   



}