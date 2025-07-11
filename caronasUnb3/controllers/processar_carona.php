<?php
session_start();
if (!isset($_SESSION['matricula']) || ($_SESSION['tipo_usuario'] ?? '') !== 'motorista') {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../includes/conexao.php';

// Recupera dados do formulário
$placa            = $_POST['placa'] ?? '';
$data_agendamento = $_POST['data_agendamento'] ?? '';
$cod_confirmacao  = $_POST['cod_confirmacao'] ?? '';
$ponto_referencia = $_POST['ponto_referencia'] ?? '';
$origem           = $_POST['origem'] ?? '';
$destino          = $_POST['destino'] ?? '';
$hora_partida     = $_POST['hora_partida'] ?? '';
$parada           = $_POST['parada'] ?? '';

$matricula = $_SESSION['matricula'];

// Busca cod_motorista do usuário logado
$stmt = $conn->prepare("SELECT cod_motorista FROM MOTORISTA WHERE matricula = ?");
$stmt->bind_param('i', $matricula);
$stmt->execute();
$stmt->bind_result($cod_motorista);
$stmt->fetch();
$stmt->close();

if (!$cod_motorista) {
    header('Location: ../oferecer_carona.php?msg=Erro ao identificar motorista');
    exit;
}

$conn->begin_transaction();

try {
    // 1. Insere LOCAL
    $stmt = $conn->prepare("INSERT INTO LOCAL (ponto_referencia, destino, origem, hora_partida) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $ponto_referencia, $destino, $origem, $hora_partida);
    $stmt->execute();
    $cod_local = $stmt->insert_id;
    $stmt->close();

    // 2. Insere CARONA (ajuda_de_custo por padrão null)
    $stmt = $conn->prepare("INSERT INTO CARONA (cod_motorista, data_agendamento, cod_confirmacao, cod_transacao) VALUES (?, ?, ?, NULL)");
    $stmt->bind_param('iss', $cod_motorista, $data_agendamento, $cod_confirmacao);
    $stmt->execute();
    $cod_carona = $stmt->insert_id;
    $stmt->close();

    // 3. Relaciona na LOCAL_CARONA (UMA LINHA SÓ)
    $stmt = $conn->prepare("INSERT INTO LOCAL_CARONA (cod_carona, cod_local, parada) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $cod_carona, $cod_local, $parada);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    header('Location: ../oferecer_carona.php?msg=Carona cadastrada com sucesso!');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('Location: ../oferecer_carona.php?msg=Erro ao cadastrar carona: ' . urlencode($e->getMessage()));
    exit;
}
?>
