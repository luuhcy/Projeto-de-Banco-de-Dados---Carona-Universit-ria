<?php
session_start();
require_once 'includes/conexao.php';

if (!isset($_SESSION['matricula']) || $_SESSION['tipo_usuario'] !== 'motorista') {
    header("Location: index.php");
    exit;
}

// Pega cod_motorista do logado
$matricula = $_SESSION['matricula'];
$sql = "SELECT cod_motorista FROM Motorista WHERE matricula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $matricula);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows > 0) {
    $dados = $res->fetch_assoc();
    $cod_motorista = $dados['cod_motorista'];
} else {
    die("Motorista não encontrado!");
}

// Busca veículos
$sqlV = "SELECT * FROM Veiculo WHERE cod_motorista = ?";
$stmtV = $conn->prepare($sqlV);
$stmtV->bind_param('i', $cod_motorista);
$stmtV->execute();
$veiculos = $stmtV->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meus Veículos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="logo-caronas">
        <span class="logo-icon">🚗</span>
        <span class="logo-text"><b>Caronas</b> UnB</span>
    </div>
    <h2>Meus Veículos</h2>
    <a class="btn" href="cadastrar_veiculo.php">Cadastrar Novo Veículo</a>
    <table style="width:100%;margin-top:20px;">
        <tr>
            <th>Placa</th>
            <th>Modelo</th>
            <th>Cor</th>
            <th>Ações</th>
        </tr>
        <?php while($v = $veiculos->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($v['placa']); ?></td>
                <td><?php echo htmlspecialchars($v['modelo']); ?></td>
                <td><?php echo htmlspecialchars($v['cor']); ?></td>
                <td>
                    <a href="editar_veiculo.php?placa=<?php echo urlencode($v['placa']); ?>">Editar</a> |
                    <a href="controllers/excluir_veiculo.php?placa=<?php echo urlencode($v['placa']); ?>" onclick="return confirm('Tem certeza que deseja excluir este veículo?')">Excluir</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <p><a href="dashboard.php">Voltar ao Dashboard</a></p>
</body>
</html>
