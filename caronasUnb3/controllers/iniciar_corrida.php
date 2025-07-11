<?php
session_start();
require_once '../includes/conexao.php';

// Função de notificação
function enviarNotificacao($conn, $cod_aluno, $mensagem) {
    $stmt = $conn->prepare("INSERT INTO NOTIFICACAO (cod_aluno, data_notificacao, mensagem) VALUES (?, CURDATE(), ?)");
    $stmt->bind_param('is', $cod_aluno, $mensagem);
    $stmt->execute();
    $stmt->close();
}

// Apenas motorista pode iniciar corrida
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

// Confirma se o motorista é dono da carona
$stmt = $conn->prepare(
    "SELECT c.cod_carona
     FROM CARONA c
     JOIN MOTORISTA m ON c.cod_motorista = m.cod_motorista
     WHERE c.cod_carona = ? AND m.matricula = ?"
);
$stmt->bind_param('ii', $cod_carona, $matricula);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    $stmt->close();
    header("Location: ../corrida_andamento.php?msg=Carona não encontrada ou acesso negado.");
    exit;
}
$stmt->close();

// Atualiza status para "em_andamento" apenas se ainda estiver pendente
$stmt = $conn->prepare("UPDATE CARONA SET status = 'em_andamento' WHERE cod_carona = ? AND status = 'pendente'");
$stmt->bind_param('i', $cod_carona);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $stmt->close();

    // Busca todos os passageiros desta carona e notifica
    $stmtPass = $conn->prepare("
        SELECT p.matricula
        FROM CARONA_PASSAGEIRO cp
        JOIN PASSAGEIRO p ON cp.cod_passageiro = p.cod_passageiro
        WHERE cp.cod_carona = ?
    ");
    $stmtPass->bind_param('i', $cod_carona);
    $stmtPass->execute();
    $res = $stmtPass->get_result();

    while ($row = $res->fetch_assoc()) {
        enviarNotificacao($conn, $row['matricula'], "Sua corrida (código $cod_carona) foi iniciada pelo motorista.");
    }
    $stmtPass->close();

    header("Location: ../corrida_andamento.php?msg=Corrida iniciada com sucesso! Passageiros notificados.");
    exit;
} else {
    $stmt->close();
    header("Location: ../corrida_andamento.php?msg=Não foi possível iniciar a corrida (status incompatível).");
    exit;
}
?>
