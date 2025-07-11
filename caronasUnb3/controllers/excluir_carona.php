<?php
session_start();
require_once '../includes/conexao.php';

if (!isset($_SESSION['matricula']) || ($_SESSION['tipo_usuario'] ?? '') !== 'motorista') {
    header('Location: ../index.php');
    exit;
}

$cod_carona = intval($_POST['cod_carona'] ?? 0);

if ($cod_carona <= 0) {
    header('Location: ../minhas_caronas_motorista.php?msg=Carona inválida.');
    exit;
}

$conn->begin_transaction();

try {
    // 1. Excluir avaliações dessa carona
    $conn->query("DELETE FROM AVALIACAO WHERE cod_carona = $cod_carona");

    // 2. Excluir passageiros associados à carona
    $conn->query("DELETE FROM CARONA_PASSAGEIRO WHERE cod_carona = $cod_carona");

    // 3. Pegar os cod_local ligados à carona
    $res = $conn->query("SELECT cod_local FROM LOCAL_CARONA WHERE cod_carona = $cod_carona");
    $cod_locais = [];
    while ($row = $res->fetch_assoc()) {
        $cod_locais[] = intval($row['cod_local']);
    }

    // 4. Excluir ligação LOCAL_CARONA
    $conn->query("DELETE FROM LOCAL_CARONA WHERE cod_carona = $cod_carona");

    // 5. Excluir os locais só se não estão ligados a nenhuma outra carona
    foreach ($cod_locais as $cod_local) {
        $ver = $conn->query("SELECT 1 FROM LOCAL_CARONA WHERE cod_local = $cod_local LIMIT 1");
        if ($ver->num_rows == 0) {
            $conn->query("DELETE FROM LOCAL WHERE cod_local = $cod_local");
        }
    }

    // 6. Excluir a carona
    $conn->query("DELETE FROM CARONA WHERE cod_carona = $cod_carona");

    $conn->commit();
    header('Location: ../minhas_caronas_motorista.php?msg=Carona excluída com sucesso!');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('Location: ../minhas_caronas_motorista.php?msg=Erro ao excluir carona: ' . urlencode($e->getMessage()));
    exit;
}
?>
