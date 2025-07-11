<?php
session_start();
require_once 'includes/conexao.php';

if (!isset($_SESSION['matricula']) || $_SESSION['tipo_usuario'] !== 'passageiro') {
    header('Location: index.php');
    exit;
}

$matricula = intval($_SESSION['matricula']);

// Busca o cod_passageiro do usuário logado
$stmtPass = $conn->prepare("SELECT cod_passageiro FROM PASSAGEIRO WHERE matricula = ?");
$stmtPass->bind_param('i', $matricula);
$stmtPass->execute();
$stmtPass->bind_result($cod_passageiro);
$stmtPass->fetch();
$stmtPass->close();

if (!$cod_passageiro) {
    echo "Erro: passageiro não encontrado.<br><a href='dashboard.php'>Voltar</a>";
    exit;
}

// Busca caronas aceitas por esse passageiro
$sql = "
SELECT
    c.cod_carona,
    c.data_agendamento,
    c.cod_confirmacao,
    a.nome AS motorista_nome,
    v.placa AS veiculo_placa,
    v.modelo AS veiculo_modelo,
    v.cor AS veiculo_cor,
    l.origem,
    l.destino,
    l.ponto_referencia,
    l.hora_partida
FROM CARONA_PASSAGEIRO cp
JOIN CARONA c ON cp.cod_carona = c.cod_carona
JOIN MOTORISTA m ON c.cod_motorista = m.cod_motorista
JOIN ALUNO a ON m.matricula = a.matricula
LEFT JOIN VEICULO v ON m.cod_motorista = v.cod_motorista
JOIN LOCAL_CARONA lc ON lc.cod_carona = c.cod_carona
JOIN LOCAL l ON lc.cod_local = l.cod_local
WHERE cp.cod_passageiro = ?
ORDER BY c.data_agendamento DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $cod_passageiro);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minhas Caronas Aceitas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="centered-box">
    <h2>📋 Minhas Caronas Aceitas</h2>
    <table>
        <tr>
            <th>Data</th>
            <th>Código</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Ponto Referência</th>
            <th>Hora</th>
            <th>Motorista</th>
            <th>Veículo</th>
            <th>Ação</th>
        </tr>
        <?php while ($c = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['data_agendamento']))) ?></td>
            <td><?= htmlspecialchars($c['cod_confirmacao']) ?></td>
            <td><?= htmlspecialchars($c['origem']) ?></td>
            <td><?= htmlspecialchars($c['destino']) ?></td>
            <td><?= htmlspecialchars($c['ponto_referencia']) ?></td>
            <td><?= htmlspecialchars(substr($c['hora_partida'],0,5)) ?></td>
            <td><?= htmlspecialchars($c['motorista_nome']) ?></td>
            <td><?= htmlspecialchars($c['veiculo_placa'] . " (" . $c['veiculo_modelo'] . "/" . $c['veiculo_cor'] . ")") ?></td>
            <td>
                <form method="POST" action="controllers/cancelar_carona.php" style="margin:0;" onsubmit="return confirm('Deseja cancelar esta carona?');">
                    <input type="hidden" name="cod_passageiro" value="<?= $cod_passageiro ?>">
                    <input type="hidden" name="cod_carona" value="<?= $c['cod_carona'] ?>">
                    <button type="submit">Cancelar</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p><a href="dashboard.php">Voltar</a></p>
</div>
</body>
</html>
