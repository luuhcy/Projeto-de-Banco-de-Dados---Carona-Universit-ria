<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e valida os dados do formulário
    $matricula = intval($_POST['matricula'] ?? 0);
    $senha     = $_POST['senha'] ?? '';

    // Prepara e executa a consulta com verificação de tipo (motorista/passsageiro)
    $sql = "
        SELECT
            a.matricula,
            a.nome,
            CASE WHEN m.matricula IS NOT NULL THEN 'motorista' ELSE 'passageiro' END AS tipo_usuario
        FROM ALUNO a
        LEFT JOIN MOTORISTA m ON a.matricula = m.matricula
        WHERE a.matricula = ? AND a.senha = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $matricula, $senha);
    $stmt->execute();

    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        // Armazena na sessão os dados do usuário, incluindo o tipo calculado
        $_SESSION['matricula']    = $user['matricula'];
        $_SESSION['nome']         = $user['nome'];
        $_SESSION['tipo_usuario'] = $user['tipo_usuario'];

        header("Location: ../dashboard.php");
        exit;
    } else {
        header("Location: ../index.php?erro=1");
        exit;
    }
}
?>
