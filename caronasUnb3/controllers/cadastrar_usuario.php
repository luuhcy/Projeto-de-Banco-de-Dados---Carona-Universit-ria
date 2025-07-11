<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../includes/conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../cadastro.php");
    exit;
}

// 1) Recebe e valida os campos
$matricula   = intval($_POST['matricula']   ?? 0);
$nome        = trim($_POST['nome']         ?? '');
$email       = trim($_POST['email']        ?? '');
$telefone    = trim($_POST['telefone']     ?? '');
$curso       = trim($_POST['curso']        ?? '');
$cod_dep     = intval($_POST['cod_dep']    ?? 0);
$genero      = $_POST['genero']            ?? '';
$senha       = trim($_POST['senha']        ?? '');
$tipo_usuario= $_POST['tipo_usuario']      ?? '';

if (
    !$matricula || !$nome || !$email ||
    !$telefone  || !$curso || !$cod_dep ||
    !$genero    || !$senha || !$tipo_usuario
) {
    echo "Preencha todos os campos! <a href='../cadastro.php'>Voltar</a>";
    exit;
}

// 2) Trata o upload da foto (opcional)
$fotoData = null;
if (
    isset($_FILES['foto']) &&
    $_FILES['foto']['error'] === UPLOAD_ERR_OK
) {
    $fotoData = file_get_contents($_FILES['foto']['tmp_name']);
}

// 3) Insere o ALUNO (tratamento separado com e sem foto)
if ($fotoData === null) {
    // Sem foto
    $sqlAluno = "INSERT INTO ALUNO (matricula, telefone, senha, nome, email, foto, curso, genero, cod_dep)
                 VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?)";
    $stmtAluno = $conn->prepare($sqlAluno);
    $stmtAluno->bind_param(
        'isssssii',
        $matricula,
        $telefone,
        $senha,
        $nome,
        $email,
        $curso,
        $genero,
        $cod_dep
    );
} else {
    // Com foto
    $sqlAluno = "INSERT INTO ALUNO (matricula, telefone, senha, nome, email, foto, curso, genero, cod_dep)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtAluno = $conn->prepare($sqlAluno);
    $stmtAluno->bind_param(
        'issssbssi',
        $matricula,
        $telefone,
        $senha,
        $nome,
        $email,
        $fotoData,
        $curso,
        $genero,
        $cod_dep
    );
}

if (!$stmtAluno->execute()) {
    echo "Erro ao cadastrar aluno: " . $stmtAluno->error
       . "<br><a href='../cadastro.php'>Voltar</a>";
    exit;
}

// salva na sessão
$_SESSION['matricula'] = $matricula;
$_SESSION['nome']      = $nome;

// 4) Se for motorista, insere em MOTORISTA
if ($tipo_usuario === 'motorista') {
    $cnh = '';
    $sqlMot = "INSERT INTO MOTORISTA (cnh, matricula) VALUES (?, ?)";
    $stmtMot = $conn->prepare($sqlMot);
    $stmtMot->bind_param('si', $cnh, $matricula);
    if (! $stmtMot->execute()) {
        echo "Aluno cadastrado, mas falha ao cadastrar motorista: "
           . $stmtMot->error
           . "<br><a href='../cadastro.php'>Voltar</a>";
        exit;
    }
    $_SESSION['tipo_usuario'] = 'motorista';
    $cod_motorista = $conn->insert_id;
    echo "<h2>Cadastro realizado com sucesso!</h2>";
    echo "<p>Você foi cadastrado como <b>Motorista</b> (cód. $cod_motorista).</p>";
    echo "<a href='../dashboard.php'>Ir para Dashboard</a>";

// 5) Caso passageiro, insere em PASSAGEIRO (apenas campo matricula)
} elseif ($tipo_usuario === 'passageiro') {
    $sqlPass = "INSERT INTO PASSAGEIRO (matricula) VALUES (?)";
    $stmtPass = $conn->prepare($sqlPass);
    $stmtPass->bind_param('i', $matricula);
    if (! $stmtPass->execute()) {
        echo "Aluno cadastrado, mas falha ao cadastrar passageiro: "
           . $stmtPass->error
           . "<br><a href='../cadastro.php'>Voltar</a>";
        exit;
    }
    $_SESSION['tipo_usuario'] = 'passageiro';
    echo "<h2>Cadastro realizado com sucesso!</h2>";
    echo "<p>Você foi cadastrado como <b>Passageiro</b>.</p>";
    echo "<a href='../dashboard.php'>Ir para Dashboard</a>";

} else {
    echo "Tipo de usuário inválido.<br><a href='../cadastro.php'>Voltar</a>";
}
?>
