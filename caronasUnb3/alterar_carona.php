<?php
session_start();
require_once 'includes/conexao.php';

if (!isset($_SESSION['matricula']) || ($_SESSION['tipo_usuario'] ?? '') !== 'motorista') {
    header('Location: index.php');
    exit;
}

$cod_carona = intval($_GET['cod_carona'] ?? $_POST['cod_carona'] ?? 0);

// Buscar dados atuais da carona
$stmt = $conn->prepare("
    SELECT c.cod_carona, c.data_agendamento, c.cod_confirmacao, l.origem, l.destino, l.ponto_referencia, l.hora_partida
    FROM CARONA c
    JOIN local_carona lc ON c.cod_carona = lc.cod_carona
    JOIN LOCAL l ON lc.cod_local = l.cod_local
    WHERE c.cod_carona = ?
    LIMIT 1
");
$stmt->bind_param('i', $cod_carona);
$stmt->execute();
$res = $stmt->get_result();
$carona = $res->fetch_assoc();
$stmt->close();

if (!$carona) {
    echo "Carona não encontrada!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Carona</title>
    <style>
        .centered-box { max-width: 500px; margin: 40px auto; background: #fff; padding: 2em; border-radius: 18px; box-shadow: 0 6px 20px #0001;}
        label { font-weight: bold; display: block; margin-top: 1em;}
        input { width: 100%; padding: 7px; border-radius: 5px; border: 1px solid #bbb;}
        button { margin-top: 16px; background: #1976d2; color: #fff; border: none; padding: 10px 22px; border-radius: 8px;}
        button:hover { background: #1258a1; }
    </style>
</head>
<body>
<div class="centered-box">
    <h2>Alterar Carona</h2>
    <form method="POST" action="controllers/salvar_alteracao_carona.php">
        <input type="hidden" name="cod_carona" value="<?= $carona['cod_carona'] ?>">
        <label>Data:</label>
        <input type="date" name="data_agendamento" value="<?= htmlspecialchars($carona['data_agendamento']) ?>" required>
        <label>Código Confirmação:</label>
        <input type="text" name="cod_confirmacao" value="<?= htmlspecialchars($carona['cod_confirmacao']) ?>" required maxlength="4">
        <label>Origem:</label>
        <input type="text" name="origem" value="<?= htmlspecialchars($carona['origem']) ?>" required>
        <label>Destino:</label>
        <input type="text" name="destino" value="<?= htmlspecialchars($carona['destino']) ?>" required>
        <label>Ponto de Referência:</label>
        <input type="text" name="ponto_referencia" value="<?= htmlspecialchars($carona['ponto_referencia']) ?>" required>
        <label>Hora de Saída:</label>
        <input type="time" name="hora_partida" value="<?= htmlspecialchars($carona['hora_partida']) ?>" required>
        <button type="submit">Salvar Alterações</button>
    </form>
    <p><a href="minhas_caronas_motorista.php">Voltar</a></p>
</div>
</body>
</html>
