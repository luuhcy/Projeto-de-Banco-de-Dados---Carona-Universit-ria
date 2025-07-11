<?php
session_start();
require_once 'includes/conexao.php';

if (!isset($_SESSION['matricula']) || $_SESSION['tipo_usuario'] !== 'motorista') {
    header("Location: index.php");
    exit;
}

$placa = $_GET['placa'] ?? '';
if (!$placa) {
    die("Veículo não encontrado! <a href='ver_veiculo.php'>Voltar</a>");
}

// Busca dados do veículo
$sql = "SELECT * FROM Veiculo WHERE placa = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $placa);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows != 1) {
    die("Veículo não encontrado! <a href='ver_veiculo.php'>Voltar</a>");
}
$veiculo = $res->fetch_assoc();

// Atualiza veículo ao enviar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = $_POST['modelo'] ?? '';
    $cor = $_POST['cor'] ?? '';

    if (empty($modelo) || empty($cor)) {
        echo "Preencha todos os campos!";
    } else {
        $sqlU = "UPDATE Veiculo SET modelo = ?, cor = ? WHERE placa = ?";
        $stmtU = $conn->prepare($sqlU);
        $stmtU->bind_param('sss', $modelo, $cor, $placa);
        if ($stmtU->execute()) {
            header("Location: ver_veiculo.php");
            exit;
        } else {
            echo "Erro ao atualizar!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Veículo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="logo-caronas">
        <span class="logo-icon">🚗</span>
        <span class="logo-text"><b>Caronas</b> UnB</span>
    </div>
    <h2>Editar Veículo (<?php echo htmlspecialchars($veiculo['placa']); ?>)</h2>
    <form method="POST">
        <label>Modelo:</label>
        <input type="text" name="modelo" value="<?php echo htmlspecialchars($veiculo['modelo']); ?>" required><br>
        <label>Cor:</label>
        <input type="text" name="cor" value="<?php echo htmlspecialchars($veiculo['cor']); ?>" required><br>
        <button type="submit">Salvar Alterações</button>
    </form>
    <p><a href="ver_veiculo.php">Voltar</a></p>
</body>
</html>
