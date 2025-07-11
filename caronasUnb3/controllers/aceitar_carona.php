<?php
session_start();
require_once '../includes/conexao.php';

if (!isset($_SESSION['matricula']) || ($_SESSION['tipo_usuario'] ?? '') !== 'passageiro') {
    header('Location: ../index.php');
    exit;
}

$matricula = intval($_SESSION['matricula']);
$cod_carona = intval($_POST['cod_carona'] ?? 0);

if ($cod_carona <= 0) {
    header("Location: ../caronas_disponiveis.php?msg=Carona inválida.");
    exit;
}

// Busca o cod_passageiro correspondente ao usuário logado
$stmtPass = $conn->prepare("SELECT cod_passageiro FROM PASSAGEIRO WHERE matricula = ?");
$stmtPass->bind_param('i', $matricula);
$stmtPass->execute();
$stmtPass->bind_result($cod_passageiro);
$stmtPass->fetch();
$stmtPass->close();

if (!$cod_passageiro) {
    header("Location: ../caronas_disponiveis.php?msg=Passageiro não encontrado.");
    exit;
}

// Verifica se já aceitou esta carona
$stmtChk = $conn->prepare("SELECT 1 FROM CARONA_PASSAGEIRO WHERE cod_passageiro = ? AND cod_carona = ?");
$stmtChk->bind_param('ii', $cod_passageiro, $cod_carona);
$stmtChk->execute();
$stmtChk->store_result();
if ($stmtChk->num_rows > 0) {
    $msg = "Você já aceitou esta carona.";
    $stmtChk->close();
    header("Location: ../caronas_disponiveis.php?msg=" . urlencode($msg));
    exit;
}
$stmtChk->close();

// Insere na tabela CARONA_PASSAGEIRO
$stmtIns = $conn->prepare("INSERT INTO CARONA_PASSAGEIRO (cod_passageiro, cod_carona) VALUES (?, ?)");
$stmtIns->bind_param('ii', $cod_passageiro, $cod_carona);
if ($stmtIns->execute()) {
    $msg = "Carona aceita com sucesso!";
} else {
    $msg = "Erro ao aceitar carona.";
}
$stmtIns->close();

header("Location: ../caronas_disponiveis.php?msg=" . urlencode($msg));
exit;
?>
