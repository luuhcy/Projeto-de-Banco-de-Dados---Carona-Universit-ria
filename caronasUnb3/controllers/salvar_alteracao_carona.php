<?php
session_start();
require_once '../includes/conexao.php';

if (!isset($_SESSION['matricula']) || ($_SESSION['tipo_usuario'] ?? '') !== 'motorista') {
    header('Location: ../index.php');
    exit;
}

$cod_carona         = intval($_POST['cod_carona'] ?? 0);
$data_agendamento   = $_POST['data_agendamento'] ?? '';
$cod_confirmacao    = $_POST['cod_confirmacao'] ?? '';
$origem             = $_POST['origem'] ?? '';
$destino            = $_POST['destino'] ?? '';
$ponto_referencia   = $_POST['ponto_referencia'] ?? '';
$hora_partida       = $_POST['hora_partida'] ?? '';

if (!$cod_carona || !$data_agendamento || !$cod_confirmacao || !$origem || !$destino || !$ponto_referencia || !$hora_partida) {
    header("Location: ../alterar_carona.php?cod_carona=$cod_carona&msg=Preencha todos os campos.");
    exit;
}

// Atualiza tabela CARONA
$stmt = $conn->prepare("UPDATE CARONA SET data_agendamento = ?, cod_confirmacao = ? WHERE cod_carona = ?");
$stmt->bind_param('ssi', $data_agendamento, $cod_confirmacao, $cod_carona);
$stmt->execute();
$stmt->close();

// Descobre o cod_local para atualizar
$stmt = $conn->prepare("
    SELECT lc.cod_local 
    FROM local_carona lc 
    WHERE lc.cod_carona = ?
    LIMIT 1
");
$stmt->bind_param('i', $cod_carona);
$stmt->execute();
$stmt->bind_result($cod_local);
$stmt->fetch();
$stmt->close();

// Atualiza tabela LOCAL
if ($cod_local) {
    $stmt2 = $conn->prepare("UPDATE LOCAL SET origem = ?, destino = ?, ponto_referencia = ?, hora_partida = ? WHERE cod_local = ?");
    $stmt2->bind_param('ssssi', $origem, $destino, $ponto_referencia, $hora_partida, $cod_local);
    $stmt2->execute();
    $stmt2->close();
}

header("Location: ../minhas_caronas_motorista.php?msg=Carona alterada com sucesso!");
exit;
?>
