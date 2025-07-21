<?php


require_once dirname(__DIR__) . '/models/Produto.php';   // LINHA 4
require_once dirname(__DIR__) . '/models/Estoque.php';  

class ProdutoController {
    private $produtoModel;
    private $estoqueModel;

    // O construtor recebe a conexão com o banco de dados
    // e inicializa as instâncias dos Models
    public function __construct($conn) {
        $this->produtoModel = new Produto($conn);
        $this->estoqueModel = new Estoque($conn);
    }

    // Método para exibir a tela de cadastro/listagem de produtos
    public function listar() {
        // Chama o Model para buscar todos os produtos
        $produtos = $this->produtoModel->buscarTodos();

        // Inclui a View que exibirá os produtos e o formulário
        // A View agora receberá a variável $produtos que o Controller preparou
      // Inclui a View que exibirá os produtos e o formulário
            require_once dirname(__DIR__) . '/views/produtos.php'; // LINHA 25
    }




    

    // Método para carregar um produto para edição
 // Método para carregar um produto para edição
    public function editar() {
        $id = $_GET['id'] ?? null;
        $produto_para_edicao = null;
        
        // Esta linha busca TODOS os produtos para a listagem na View
        $produtos = $this->produtoModel->buscarTodos(); 

        if ($id && is_numeric($id)) {
            $produto_para_edicao = $this->produtoModel->buscarPorId((int)$id);
        }

        // Inclui a View, passando o produto para edição E a lista de produtos existentes
        require_once dirname(__DIR__) . '/views/produtos.php';
    }




    // Método para processar o cadastro de um novo produto
    public function salvar() {
        // Verifica se a requisição é POST (envio do formulário)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome'] ?? '');
            $preco = trim($_POST['preco'] ?? '');
            $variacoes = $_POST['variacao'] ?? [];
            $quantidades = $_POST['quantidade'] ?? [];

            // Validação básica
            if (empty($nome) || empty($preco) || !is_numeric($preco)) {
                // Podemos adicionar uma mensagem de erro aqui no futuro
                // Por enquanto, apenas redireciona de volta
                header("Location: /mini-erp/public/index.php?rota=produtos&acao=listar&erro=dados_invalidos");
                exit;
            }

            // Salva o produto usando o Produto Model
            $id_produto = $this->produtoModel->criar($nome, (float)$preco);

            if ($id_produto) {
                // Salva as variações de estoque usando o Estoque Model
                foreach ($variacoes as $i => $var) {
                    $var = trim($var);
                    $qtd = isset($quantidades[$i]) ? (int)$quantidades[$i] : 0;

                    if ($var !== '' && $qtd >= 0) {
                        $this->estoqueModel->criar($id_produto, $var, $qtd);
                    }
                }
                // Redireciona de volta para a tela de listagem com sucesso
                header("Location: /mini-erp/public/index.php?rota=produtos&acao=listar&ok=1");
                exit;
            } else {
                // Em caso de falha ao salvar o produto
                header("Location: /mini-erp/public/index.php?rota=produtos&acao=listar&erro=falha_cadastro");
                exit;
            }
        } else {
            // Se não for POST, redireciona para a listagem
            header("Location: /mini-erp/public/index.php?rota=produtos&acao=listar");
            exit;
        }
    }

public function atualizar() {
        // Verifica se a requisição é POST (envio do formulário)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = trim($_POST['id'] ?? '');
            $nome = trim($_POST['nome'] ?? '');
            $preco = trim($_POST['preco'] ?? '');
            $variacoes = $_POST['variacao'] ?? [];
            $quantidades = $_POST['quantidade'] ?? [];
            $estoque_ids = $_POST['estoque_id'] ?? []; // IDs das variações existentes

            // Validação básica
            if (empty($id) || !is_numeric($id) || empty($nome) || empty($preco) || !is_numeric($preco)) {
                header("Location: /mini-erp/public/index.php?rota=produtos&acao=editar&id=" . urlencode($id) . "&erro=dados_invalidos");
                exit;
            }

            $id_produto = (int)$id;

            // 1. Atualiza os dados principais do produto
            $produto_atualizado = $this->produtoModel->atualizar($id_produto, $nome, (float)$preco);

            if ($produto_atualizado) {
                // 2. Processa as variações de estoque
                $variacoes_existentes_no_banco = $this->estoqueModel->buscarPorProdutoId($id_produto);
                $ids_existentes_no_banco = array_column($variacoes_existentes_no_banco, 'id');
                $ids_enviados_no_form = array_filter($estoque_ids); // Filtra vazios (novas variações)

                $variacoes_a_manter = [];

                foreach ($variacoes as $i => $var) {
                    $var = trim($var);
                    $qtd = isset($quantidades[$i]) ? (int)$quantidades[$i] : 0;
                    $estoque_id = isset($estoque_ids[$i]) && is_numeric($estoque_ids[$i]) ? (int)$estoque_ids[$i] : null;

                    if ($var !== '' && $qtd >= 0) {
                        if ($estoque_id) {
                            // Atualiza variação existente
                            $this->estoqueModel->atualizar($estoque_id, $var, $qtd);
                            $variacoes_a_manter[] = $estoque_id;
                        } else {
                            // Cria nova variação
                            $this->estoqueModel->criar($id_produto, $var, $qtd);
                        }
                    }
                }

                // 3. Deleta variações que foram removidas do formulário
                $ids_para_deletar = array_diff($ids_existentes_no_banco, $variacoes_a_manter);
                foreach ($ids_para_deletar as $id_variacao_deletar) {
                    $this->estoqueModel->deletar($id_variacao_deletar);
                }

                // Redireciona de volta para a tela de listagem com sucesso
                header("Location: /mini-erp/public/index.php?rota=produtos&acao=listar&ok=2"); // ok=2 para atualização
                exit;
            } else {
                // Em caso de falha ao atualizar o produto principal
                header("Location: /mini-erp/public/index.php?rota=produtos&acao=editar&id=" . urlencode($id) . "&erro=falha_atualizacao");
                exit;
            }
        } else {
            // Se não for POST, redireciona para a listagem
            header("Location: /mini-erp/public/index.php?rota=produtos&acao=listar");
            exit;
        }
    }
// ... (código existente da classe ProdutoController, incluindo listar, salvar, editar, atualizar) ...

    // Método para processar a exclusão de um produto
    public function excluir() {
        $id = $_GET['id'] ?? null;

        if ($id && is_numeric($id)) {
            $id_produto = (int)$id;

            // Chama o método deletar do ProdutoModel
            $deletado = $this->produtoModel->deletar($id_produto);

            if ($deletado) {
                // Redireciona de volta para a tela de listagem com sucesso
                header("Location: /mini-erp/public/index.php?rota=produtos&acao=listar&ok=3"); // ok=3 para exclusão
                exit;
            } else {
                // Em caso de falha ao deletar
                header("Location: /mini-erp/public/index.php?rota=produtos&acao=listar&erro=falha_exclusao");
                exit;
            }
        } else {
            // Se o ID não for válido, redireciona para a listagem
            header("Location: /mini-erp/public/index.php?rota=produtos&acao=listar");
            exit;
        }
    }



}