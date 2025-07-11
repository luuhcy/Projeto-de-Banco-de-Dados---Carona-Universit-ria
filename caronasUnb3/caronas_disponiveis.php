<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once 'includes/conexao.php';

// Só passageiro pode acessar (ajuste para aceitar ambos se quiser)
if (!isset($_SESSION['matricula']) || ($_SESSION['tipo_usuario'] ?? '') !== 'passageiro') {
    header('Location: index.php');
    exit;
}

$matricula = intval($_SESSION['matricula']);

// Pega o cod_passageiro deste usuário
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

// Consulta todas as caronas que o passageiro ainda NÃO aceitou
$sql = "
SELECT
    c.cod_carona,
    c.data_agendamento,
    c.cod_confirmacao,
    m.cod_motorista,
    a.nome AS motorista_nome,
    v.placa AS veiculo_placa,
    v.modelo AS veiculo_modelo,
    v.cor AS veiculo_cor,
    lc.parada,
    l.origem,
    l.destino,
    l.ponto_referencia,
    l.hora_partida
FROM CARONA c
JOIN MOTORISTA m ON c.cod_motorista = m.cod_motorista
JOIN ALUNO a ON m.matricula = a.matricula
LEFT JOIN VEICULO v ON m.cod_motorista = v.cod_motorista
JOIN LOCAL_CARONA lc ON c.cod_carona = lc.cod_carona
JOIN LOCAL l ON lc.cod_local = l.cod_local
WHERE c.cod_carona NOT IN (
    SELECT cod_carona FROM CARONA_PASSAGEIRO WHERE cod_passageiro = ?
)
ORDER BY c.data_agendamento DESC, c.cod_carona DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $cod_passageiro);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Buscar Caronas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .centered-box { max-width: 1200px; margin: 40px auto; background: #fff; padding: 2em; border-radius: 20px; box-shadow: 0 6px 24px rgba(40,60,130,.12); text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 1em; }
        th, td { padding: 8px 4px; text-align: center; border: 1px solid #eee; }
        th { background: #eef5ff; }
        tr:nth-child(even) { background: #f9fbff; }
        .msg-sucesso { background: #e6ffe7; color: #216c38; border: 1px solid #9ee4ae; margin-bottom: 18px; padding: 8px; border-radius: 7px; }
    </style>
</head>
<body>
<div class="centered-box">
    <h2>Buscar Caronas</h2>
    <?php if (!empty($_GET['msg'])): ?>
        <div class="msg-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Data</th>
                <th>Motorista</th>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Cor</th>
                <th>Parada</th>
                <th>Ponto Ref</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Hora</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while($c = $res->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($c['cod_carona']) ?></td>
                    <td><?= htmlspecialchars($c['data_agendamento']) ?></td>
                    <td><?= htmlspecialchars($c['motorista_nome']) ?></td>
                    <td><?= htmlspecialchars($c['veiculo_placa'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['veiculo_modelo'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['veiculo_cor'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($c['parada'] ?: '-') ?></td>
                    <td><?= htmlspecialchars($c['ponto_referencia']) ?></td>
                    <td><?= htmlspecialchars($c['origem']) ?></td>
                    <td><?= htmlspecialchars($c['destino']) ?></td>
                    <td><?= htmlspecialchars(substr($c['hora_partida'],0,5)) ?></td>
                    <td>
                        <form method="POST" action="controllers/aceitar_carona.php" style="margin:0">
                            <input type="hidden" name="cod_carona" value="<?= $c['cod_carona'] ?>">
                            <button type="submit">Aceitar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <p><a href="dashboard.php">Voltar ao Dashboard</a></p>
</div>
</body>
</html>
