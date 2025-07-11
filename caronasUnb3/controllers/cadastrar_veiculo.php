<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../includes/conexao.php';

// Permite só motorista
if (!isset($_SESSION['matricula']) || $_SESSION['tipo_usuario'] !== 'motorista') {
    die("Acesso negado. <a href='../index.php'>Login</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa  = strtoupper(trim($_POST['placa'] ?? ''));
    $modelo = $_POST['modelo'] ?? '';
    $cor    = $_POST['cor'] ?? '';

    if (empty($placa) || empty($modelo) || empty($cor)) {
        die("Preencha todos os campos! <a href='../cadastrar_veiculo.php'>Voltar</a>");
    }

    // Busca o cod_motorista pelo usuário logado
    $matricula = $_SESSION['matricula'];
    $sql = "SELECT cod_motorista FROM Motorista WHERE matricula = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $dados = $result->fetch_assoc();
        $cod_motorista = $dados['cod_motorista'];
    } else {
        die("Motorista não encontrado no banco! <a href='../dashboard.php'>Voltar</a>");
    }

    // Cadastra o veículo
    $sqlV = "INSERT INTO Veiculo (placa, modelo, cor, cod_motorista)
             VALUES (?, ?, ?, ?)";
    $stmtV = $conn->prepare($sqlV);
    if (!$stmtV) {
        die("Erro ao preparar o cadastro: " . $conn->error);
    }
    $stmtV->bind_param('sssi', $placa, $modelo, $cor, $cod_motorista);

    if ($stmtV->execute()) {
        echo "<h2>Veículo cadastrado com sucesso!</h2>";
        echo "<a href='../dashboard.php'>Voltar ao Dashboard</a>";
    } else {
        if ($conn->errno == 1062) {
            echo "Já existe um veículo com esta placa! <a href='../cadastrar_veiculo.php'>Tentar novamente</a>";
        } else {
            echo "Erro ao cadastrar veículo: " . $stmtV->error . "<br><a href='../cadastrar_veiculo.php'>Tentar novamente</a>";
        }
    }
} else {
    header("Location: ../cadastrar_veiculo.php");
    exit;
}
?>
