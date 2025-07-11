<?php
session_start();
require_once 'includes/conexao.php';

if (!isset($_SESSION['matricula'])) {
    header("Location: index.php");
    exit;
}

$matricula = intval($_SESSION['matricula']);

// Busca todas as notificações do usuário, mais recentes primeiro
$sql = "SELECT mensagem, data_notificacao FROM NOTIFICACAO WHERE cod_aluno = ? ORDER BY data_notificacao DESC, cod_notificacao DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $matricula);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minhas Notificações</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8faff; color: #223; }
        .notificacao { background: #f5faff; margin: 16px auto; padding: 18px; border-radius: 8px; max-width: 600px; box-shadow: 0 1px 10px #0001;}
        .data { font-size: 0.97em; color: #1e57b0; font-weight: bold;}
        .mensagem { margin-top: 0.5em; color: #223;}
        h2 { text-align: center;}
    </style>
</head>
<body>
    <h2>🔔 Minhas Notificações</h2>
    <?php if ($res->num_rows == 0): ?>
        <div class="notificacao">Você não possui notificações.</div>
    <?php else: ?>
        <?php while ($n = $res->fetch_assoc()): ?>
            <div class="notificacao">
                <span class="data"><?= htmlspecialchars(date('d/m/Y', strtotime($n['data_notificacao']))) ?></span>
                <div class="mensagem"><?= htmlspecialchars($n['mensagem']) ?></div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
    <div style="text-align:center; margin-top:2em;">
        <a href="dashboard.php">Voltar ao Dashboard</a>
    </div>
</body>
</html>
