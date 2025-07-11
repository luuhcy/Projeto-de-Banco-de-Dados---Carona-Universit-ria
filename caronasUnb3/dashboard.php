<?php
session_start();
if (!isset($_SESSION['matricula'])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/includes/conexao.php';

// Dados do usuário
$matricula = $_SESSION['matricula'];
$nome      = $_SESSION['nome'] ?? '';
$tipo      = strtolower(trim($_SESSION['tipo_usuario'] ?? ''));

// Busca foto do usuário
$stmt = $conn->prepare("SELECT foto FROM ALUNO WHERE matricula = ?");
$stmt->bind_param('i', $matricula);
$stmt->execute();
$stmt->bind_result($fotoData);
$stmt->fetch();
$stmt->close();

// Define a fonte da foto ou avatar padrão
$fotoSrc = ($fotoData)
    ? 'data:image/jpeg;base64,' . base64_encode($fotoData)
    : 'assets/img/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistema de Caronas UnB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f8faff; color: #1a237e; }
        .dashboard-box { background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(26,35,126,0.07); max-width: 440px; margin: 40px auto; padding: 32px 40px 24px; text-align: center; }
        .logo-caronas { display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 2rem; font-weight: bold; margin-bottom: 20px; }
        .avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #1565c0; margin-bottom: 16px; }
        ul { list-style: none; padding: 0; text-align: left; margin-top: 20px; }
        ul li { margin-bottom: 12px; }
        ul li a { display: block; color: #1565c0; text-decoration: none; background: #e3f2fd; padding: 8px 16px; border-radius: 7px; transition: background 0.2s; }
        ul li a:hover { background: #bbdefb; }
        h2 { margin: 8px 0; }
    </style>
</head>
<body>
    <div class="dashboard-box">
        <div class="logo-caronas">
            <span>🚗</span><span><b>Caronas</b> UnB</span>
        </div>
        <img src="<?= htmlspecialchars($fotoSrc) ?>" alt="Avatar de <?= htmlspecialchars($nome) ?>" class="avatar">
        <h2>Bem-vindo(a), <?= htmlspecialchars($nome) ?>!</h2>
        <ul>
            <li><a href="corrida_andamento.php">🚦 Corridas em Andamento</a></li>
            <?php if ($tipo === 'motorista'): ?>
                <li><a href="oferecer_carona.php">🚗 Oferecer Carona</a></li>
                <li><a href="minhas_caronas_motorista.php">📋 Minhas Caronas</a></li>
                <li><a href="cadastrar_veiculo.php">🚘 Cadastrar Veículo</a></li>
                <li><a href="ver_veiculo.php">🚙 Meus Veículos</a></li>
            <?php elseif ($tipo === 'passageiro'): ?>
                <li><a href="caronas_disponiveis.php">🔍 Buscar Caronas</a></li>
                <li><a href="minhas_caronas_passageiro.php">📋 Minhas Caronas Aceitas</a></li>
                <li><a href="notificacoes.php">🔔 Minhas Notificações</a></li>
            <?php endif; ?>
            <li><a href="perfil.php">👤 Meu Perfil</a></li>
            <li><a href="controllers/logout.php">🚪 Sair</a></li>
        </ul>
    </div>
</body>
</html>
