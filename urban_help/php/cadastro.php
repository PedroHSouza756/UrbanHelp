<?php
// Dados de conexão com o MySQL
$servername = "localhost"; // Nome do servidor
$username = "root"; // Nome de usuário do MySQL
$password = ""; // Senha do MySQL

// Cria a conexão com o MySQL para a criação do banco de dados
$conn = new mysqli($servername, $username, $password);

// Verifica se ocorreu algum erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão com o servidor MySQL: " . $conn->connect_error);
}

// Cria o banco de dados se não existir
$databaseName = "urban";
$createDatabaseSQL = "CREATE DATABASE IF NOT EXISTS $databaseName";

if ($conn->query($createDatabaseSQL) === TRUE) {
    // Removido o texto de confirmação
} else {
    die("Erro ao criar o banco de dados: " . $conn->error);
}

$conn->close();

// Conecta-se ao banco de dados recém-criado
$conn = new mysqli($servername, $username, $password, $databaseName);

// Verifica se ocorreu algum erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Consulta SQL para criar a tabela 'cliente' se não existir
$createClienteTableSQL = "CREATE TABLE IF NOT EXISTS cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    senha VARCHAR(255) NOT NULL
)";

if ($conn->query($createClienteTableSQL) === TRUE) {
    // Removido o texto de confirmação
} else {
    die("Erro ao criar a tabela 'cliente': " . $conn->error);
}

// Consulta SQL para criar a tabela 'funcionario' se não existir
$createFuncionarioTableSQL = "CREATE TABLE IF NOT EXISTS funcionario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    cnpj VARCHAR(18) NOT NULL,
    telefoneEmpresa VARCHAR(20),
    servico VARCHAR(255),
    senha VARCHAR(255) NOT NULL
)";

if ($conn->query($createFuncionarioTableSQL) === TRUE) {
    // Removido o texto de confirmação
} else {
    die("Erro ao criar a tabela 'funcionario': " . $conn->error);
}

// Função para formatar CPF (inserir máscara)
function formatCPF($cpf) {
    $cpf = preg_replace("/[^0-9]/", "", $cpf);
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

// Função para formatar CNPJ (inserir máscara)
function formatCNPJ($cnpj) {
    $cnpj = preg_replace("/[^0-9]/", "", $cnpj);
    return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12, 2);
}

// Obtenha os valores do formulário
$nome = $_POST['nome'];
$email = $_POST['email'];
$documento = $_POST['documento'];
$senha = $_POST['senha'];

// Define um array associativo com os campos específicos de cada tipo de documento
$documentos = [
    'cpf' => [
        'campo' => 'cpf',
        'tabela' => 'cliente',
        'campos' => ['nome', 'email', 'cpf', 'senha'],
        'funcao_format' => 'formatCPF'
    ],
    'cnpj' => [
        'campo' => 'cnpj',
        'tabela' => 'funcionario',
        'campos' => ['nome', 'email', 'cnpj', 'telefoneEmpresa', 'servico', 'senha'],
        'funcao_format' => 'formatCNPJ'
    ]
];

// Verifica se o tipo de documento é válido
if (isset($documentos[$documento])) {
    $documentoConfig = $documentos[$documento];

    // Formata o documento
    $campoDocumento = call_user_func($documentoConfig['funcao_format'], $_POST[$documentoConfig['campo']]);
    $hashedSenha = password_hash($senha, PASSWORD_DEFAULT);

    // Verifica se o CPF ou CNPJ já existe na tabela
    $checkQuery = "SELECT id FROM {$documentoConfig['tabela']} WHERE {$documentoConfig['campo']} = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param('s', $campoDocumento);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        // CPF ou CNPJ já existe na tabela, retorne para o cadastro após 4 segundos
        echo "CPF ou CNPJ já cadastrado. Redirecionando...";
        header("Refresh: 4; URL=cadastro.html"); // Redireciona após 4 segundos
        exit(); // Certifique-se de sair do script após o redirecionamento
    }

    // Continue com a inserção dos dados
    $campos = implode(', ', $documentoConfig['campos']);
    $parametros = str_repeat('?, ', count($documentoConfig['campos']) - 1) . '?';
    $sql = "INSERT INTO {$documentoConfig['tabela']} ($campos) VALUES ($parametros)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro na preparação da consulta: " . $conn->error);
    }

    // Parâmetros para bind_param
    $params = [''];
    for ($i = 0; $i < count($documentoConfig['campos']); $i++) {
        $params[0] .= 's'; // Adiciona 's' para string
    }
    if ($documento === 'cnpj') {
        $params[] = &$nome;
        $params[] = &$email;
        $params[] = &$campoDocumento;
        $telefoneEmpresa = $_POST['telefoneEmpresa'];
        $servico = $_POST['servico'];
        $params[] = &$telefoneEmpresa;
        $params[] = &$servico;
        $params[] = &$hashedSenha;
    }  
    else{  
    $params[] = &$nome;
    $params[] = &$email;
    $params[] = &$campoDocumento;
    $params[] = &$hashedSenha;
    }


   
    // Faz o bind dos parâmetros
    call_user_func_array([$stmt, 'bind_param'], $params);

    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso!";
        
        // Redireciona para a página "index.html"
        header("Location: index.html");
        exit(); // Certifique-se de sair do script após o redirecionamento
    } else {
        echo "Erro ao salvar os dados: " . $stmt->error;
    }

    $stmt->close();
} else {
    die("Escolha um tipo de documento válido (CPF ou CNPJ).");
}

$conn->close();


?>
