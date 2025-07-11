<?php
session_start();
require_once '../includes/conexao.php';

$matricula = $_SESSION['matricula'];

$conn->begin_transaction();

try {
    // 1. Buscar todos os cod_passageiro e cod_motorista desse aluno
    $cod_passageiros = [];
    $cod_motoristas = [];
    $res = $conn->query("SELECT cod_passageiro FROM PASSAGEIRO WHERE matricula = $matricula");
    while ($row = $res->fetch_assoc()) $cod_passageiros[] = $row['cod_passageiro'];
    $res = $conn->query("SELECT cod_motorista FROM MOTORISTA WHERE matricula = $matricula");
    while ($row = $res->fetch_assoc()) $cod_motoristas[] = $row['cod_motorista'];

    // 2. Deletar avaliações
    if ($cod_passageiros) {
        $conn->query("DELETE FROM AVALIACAO WHERE cod_passageiro IN (" . implode(',', $cod_passageiros) . ")");
    }
    if ($cod_motoristas) {
        $conn->query("
            DELETE FROM AVALIACAO 
            WHERE cod_carona IN (SELECT cod_carona FROM CARONA WHERE cod_motorista IN (" . implode(',', $cod_motoristas) . "))");
    }

    // 3. Deletar relacionamento de caronas aceitas
    if ($cod_passageiros) {
        $conn->query("DELETE FROM CARONA_PASSAGEIRO WHERE cod_passageiro IN (" . implode(',', $cod_passageiros) . ")");
    }

    // 4. Deletar caronas do usuário (e locais vinculados)
    if ($cod_motoristas) {
        // Pega todas as caronas desse motorista
        $caronas = [];
        $res = $conn->query("SELECT cod_carona FROM CARONA WHERE cod_motorista IN (" . implode(',', $cod_motoristas) . ")");
        while ($row = $res->fetch_assoc()) $caronas[] = $row['cod_carona'];
        // Pega locais dessas caronas
        if ($caronas) {
            $locais = [];
            $res = $conn->query("SELECT cod_local FROM LOCAL_CARONA WHERE cod_carona IN (" . implode(',', $caronas) . ")");
            while ($row = $res->fetch_assoc()) $locais[] = $row['cod_local'];
            $conn->query("DELETE FROM LOCAL_CARONA WHERE cod_carona IN (" . implode(',', $caronas) . ")");
            if ($locais) $conn->query("DELETE FROM LOCAL WHERE cod_local IN (" . implode(',', $locais) . ")");
        }
        $conn->query("DELETE FROM CARONA WHERE cod_motorista IN (" . implode(',', $cod_motoristas) . ")");
    }

    // 5. Deletar veículos
    if ($cod_motoristas) {
        $conn->query("DELETE FROM VEICULO WHERE cod_motorista IN (" . implode(',', $cod_motoristas) . ")");
    }

    // 6. Deletar notificações
    $conn->query("DELETE FROM NOTIFICACAO WHERE cod_aluno = $matricula");

    // 7. Deletar passageiro e motorista
    $conn->query("DELETE FROM PASSAGEIRO WHERE matricula = $matricula");
    $conn->query("DELETE FROM MOTORISTA WHERE matricula = $matricula");

    // 8. Finalmente, o aluno
    $conn->query("DELETE FROM ALUNO WHERE matricula = $matricula");

    $conn->commit();
    session_destroy();
    header('Location: ../index.php?msg=Conta excluída com sucesso!');
    exit;
} catch (Exception $e) {
    $conn->rollback();
    header('Location: ../perfil.php?msg=Erro ao excluir: '.$e->getMessage());
    exit;
}
?>
