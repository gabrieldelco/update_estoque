<?php

class EstoqueManager
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function atualizarEstoque($produtos)
    {
        try {
            $this->pdo->beginTransaction();

            //Selecionando e preparando os dados
            foreach ($produtos as $produto) {
                $stmt = $this->pdo->prepare('SELECT id FROM estoque WHERE produto = :produto AND cor = :cor AND tamanho = :tamanho AND deposito = :deposito AND data_disponibilidade = :data_disponibilidade');
                $stmt->execute([
                    ':produto' => $produto['produto'],
                    ':cor' => $produto['cor'],
                    ':tamanho' => $produto['tamanho'],
                    ':deposito' => $produto['deposito'],
                    ':data_disponibilidade' => $produto['data_disponibilidade']
                ]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row) {
                    // Se o registro existir, atualiza a quantidade do produto somando com a quantidade existente
                    $stmt = $this->pdo->prepare('UPDATE estoque SET quantidade = quantidade + :quantidade WHERE id = :id');
                    $stmt->execute([
                        ':quantidade' => $produto['quantidade'],
                        ':id' => $row['id']
                    ]);
                } else {
                    // Se o registro não existir, insere um novo registro
                    $stmt = $this->pdo->prepare('INSERT INTO estoque (produto, cor, tamanho, deposito, data_disponibilidade, quantidade) VALUES (:produto, :cor, :tamanho, :deposito, :data_disponibilidade, :quantidade)');
                    $stmt->execute([
                        ':produto' => $produto['produto'],
                        ':cor' => $produto['cor'],
                        ':tamanho' => $produto['tamanho'],
                        ':deposito' => $produto['deposito'],
                        ':data_disponibilidade' => $produto['data_disponibilidade'],
                        ':quantidade' => $produto['quantidade']
                    ]);
                }
            }

            $this->pdo->commit(); //commita as alterações
            echo "Estoque atualizado com sucesso!";
        } catch (Exception $e) {
            $this->pdo->rollback();
            echo "Erro ao atualizar o estoque: " . $e->getMessage();
        }
    }
}

// Conexão e configuração do banco de dados
$dsn = 'mysql:host=localhost:3306;dbname=gerenciamento_estoque';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //options que permitem o tratamento de erros no PDO

    // Array de JSON fornecido no teste
    $json = '[
        {
            "produto": "10.01.0419",
            "cor": "00",
            "tamanho": "P",
            "deposito": "DEP1",
            "data_disponibilidade": "2023-05-01",
            "quantidade": 15
        },
        {
            "produto": "11.01.0568",
            "cor": "08",
            "tamanho": "P",
            "deposito": "DEP1",
            "data_disponibilidade": "2023-05-01",
            "quantidade": 2
        },
        {
            "produto": "11.01.0568",
            "cor": "08",
            "tamanho": "M",
            "deposito": "DEP1",
            "data_disponibilidade": "2023-05-01",
            "quantidade": 4
        },
        {
            "produto": "11.01.0568",
            "cor": "08",
            "tamanho": "G",
            "deposito": "1",
            "data_disponibilidade": "2023-05-01",
            "quantidade": 6
        },
        {
            "produto": "11.01.0568",
            "cor": "08",
            "tamanho": "P",
            "deposito": "DEP1",
            "data_disponibilidade": "2023-06-01",
            "quantidade": 8
        }
    ]';

    // Transformando o JSON em um array associativo (que pode ser usado em índice)
    $produtos = json_decode($json, true);

    // Instânciando a classe EstoqueManager
    $estoqueManager = new EstoqueManager($pdo);

    // Atualiza o estoque com os produtos do JSON a partir do método atualizarEstoque
    $estoqueManager->atualizarEstoque($produtos);
} catch (PDOException $e) {
    echo "Erro de conexão com o banco de dados: " . $e->getMessage();
}
