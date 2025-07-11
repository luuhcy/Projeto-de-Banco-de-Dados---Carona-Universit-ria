<?php
session_start();
if (!isset($_SESSION['matricula']) || $_SESSION['tipo_usuario'] !== 'motorista') {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Veículo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="logo-caronas">
        <span class="logo-icon">🚗</span>
        <span class="logo-text"><b>Caronas</b> UnB</span>
    </div>
    <h2>Cadastrar Veículo</h2>
    <form method="POST" action="controllers/cadastrar_veiculo.php">
        <label>Placa:</label>
        <input type="text" name="placa" maxlength="7" required><br>
        <label>Modelo:</label>
        <input type="text" name="modelo" maxlength="20" required><br>
        <label>Cor:</label>
        <input type="text" name="cor" maxlength="10" required><br>
        <button type="submit">Cadastrar Veículo</button>
    </form>
    <p><a href="dashboard.php">Voltar ao Dashboard</a></p>
</body>
</html>
