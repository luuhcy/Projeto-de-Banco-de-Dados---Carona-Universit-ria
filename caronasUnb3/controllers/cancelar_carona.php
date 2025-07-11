<?php
session_start();
require_once __DIR__ . '/../includes/conexao.php';

if (!isset($_SESSION['matricula']) || $_SESSION['tipo_usuario'] !== 'passageiro') {
    header("Location: ../index.php");
    exit;
}

$cod_carona = intval($_POST['cod_carona'] ?? 0);
$cod_passageiro = intval($_POST['cod_passageiro'] ?? 0);

// Se o formulário enviou o cod_passageiro, não precisa consultar de novo:
if (!$cod_passageiro) {
    // Recupera cod_passageiro pela matrícula logada
    $matricula = $_SESSION['matricula'];
    $stmt = $conn->prepare("SELECT cod_passageiro FROM PASSAGEIRO WHERE matricula = ?");
    $stmt->bind_param('i', $matricula);
    $stmt->execute();
    $stmt->bind_result($cod_passageiro);
    $stmt->fetch();
    $stmt->close();
}

// Só prossegue se ambos são válidos
if (!$cod_carona || !$cod_passageiro) {
    header("Location: ../minhas_caronas_passageiro.php?msg=Dados inválidos.");
    exit;
}

// Exclui o vínculo do passageiro com a carona
$stmtDel = $conn->prepare("DELETE FROM CARONA_PASSAGEIRO WHERE cod_carona = ? AND cod_passageiro = ?");
$stmtDel->bind_param('ii', $cod_carona, $cod_passageiro);
$stmtDel->execute();
$stmtDel->close();

header("Location: ../minhas_caronas_passageiro.php?msg=Carona cancelada com sucesso!");
exit;
?>
