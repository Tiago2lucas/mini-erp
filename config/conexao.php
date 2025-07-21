<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "erp"; // esse deve ser o nome do banco que você criou no phpMyAdmin

$conn = new mysqli($host, $usuario, $senha, $banco);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Erro de conexão com o banco de dados: " . $conn->connect_error);
}
?>
