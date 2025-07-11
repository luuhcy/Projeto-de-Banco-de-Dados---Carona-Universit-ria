<?php
// Conexão com o banco de dados Caronas UNB
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'caronas_unb5';

$conn = new mysqli($host, $user, $pass, $db);

// Verifica conexão
if ($conn->connect_error) {
    die('Conexão falhou: ' . $conn->connect_error);
}

// Define charset para evitar problemas de acentuação
$conn->set_charset('utf8mb4');
?>

