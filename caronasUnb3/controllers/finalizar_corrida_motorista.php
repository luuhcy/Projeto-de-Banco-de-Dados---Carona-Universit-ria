<?php
session_start();
require_once '../includes/conexao.php';

// Apenas motorista pode finalizar corrida
if (!isset($_SESSION['matricula']) || ($_SESSION['tipo_usuario'] ?? '') !== 'motorista') {
    header('Location: ../index.php?msg=Login necessário.');
    exit;
}

$matricula = intval($_SESSION['matricula']);
$cod_carona = intval($_POST['cod_carona'] ?? 0);

if (!$cod_carona) {
    header("Location: ../corrida_andamento.php?msg=Carona inválida.");
    exit;
}

// Confere se a carona pertence ao motorista logado
$stmt = $conn->prepare("
    SELECT c.cod_carona
    FROM CARONA c
    JOIN MOTORISTA m ON c.cod_motorista = m.cod_motorista
    WHERE c.cod_carona = ? AND m.matricula = ?
");
$stmt->bind_param('ii', $cod_carona, $matricula);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    $stmt->close();
    header("Location: ../corrida_andamento.php?msg=Carona não encontrada ou acesso negado.");
    exit;
}
$stmt->close();

// Só finaliza se estiver "em_andamento"
$stmt = $conn->prepare("UPDATE CARONA SET status = 'finalizada' WHERE cod_carona = ? AND status = 'em_andamento'");
$stmt->bind_param('i', $cod_carona);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $stmt->close();
    header("Location: ../corrida_andamento.php?msg=Corrida finalizada com sucesso!");
    exit;
} else {
    $stmt->close();
    header("Location: ../corrida_andamento.php?msg=Não foi possível finalizar a corrida (status incompatível).");
    exit;
}
?>
