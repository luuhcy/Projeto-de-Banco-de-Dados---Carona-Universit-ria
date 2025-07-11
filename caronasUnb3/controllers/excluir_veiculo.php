<?php
session_start();
require_once __DIR__ . '/../includes/conexao.php';

if (!isset($_SESSION['matricula']) || $_SESSION['tipo_usuario'] !== 'motorista') {
    header("Location: ../index.php");
    exit;
}

$placa = $_GET['placa'] ?? '';
if (!$placa) {
    header("Location: ../ver_veiculo.php");
    exit;
}

// Verifica se o veículo realmente pertence ao motorista logado (opcional para segurança extra)
$matricula = $_SESSION['matricula'];
$sql = "SELECT cod_motorista FROM Motorista WHERE matricula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $matricula);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $dados = $res->fetch_assoc();
    $cod_motorista = $dados['cod_motorista'];
    $sqlV = "DELETE FROM Veiculo WHERE placa = ? AND cod_motorista = ?";
    $stmtV = $conn->prepare($sqlV);
    $stmtV->bind_param('si', $placa, $cod_motorista);
    $stmtV->execute();
}

header("Location: ../ver_veiculo.php");
exit;
?>
