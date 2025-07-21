<?php

class Produto {
    private $conn;

    // O construtor recebe a conexão com o banco de dados
    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para inserir um novo produto
    public function criar($nome, $preco) {
        $query = "INSERT INTO produtos (nome, preco) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);

        // 'sd' significa: s=string, d=double (para o preço)
        $stmt->bind_param("sd", $nome, $preco);

        if ($stmt->execute()) {
            // Retorna o ID do produto recém-criado
            return $stmt->insert_id;
        }
        return false;
    }

 // Método para buscar um produto específico pelo ID, incluindo suas variações
    public function buscarPorId($id) {
        $produtos = []; // Será um array associativo para o produto único

        // Primeiro, busca os dados básicos do produto
        $query_produto = "SELECT id, nome, preco FROM produtos WHERE id = ?";
        $stmt_produto = $this->conn->prepare($query_produto);
        $stmt_produto->bind_param("i", $id); // 'i' para integer
        $stmt_produto->execute();
        $result_produto = $stmt_produto->get_result();

        if ($result_produto->num_rows > 0) {
            $produto_data = $result_produto->fetch_assoc();
            $produtos = [
                'id' => $produto_data['id'],
                'nome' => $produto_data['nome'],
                'preco' => $produto_data['preco'],
                'estoques' => []
            ];

            // Em seguida, busca todas as variações de estoque para este produto
            $query_estoque = "SELECT id, variacao, quantidade FROM estoque WHERE produto_id = ?";
            $stmt_estoque = $this->conn->prepare($query_estoque);
            $stmt_estoque->bind_param("i", $id);
            $stmt_estoque->execute();
            $result_estoque = $stmt_estoque->get_result();

            if ($result_estoque->num_rows > 0) {
                while ($row_estoque = $result_estoque->fetch_assoc()) {
                    $produtos['estoques'][] = [
                        'id' => $row_estoque['id'], // ID da variação do estoque
                        'variacao' => $row_estoque['variacao'],
                        'quantidade' => (int)$row_estoque['quantidade']
                    ];
                }
            }
        }
        $stmt_produto->close();
        $stmt_estoque->close();
        return $produtos; // Retorna o produto completo com suas variações
    }


    // ... (código existente da classe Produto) ...

    // Método para deletar um produto pelo ID
    public function deletar($id) {
        $query = "DELETE FROM produtos WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id); // 'i' para integer
        $success = $stmt->execute();
        $stmt->close();
        return $success; // Retorna true em caso de sucesso, false em caso de falha
    }

    // ... (o restante do código da classe Produto) ...

    // Método para atualizar um produto existente
    public function atualizar($id, $nome, $preco) {
        $query = "UPDATE produtos SET nome = ?, preco = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sdi", $nome, $preco, $id); // 's' string, 'd' double (decimal), 'i' integer
        $success = $stmt->execute();
        $stmt->close();
        return $success; // Retorna true em caso de sucesso, false em caso de falha
    }

    // ... (o restante do código da classe Produto) ...


    // Método para buscar todos os produtos (com suas variações)
    public function buscarTodos() {
        // JOIN com a tabela estoque para buscar variações
        $query = "SELECT p.id, p.nome, p.preco, e.variacao, e.quantidade 
                  FROM produtos p 
                  LEFT JOIN estoque e ON e.produto_id = p.id 
                  ORDER BY p.id DESC";

        $result = $this->conn->query($query);
        $produtos = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $id = $row['id'];
                if (!isset($produtos[$id])) {
                    $produtos[$id] = [
                        'id' => $id,
                        'nome' => $row['nome'],
                        'preco' => $row['preco'],
                        'estoques' => [] // Array para armazenar as variações de estoque
                    ];
                }
                // Adiciona a variação e quantidade se existirem
                if ($row['variacao'] !== null) {
                    $produtos[$id]['estoques'][] = [
                        'variacao' => $row['variacao'],
                        'quantidade' => (int)$row['quantidade']
                    ];
                }
            }
        }
        return $produtos;
    }

    // Futuramente, teremos métodos para atualizar, deletar, buscar por ID, etc.
}