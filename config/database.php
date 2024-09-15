<?php 
// Configurações do Banco de Dados - Neo Finance
$host = 'localhost';
$dbnome = 'neofinance_manager';
$usuario = 'root';
$senha = '';

try {
    // Criando uma nova instancia do PDO p/ conectar no Banco de Dados
    $pdo = new PDO("mysql:host=$host;dbnome=$dbnome;charser=utf8",$usuario,$senha);

    //Definindo o modo de erros p/ exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

    // Se houver algum erro na conexão com o Bando
} catch (PDOException $e) {
    // Exibindo log de erro:
    die("Erro de conexão com o banco de dados: ".$e->getMessage());
}
?>