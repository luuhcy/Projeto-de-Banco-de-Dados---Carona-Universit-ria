<?php
session_start();
if (!isset($_SESSION['matricula']) || ($_SESSION['tipo_usuario'] ?? '') !== 'motorista') {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/includes/conexao.php';

// Busca veículos cadastrados para esse motorista
$matricula = $_SESSION['matricula'];
$stmt = $conn->prepare("SELECT cod_motorista FROM MOTORISTA WHERE matricula = ?");
$stmt->bind_param('i', $matricula);
$stmt->execute();
$stmt->bind_result($cod_motorista);
$stmt->fetch();
$stmt->close();

$veiculos = [];
if ($cod_motorista) {
    $stmt = $conn->prepare("SELECT placa, modelo, cor FROM VEICULO WHERE cod_motorista = ?");
    $stmt->bind_param('i', $cod_motorista);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($v = $result->fetch_assoc()) {
        $veiculos[] = $v;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Oferecer Carona | Sistema de Caronas UnB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f8faff; }
        .centered-box { max-width: 480px; margin: 40px auto; background: #fff; padding: 24px; border-radius: 10px; box-shadow: 0 2px 12px #1a237e11; }
        form { display: flex; flex-direction: column; gap: 12px; }
        label { font-weight: bold; }
        input, select, button { padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #1565c0; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #1258a1; }
        .msg-sucesso { background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 5px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="centered-box">
        <h2>Oferecer Carona</h2>
        <?php if (isset($_GET['msg'])): ?>
            <div class="msg-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>
        <form method="POST" action="controllers/processar_carona.php">
            <label for="placa">Veículo:</label>
            <select id="placa" name="placa" required>
                <option value="">Selecione...</option>
                <?php foreach ($veiculos as $v): ?>
                    <option value="<?= htmlspecialchars($v['placa']) ?>">
                        <?= htmlspecialchars($v['placa'] . ' — ' . $v['modelo'] . ' (' . $v['cor'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="data_agendamento">Data da Carona:</label>
            <input type="date" id="data_agendamento" name="data_agendamento" required>

            <label for="cod_confirmacao">Código de Confirmação:</label>
            <input type="text" id="cod_confirmacao" name="cod_confirmacao" maxlength="4" required>

            <label for="ponto_referencia">Ponto de Referência:</label>
            <input type="text" id="ponto_referencia" name="ponto_referencia" required>

            <label for="origem">Origem:</label>
            <input type="text" id="origem" name="origem" required>

            <label for="destino">Destino:</label>
            <input type="text" id="destino" name="destino" required>

            <label for="hora_partida">Hora de Saída:</label>
            <input type="time" id="hora_partida" name="hora_partida" required>

            <label for="parada">Parada Intermediária (opcional):</label>
            <input type="text" id="parada" name="parada" placeholder="Ex: Rodoviária">

            <button type="submit">Cadastrar Carona</button>
        </form>
        <p><a href="dashboard.php">← Voltar ao Dashboard</a></p>
    </div>
</body>
</html>
