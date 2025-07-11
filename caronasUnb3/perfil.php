<?php
session_start();
if (!isset($_SESSION['matricula'])) {
    header("Location: index.php");
    exit;
}
require_once __DIR__ . '/includes/conexao.php';

$matricula = $_SESSION['matricula'];
$nome      = $_SESSION['nome'] ?? '';

// Busca dados do usuário
$stmt = $conn->prepare("SELECT foto, telefone FROM ALUNO WHERE matricula = ?");
$stmt->bind_param("i", $matricula);
$stmt->execute();
$result = $stmt->get_result();
$fotoData = null;
$telefone = '';
if ($row = $result->fetch_assoc()) {
    $fotoData = $row['foto'];
    $telefone = $row['telefone'];
}

$fotoSrc = $fotoData
    ? 'data:image/jpeg;base64,' . base64_encode($fotoData)
    : 'assets/img/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meu Perfil | Sistema de Caronas UnB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f8faff; color: #1a237e; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 12px #1a237e11; }
        h2 { text-align: center; margin-bottom: 10px; }
        .back-link { text-align: center; margin-bottom: 20px; }
        .back-link a { color: #1565c0; text-decoration: none; font-size: 0.9rem; }
        .profile { text-align: center; margin-bottom: 30px; }
        .avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #1565c0; }
        .section { margin-bottom: 30px; }
        .section h3 { margin-bottom: 10px; color: #1565c0; }
        .section form { display: flex; flex-direction: column; gap: 10px; }
        .section label { font-weight: bold; }
        .section input[type="text"],
        .section input[type="password"],
        .section input[type="file"] { padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        .section button { align-self: start; padding: 8px 16px; background: #1565c0; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        .section button:hover { background: #1258a1; }
        .danger button { background: #c62828; }
        .danger button:hover { background: #a51c1c; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Meu Perfil</h2>
        <div class="back-link"><a href="dashboard.php">&larr; Voltar ao Dashboard</a></div>
        <div class="profile">
            <img src="<?= htmlspecialchars($fotoSrc) ?>" alt="Foto" class="avatar">
            <p><strong><?= htmlspecialchars($nome) ?></strong></p>
            <p>Telefone: <?= htmlspecialchars($telefone) ?></p>
        </div>
        <div class="section">
            <h3>Alterar Senha</h3>
            <form method="POST" action="controllers/alterar_senha.php">
                <label for="current_password">Senha atual:</label>
                <input type="password" id="current_password" name="current_password" required>
                <label for="new_password">Nova senha:</label>
                <input type="password" id="new_password" name="new_password" required>
                <label for="confirm_password">Confirmar nova senha:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <button type="submit">Salvar Senha</button>
            </form>
        </div>
        <div class="section">
                       <h3>Alterar Foto</h3>
            <form method="POST" action="controllers/alterar_foto.php" enctype="multipart/form-data">
                <label for="new_photo">Nova foto:</label>
                <input type="file" id="new_photo" name="new_photo" accept="image/*" required>
                <button type="submit">Atualizar Foto</button>
            </form>
        </div>
        <div class="section">
            <h3>Alterar Telefone</h3>
            <form method="POST" action="controllers/alterar_telefone.php">
                <label for="new_phone">Novo telefone:</label>
                <input type="text" id="new_phone" name="new_phone" value="<?= htmlspecialchars($telefone) ?>" required>
                <button type="submit">Atualizar Telefone</button>
            </form>
        </div>
        <div class="section danger">
            <h3>Excluir Conta</h3>
            <form method="POST" action="controllers/excluir_conta.php" onsubmit="return confirm('Tem certeza? Esta ação é irreversível.');">
                <button type="submit">Excluir Conta</button>
            </form>
        </div>
    </div>
</body>
</html>
