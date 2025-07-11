<?php
session_start();
require_once '../includes/conexao.php';

$matricula = $_SESSION['matricula'] ?? null;
$current = trim($_POST['current_password'] ?? '');
$new     = trim($_POST['new_password'] ?? '');
$confirm = trim($_POST['confirm_password'] ?? '');

if (!$matricula || !$current || !$new || !$confirm) {
    header('Location: ../perfil.php?msg=Preencha todos os campos!');
    exit;
}

// Busca a senha atual do banco
$stmt = $conn->prepare('SELECT senha FROM ALUNO WHERE matricula = ?');
$stmt->bind_param('i', $matricula);
$stmt->execute();
$stmt->bind_result($senhaAtual);
$stmt->fetch();
$stmt->close();

if ($current !== $senhaAtual) {
    header('Location: ../perfil.php?msg=Senha atual incorreta!');
    exit;
}
if ($new !== $confirm) {
    header('Location: ../perfil.php?msg=Nova senha não confere com a confirmação!');
    exit;
}
if (strlen($new) < 4) {
    header('Location: ../perfil.php?msg=Senha deve ter ao menos 4 caracteres!');
    exit;
}

// Atualiza a senha
$stmt = $conn->prepare('UPDATE ALUNO SET senha = ? WHERE matricula = ?');
$stmt->bind_param('si', $new, $matricula);
$stmt->execute();
$stmt->close();

header('Location: ../perfil.php?msg=Senha alterada com sucesso!');
exit;
?>
