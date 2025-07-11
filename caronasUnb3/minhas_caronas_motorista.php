<?php
session_start();
require_once 'includes/conexao.php';

// Só motorista pode acessar
if (!isset($_SESSION['matricula']) || ($_SESSION['tipo_usuario'] ?? '') !== 'motorista') {
    header('Location: index.php');
    exit;
}

$matricula = $_SESSION['matricula'];

// Descobre cod_motorista
$stmt = $conn->prepare("SELECT cod_motorista FROM MOTORISTA WHERE matricula = ?");
$stmt->bind_param('i', $matricula);
$stmt->execute();
$stmt->bind_result($cod_motorista);
$stmt->fetch();
$stmt->close();

// Busca caronas do motorista
$sql = "SELECT c.cod_carona, c.data_agendamento, c.cod_confirmacao, l.origem, l.destino, l.ponto_referencia, l.hora_partida
        FROM CARONA c
        JOIN local_carona lc ON c.cod_carona = lc.cod_carona
        JOIN LOCAL l ON lc.cod_local = l.cod_local
        WHERE c.cod_motorista = ?
        GROUP BY c.cod_carona
        ORDER BY c.data_agendamento DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $cod_motorista);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minhas Caronas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .centered-box { max-width: 800px; margin: 40px auto; background: #fff; padding: 2em; border-radius: 20px; box-shadow: 0 6px 24px rgba(40,60,130,.12); text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 2em; }
        th, td { padding: 10px; text-align: center; border-bottom: 1px solid #eee; }
        th { background: #e3f0ff; }
        tr:nth-child(even) td { background: #f7fbff; }
        .btn-delete { background: #e53935; color: #fff; border: none; padding: 7px 15px; border-radius: 8px; cursor: pointer; }
        .btn-delete:hover { background: #c62828; }
    </style>
</head>
<body>
<div class="centered-box">
    <h2>Minhas Caronas</h2>
    <?php if (isset($_GET['msg'])): ?>
        <div class="msg-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <table>
        <tr>
            <th>Código</th>
            <th>Data</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Ponto Referência</th>
            <th>Hora</th>
            <th>Ação</th>
        </tr>
        <?php while ($c = $res->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($c['cod_confirmacao']) ?></td>
                <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['data_agendamento']))) ?></td>
                <td><?= htmlspecialchars($c['origem']) ?></td>
                <td><?= htmlspecialchars($c['destino']) ?></td>
                <td><?= htmlspecialchars($c['ponto_referencia']) ?></td>
                <td><?= htmlspecialchars(substr($c['hora_partida'], 0, 5)) ?></td>
                <td>
                    <form method="POST" action="controllers/excluir_carona.php" onsubmit="return confirm('Excluir esta carona? Todos os dados relacionados serão removidos!');" style="display:inline;">
                        <input type="hidden" name="cod_carona" value="<?= $c['cod_carona'] ?>">
                        <button type="submit" class="btn-delete">Excluir</button>
                    </form>
                </td>
                <td>
  
    <form method="GET" action="alterar_carona.php" style="display:inline; margin-left:8px;">
        <input type="hidden" name="cod_carona" value="<?= $c['cod_carona'] ?>">
        <button type="submit" class="btn-delete" style="background: #1976d2; margin-left:4px;">Alterar</button>
    </form>
</td>
            </tr>
        <?php endwhile; ?>
    </table>
    <p><a href="dashboard.php">Voltar ao Dashboard</a></p>
</div>
</body>
</html>
