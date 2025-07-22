<?php
// Inclui os Models que este Controller vai usar
;

require_once dirname(__DIR__) . '/models/Produto.php';
require_once dirname(__DIR__) . '/models/Estoque.php';
require_once dirname(__DIR__) . '/models/Pedido.php';       // NOVO
require_once dirname(__DIR__) . '/models/ItemPedido.php';


class CarrinhoController {
  private $produtoModel;
    private $estoqueModel;
    private $pedidoModel;    // NOVO
    private $itemPedidoModel; // NOVO
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->produtoModel = new Produto($conn);
        $this->estoqueModel = new Estoque($conn);
         $this->pedidoModel = new Pedido($conn);          
        $this->itemPedidoModel = new ItemPedido($conn);



        // Inicializa o carrinho na sessão se ele não existir
        if (!isset($_SESSION['carrinho'])) {
            $_SESSION['carrinho'] = [];
        }
    }

public function finalizarPedido() {
    if (empty($_SESSION['carrinho'])) {
        header("Location: /mini-erp/public/index.php?rota=carrinho&acao=ver&erro=carrinho_vazio");
        exit;
    }

    $carrinho = $_SESSION['carrinho'];
    $subtotal = 0;
    foreach ($carrinho as $item) {
        $subtotal += $item['preco'] * $item['quantidade'];
    }

    // Recalcula o frete (garante que está correto no momento da finalização)
    $frete = 0;
    if ($subtotal >= 52 && $subtotal <= 166.59) {
        $frete = 15.00;
    } elseif ($subtotal > 200) {
        $frete = 0.00;
    } else {
        $frete = 20.00;
    }
    $total_com_frete = $subtotal + $frete;

    // Inicia uma transação para garantir a integridade dos dados
    $this->conn->begin_transaction();

    try {
        // 1. Cria o pedido principal
        // INÍCIO DA ALTERAÇÃO: REMOVER GERAÇÃO DE DATA MANUAL
        // A linha abaixo ($data_pedido = date('Y-m-d H:i:s');) SERÁ REMOVIDA.
        // A coluna 'data_pedido' no seu banco de dados (tabela 'pedidos')
        // deve estar configurada como DATETIME ou TIMESTAMP com DEFAULT CURRENT_TIMESTAMP().
        // Dessa forma, o MySQL preencherá automaticamente a data e hora do pedido.
        // FIM DA ALTERAÇÃO: REMOVER GERAÇÃO DE DATA MANUAL

        // INÍCIO DA ALTERAÇÃO: AJUSTAR A CHAMADA AO pedidoModel->criar()
        // O método 'criar' no seu PedidoModel foi ajustado para não receber $data_pedido.
        // Agora, ele espera apenas: subtotal, frete, total_com_frete e status inicial.
        $pedido_id = $this->pedidoModel->criar($subtotal, $frete, $total_com_frete, 'Pendente');
        // FIM DA ALTERAÇÃO: AJUSTAR A CHAMADA AO pedidoModel->criar()

        if (!$pedido_id) {
            throw new Exception("Falha ao criar o pedido principal.");
        }

        // 2. Adiciona os itens do pedido e baixa o estoque
        foreach ($carrinho as $chave_item => $item) {
            // Adiciona o item ao pedido
            $item_adicionado = $this->itemPedidoModel->criar(
                $pedido_id,
                $item['produto_id'],
                $item['estoque_id'],
                $item['nome'],
                $item['variacao'],
                $item['preco'],
                $item['quantidade'],
                $item['preco'] * $item['quantidade']
            );

            if (!$item_adicionado) {
                throw new Exception("Falha ao adicionar item ao pedido.");
            }

            // Baixa o estoque
            if ($item['estoque_id']) {
                $estoque_atual_data = $this->estoqueModel->buscarPorId($item['estoque_id']);
                if (!$estoque_atual_data || $estoque_atual_data['quantidade'] < $item['quantidade']) {
                    throw new Exception("Estoque insuficiente para o item: " . $item['nome'] . " " . $item['variacao']);
                }
                $nova_quantidade_estoque = $estoque_atual_data['quantidade'] - $item['quantidade'];
                $estoque_baixado = $this->estoqueModel->atualizar(
                    $item['estoque_id'],
                    $estoque_atual_data['variacao'], // Mantém a variação original
                    $nova_quantidade_estoque
                );

                if (!$estoque_baixado) {
                    throw new Exception("Falha ao baixar estoque para o item: " . $item['nome'] . " " . $item['variacao']);
                }
            }
            // Se o produto não tem estoque_id (como um produto "padrão" sem variações), você precisaria de uma lógica diferente aqui.
            // No nosso modelo atual, todo estoque é gerenciado via `estoque_id`.
        }

        // Se tudo deu certo, commita a transação
        $this->conn->commit();
        
        // Limpa o carrinho da sessão
        unset($_SESSION['carrinho']);

        // Redireciona com mensagem de sucesso
        header("Location: /mini-erp/public/index.php?rota=carrinho&acao=ver&ok=pedido_finalizado");
        exit;

    } catch (Exception $e) {
        // Se algo deu errado, faz rollback da transação
        $this->conn->rollback();
        // Redireciona com mensagem de erro
        header("Location: /mini-erp/public/index.php?rota=carrinho&acao=ver&erro=falha_finalizar_pedido&msg=" . urlencode($e->getMessage()));
        exit;
    }
}

    // Método para adicionar um produto ao carrinho
    public function adicionar() {
        $id_produto = $_GET['id'] ?? null;
        $id_estoque = $_GET['estoque_id'] ?? null;
        $quantidade_adicionar = 1;

        if ($id_produto && is_numeric($id_produto)) {
            $produto_data = $this->produtoModel->buscarPorId((int)$id_produto);

            if ($produto_data) {
                $item_adicionado = false;
                $chave_item_carrinho = $id_produto;

                if ($id_estoque && is_numeric($id_estoque)) {
                    $variacao_encontrada = null;
                    foreach ($produto_data['estoques'] as $est) {
                        if ($est['id'] == (int)$id_estoque) {
                            $variacao_encontrada = $est;
                            break;
                        }
                    }

                    if ($variacao_encontrada) {
                        $chave_item_carrinho = $id_produto . '_' . $id_estoque;
                        
                        $quantidade_no_carrinho = $_SESSION['carrinho'][$chave_item_carrinho]['quantidade'] ?? 0;
                        $estoque_disponivel = $variacao_encontrada['quantidade'];

                        // **VERIFICAÇÃO DE ESTOQUE AQUI**
                        if (($quantidade_no_carrinho + $quantidade_adicionar) <= $estoque_disponivel) {
                            if (isset($_SESSION['carrinho'][$chave_item_carrinho])) {
                                $_SESSION['carrinho'][$chave_item_carrinho]['quantidade'] += $quantidade_adicionar;
                            } else {
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
                        } else {
                            // Redireciona com erro de estoque insuficiente
                            header("Location: /mini-erp/public/index.php?rota=carrinho&acao=ver&erro_estoque=insuficiente&item=" . urlencode($produto_data['nome'] . ' (' . $variacao_encontrada['variacao'] . ')'));
                            exit;
                        }
                    }
                } else {
                    // Lógica para produtos sem variações (ou variação 'Padrão')
                    // Primeiro, busca a quantidade em estoque para o produto principal/padrão
                    $estoque_produto_principal = 0;
                    if (!empty($produto_data['estoques'])) {
                        // Se houver variações, para o botão 'comprar' do produto base, pegaremos o estoque da primeira variação ou você pode definir uma lógica de qual variação é a 'padrão'.
                        // Por simplicidade, vamos usar o estoque da primeira variação ou 0 se não houver.
                        $estoque_produto_principal = $produto_data['estoques'][0]['quantidade'] ?? 0;
                        $chave_item_carrinho = $id_produto . '_' . ($produto_data['estoques'][0]['id'] ?? 'padrao'); // Adapta a chave para ser consistente
                        $id_estoque_real = $produto_data['estoques'][0]['id'] ?? null;
                        $variacao_nome = $produto_data['estoques'][0]['variacao'] ?? 'Padrão';

                    } else {
                        // Se não há variações, assumimos que o estoque está no próprio produto ou não é gerenciado por variações.
                        // Para este caso, como sua tabela `produtos` não tem campo `quantidade`, assumimos 0 ou você pode adicionar um campo `quantidade_padrao` na tabela `produtos`.
                        // Por agora, vamos considerar que produtos sem estoque_id específico (e sem variações em `estoques` array) têm 0 estoque.
                        $estoque_produto_principal = 0; // Ajuste se seu modelo de dados tiver estoque no produto principal sem variação
                        $chave_item_carrinho = $id_produto . '_padrao';
                        $variacao_nome = 'Padrão';
                        $id_estoque_real = null;
                    }


                    $quantidade_no_carrinho = $_SESSION['carrinho'][$chave_item_carrinho]['quantidade'] ?? 0;

                    if (($quantidade_no_carrinho + $quantidade_adicionar) <= $estoque_produto_principal) {
                        if (isset($_SESSION['carrinho'][$chave_item_carrinho])) {
                            $_SESSION['carrinho'][$chave_item_carrinho]['quantidade'] += $quantidade_adicionar;
                        } else {
                            $_SESSION['carrinho'][$chave_item_carrinho] = [
                                'produto_id' => $id_produto,
                                'estoque_id' => $id_estoque_real, // ID do estoque real
                                'nome' => $produto_data['nome'],
                                'preco' => $produto_data['preco'],
                                'variacao' => $variacao_nome,
                                'quantidade' => $quantidade_adicionar
                            ];
                        }
                        $item_adicionado = true;
                    } else {
                         header("Location: /mini-erp/public/index.php?rota=carrinho&acao=ver&erro_estoque=insuficiente&item=" . urlencode($produto_data['nome'] . ' (' . $variacao_nome . ')'));
                        exit;
                    }
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
    
public function atualizarQuantidade() {
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $chave_item = $_POST['chave_item'] ?? null;
            $nova_quantidade = $_POST['quantidade'] ?? null;

            if ($chave_item && is_numeric($nova_quantidade) && (int)$nova_quantidade > 0) {
                $nova_quantidade = (int)$nova_quantidade;

                if (isset($_SESSION['carrinho'][$chave_item])) {
                    $item_no_carrinho = $_SESSION['carrinho'][$chave_item];
                    $produto_id = $item_no_carrinho['produto_id'];
                    $estoque_id = $item_no_carrinho['estoque_id'];

                    $estoque_disponivel = 0;
                    if ($estoque_id) {
                        // Busca a variação específica para pegar o estoque real
                        $variacao_estoque = $this->estoqueModel->buscarPorId($estoque_id); // Precisamos de um novo método buscarPorId no EstoqueModel
                        if ($variacao_estoque) {
                            $estoque_disponivel = $variacao_estoque['quantidade'];
                        }
                    } else {
                        // Se não tem estoque_id (produto 'Padrão' sem variação explícita)
                        // Lógica similar à do adicionar(): pegar do primeiro estoque ou tratar como 0 se não houver.
                        $produto_data_completa = $this->produtoModel->buscarPorId($produto_id);
                        if (!empty($produto_data_completa['estoques'])) {
                            $estoque_disponivel = $produto_data_completa['estoques'][0]['quantidade'] ?? 0;
                        }
                    }

                    if ($nova_quantidade <= $estoque_disponivel) {
                        $_SESSION['carrinho'][$chave_item]['quantidade'] = $nova_quantidade;
                    } else {
                        // Redireciona com erro de estoque insuficiente
                        $item_nome = $item_no_carrinho['nome'] . ' (' . ($item_no_carrinho['variacao'] ?? 'Padrão') . ')';
                        header("Location: /mini-erp/public/index.php?rota=carrinho&acao=ver&erro_estoque=max_atingido&item=" . urlencode($item_nome) . "&max=" . $estoque_disponivel);
                        exit;
                    }
                }
            }
        }
        header("Location: /mini-erp/public/index.php?rota=carrinho&acao=ver");
        exit;
    }
 public function remover() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $chave_item = $_POST['chave_item'] ?? null;

            if ($chave_item && isset($_SESSION['carrinho'][$chave_item])) {
                unset($_SESSION['carrinho'][$chave_item]);
            }
        }
        header("Location: /mini-erp/public/index.php?rota=carrinho&acao=ver");
        exit;
    }


}
?>