<?php
$servername = "localhost"; // Nome do servidor (ou IP)
$username = "root"; // Nome de usuário do banco de dados
$password = ""; // Senha do banco de dados
$dbname = "finance"; // Nome do banco de dados

// Criação da conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Mensagem de conexão bem sucedida -> echo('Conexão feta com sucesso!');

// Verifica se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>