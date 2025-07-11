<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/conexao.php';

if (!isset($_SESSION['matricula'])) {
    header("Location: index.php");
    exit;
}

$matricula = intval($_SESSION['matricula']);
$tipo = $_SESSION['tipo_usuario'] ?? '';

function statusFormat($status) {
    if ($status === 'pendente') return 'Aguardando início';
    if ($status === 'em_andamento') return 'Em Andamento';
    if ($status === 'finalizada') return 'Finalizada';
    return ucfirst($status);
}

if ($tipo === 'passageiro') {
    // Busca cod_passageiro do usuário
    $stmtPass = $conn->prepare("SELECT cod_passageiro FROM PASSAGEIRO WHERE matricula = ?");
    $stmtPass->bind_param('i', $matricula);
    $stmtPass->execute();
    $stmtPass->bind_result($cod_passageiro);
    $stmtPass->fetch();
    $stmtPass->close();

    if (!$cod_passageiro) {
        die("Erro: passageiro não encontrado.");
    }

    // Busca corridas do passageiro que ainda não foram avaliadas
    $sql = "
    SELECT 
        c.cod_carona,
        c.data_agendamento,
        c.cod_confirmacao,
        c.status,
        l.origem,
        l.destino,
        lc.parada,
        l.ponto_referencia,
        l.hora_partida,
        am.nome AS motorista_nome
    FROM CARONA_PASSAGEIRO cp
    JOIN CARONA c ON cp.cod_carona = c.cod_carona
    JOIN MOTORISTA m ON c.cod_motorista = m.cod_motorista
    JOIN ALUNO am ON m.matricula = am.matricula
    JOIN LOCAL_CARONA lc ON c.cod_carona = lc.cod_carona
    JOIN LOCAL l ON lc.cod_local = l.cod_local
    LEFT JOIN AVALIACAO av ON av.cod_carona = c.cod_carona AND av.cod_passageiro = cp.cod_passageiro
    WHERE cp.cod_passageiro = ?
      AND c.status IN ('pendente', 'em_andamento', 'finalizada')
      AND av.cod_avaliacao IS NULL
    ORDER BY c.data_agendamento DESC, c.cod_carona DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $cod_passageiro);
    $stmt->execute();
    $res = $stmt->get_result();

} elseif ($tipo === 'motorista') {
    // Busca cod_motorista do usuário logado
    $stmtMot = $conn->prepare("SELECT cod_motorista FROM MOTORISTA WHERE matricula = ?");
    $stmtMot->bind_param('i', $matricula);
    $stmtMot->execute();
    $stmtMot->bind_result($cod_motorista);
    $stmtMot->fetch();
    $stmtMot->close();

    if (!$cod_motorista) {
        die("Erro: motorista não encontrado.");
    }

    // Busca caronas do motorista que possuem pelo menos um passageiro não avaliado
    $sql = "
    SELECT 
        c.cod_carona,
        c.data_agendamento,
        c.cod_confirmacao,
        c.status,
        l.origem,
        l.destino,
        lc.parada,
        l.ponto_referencia,
        l.hora_partida
    FROM CARONA c
    JOIN LOCAL_CARONA lc ON c.cod_carona = lc.cod_carona
    JOIN LOCAL l ON lc.cod_local = l.cod_local
    WHERE c.cod_motorista = ?
      AND c.status IN ('pendente', 'em_andamento', 'finalizada')
      AND EXISTS (
        SELECT 1 FROM CARONA_PASSAGEIRO cp
        LEFT JOIN AVALIACAO av ON av.cod_carona = cp.cod_carona AND av.cod_passageiro = cp.cod_passageiro
        WHERE cp.cod_carona = c.cod_carona AND av.cod_avaliacao IS NULL
      )
    ORDER BY c.data_agendamento DESC, c.cod_carona DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $cod_motorista);
    $stmt->execute();
    $res = $stmt->get_result();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Corridas em Andamento</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .centered-box { max-width: 1200px; margin: 40px auto; background: #fff; padding: 2em; border-radius: 20px; box-shadow: 0 6px 24px rgba(40,60,130,.12); text-align: center; }
        table { width: 100%; border-collapse: collapse; background: #f9fbff; border-radius: 16px; margin-bottom: 2em; }
        th, td { padding: 1em 0.6em; text-align: center; font-size: 1.05em; }
        th { background: #185ada; color: #fff; font-weight: 700; }
        tr:nth-child(even) td { background: #f0f6ff; }
        td { background: #f9fbff; border-bottom: 1px solid #e6ecfa; color: #223; }
        button { background: #185ada; color: #fff; border: none; padding: 0.5em 1.3em; border-radius: 8px; font-size: 1em; font-weight: 600; cursor: pointer; transition: background .18s; }
        button:hover { background: #144bb5; }
        .pass-list { font-size: 0.98em; margin: 0.3em 0; color: #256; }
    </style>
</head>
<body>
<div class="centered-box">
    <h2>Corridas em Andamento</h2>
    <table>
        <tr>
            <th>Data</th>
            <th>Código</th>
            <th>Origem</th>
            <th>Destino</th>
            <th>Ponto Ref.</th>
            <th>Hora Saída</th>
            <?php if ($tipo === 'motorista'): ?>
                <th>Passageiros</th>
            <?php else: ?>
                <th>Motorista</th>
            <?php endif; ?>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php while ($c = $res->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars(date('d/m/Y', strtotime($c['data_agendamento']))) ?></td>
                <td><?= htmlspecialchars($c['cod_confirmacao']) ?></td>
                <td><?= htmlspecialchars($c['origem']) ?></td>
                <td><?= htmlspecialchars($c['destino']) ?></td>
                <td><?= htmlspecialchars($c['ponto_referencia']) ?></td>
                <td><?= htmlspecialchars(substr($c['hora_partida'],0,5)) ?></td>
                <?php if ($tipo === 'motorista'): ?>
                    <td>
                        <?php
                        // Lista os passageiros desta carona:
                        $sqlp = "SELECT a.nome 
                                 FROM CARONA_PASSAGEIRO cp 
                                 JOIN PASSAGEIRO p ON cp.cod_passageiro = p.cod_passageiro
                                 JOIN ALUNO a ON p.matricula = a.matricula
                                 WHERE cp.cod_carona = ?";
                        $stm = $conn->prepare($sqlp);
                        $stm->bind_param('i', $c['cod_carona']);
                        $stm->execute();
                        $rsp = $stm->get_result();
                        $n = 0;
                        while ($p = $rsp->fetch_assoc()) {
                            if ($n++) echo ", ";
                            echo htmlspecialchars($p['nome']);
                        }
                        $stm->close();
                        if ($n === 0) echo '-';
                        ?>
                    </td>
                <?php else: ?>
                    <td><?= htmlspecialchars($c['motorista_nome']) ?></td>
                <?php endif; ?>
                <td><?= statusFormat($c['status']) ?></td>
                <td>
                <?php if ($tipo === 'motorista'): ?>
                    <?php if ($c['status'] === 'pendente'): ?>
                        <form method="POST" action="controllers/iniciar_corrida.php" style="display:inline;" onsubmit="return confirm('Iniciar corrida?');">
                            <input type="hidden" name="cod_carona" value="<?= $c['cod_carona'] ?>">
                            <button type="submit">Iniciar corrida</button>
                        </form>
                    <?php elseif ($c['status'] === 'em_andamento'): ?>
                        <form method="POST" action="controllers/finalizar_corrida_motorista.php" style="display:inline;" onsubmit="return confirm('Finalizar corrida?');">
                            <input type="hidden" name="cod_carona" value="<?= $c['cod_carona'] ?>">
                            <button type="submit">Finalizar corrida</button>
                        </form>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                <?php elseif ($tipo === 'passageiro'): ?>
                    <?php if ($c['status'] === 'finalizada'): ?>
                        <form method="GET" action="feedback.php" style="display:inline;">
                            <input type="hidden" name="cod_carona" value="<?= $c['cod_carona'] ?>">
                            <button type="submit">Dar feedback</button>
                        </form>
                    <?php else: ?>
                        Aguardando motorista
                    <?php endif; ?>
                <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <p><a href="dashboard.php">Voltar ao Dashboard</a></p>
</div>
</body>
</html>
