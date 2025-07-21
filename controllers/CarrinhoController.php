<?php
// Inclui os Models que este Controller vai usar
require_once dirname(__DIR__) . '/models/Produto.php';
require_once dirname(__DIR__) . '/models/Estoque.php';

class CarrinhoController {
    private $produtoModel;
    private $estoqueModel;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->produtoModel = new Produto($conn);
        $this->estoqueModel = new Estoque($conn);

        // Inicializa o carrinho na sessão se ele não existir
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }
    }

    // Método para adicionar um produto ao carrinho
    public function adicionar() {
        $id_produto = $_GET['id'] ?? null;
        $id_estoque = $_GET['estoque_id'] ?? null;
        $quantidade_adicionar = 1; // Por enquanto, sempre adiciona 1 unidade por clique

        if ($id_produto && is_numeric($id_produto)) {
            $produto_data = $this->produtoModel->buscarPorId((int)$id_produto);

            if ($produto_data) {
                $item_adicionado = false;
                $chave_item_carrinho = $id_produto; // Chave base para o produto

                // Se houver id_estoque, é uma variação específica
                if ($id_estoque && is_numeric($id_estoque)) {
                    // Encontra a variação correta
                    $variacao_encontrada = null;
                    foreach ($produto_data['estoques'] as $est) {
                        if ($est['id'] == (int)$id_estoque) {
                            $variacao_encontrada = $est;
                            break;
                        }
                    }

                    if ($variacao_encontrada) {
                        $chave_item_carrinho = $id_produto . '_' . $id_estoque; // Chave única para variação
                        
                        if (isset($_SESSION['carrinho'][$chave_item_carrinho])) {
                            // Se já existe no carrinho, incrementa a quantidade
                            $_SESSION['carrinho'][$chave_item_carrinho]['quantidade'] += $quantidade_adicionar;
                        } else {
                            // Adiciona o item (com variação) ao carrinho
                            $_SESSION['carrinho'][$chave_item_carrinho] = [
                                'produto_id' => $id_produto,
                                'estoque_id' => (int)$id_estoque,
                                'nome' => $produto_data['nome'],
                                'preco' => $produto_data['preco'],
                                'variacao' => $variacao_encontrada['variacao'],
                                'quantidade' => $quantidade_adicionar
                            ];
                        }
                        $item_adicionado = true;
                    }
                } else {
                    // Produto sem variações ou comprando o "produto principal" sem especificar variação
                    // Neste caso, para simplificar, se não há estoque_id, consideramos a compra do produto 'base'
                    // ou o primeiro estoque se houver apenas um (pode ser ajustado para ser mais rigoroso)
                    if (isset($_SESSION['carrinho'][$chave_item_carrinho])) {
                        $_SESSION['carrinho'][$chave_item_carrinho]['quantidade'] += $quantidade_adicionar;
                    } else {
                        // Se não tem variação, adiciona o produto base
                        $_SESSION['carrinho'][$chave_item_carrinho] = [
                            'produto_id' => $id_produto,
                            'estoque_id' => null, // Não tem estoque_id específico
                            'nome' => $produto_data['nome'],
                            'preco' => $produto_data['preco'],
                            'variacao' => 'Padrão', // Ou vazio, conforme sua preferência
                            'quantidade' => $quantidade_adicionar
                        ];
                    }
                    $item_adicionado = true;
                }
            }
        }

        // Redireciona para a página do carrinho (que vamos criar)
        header("Location: /mini-erp/public/index.php?rota=carrinho&acao=ver");
        exit;
    }
    
    // Método para exibir o conteúdo do carrinho
    public function ver() {
        // A lógica para calcular total e frete virá aqui
        $carrinho = $_SESSION['carrinho'] ?? []; // Pega o carrinho da sessão
        $subtotal = 0;
        foreach ($carrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }

        // Simples cálculo de frete (será melhorado depois)
        $frete = 0;
        if ($subtotal >= 52 && $subtotal <= 166.59) {
            $frete = 15.00;
        } elseif ($subtotal > 200) {
            $frete = 0.00; // Frete grátis
        } else {
            $frete = 20.00;
        }
        $total_com_frete = $subtotal + $frete;

        require_once dirname(__DIR__) . '/views/carrinho.php';
    }

    // Métodos futuros: remover, atualizarQuantidade, finalizarPedido, etc.
}
?>