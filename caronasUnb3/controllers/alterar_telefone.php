<?php
session_start();
require_once '../includes/conexao.php';

$matricula = $_SESSION['matricula'];
$newPhone  = trim($_POST['new_phone'] ?? '');

if (!$newPhone) {
    header('Location: ../perfil.php?msg=Informe o novo telefone!');
    exit;
}

$stmt = $conn->prepare('UPDATE ALUNO SET telefone = ? WHERE matricula = ?');
$stmt->bind_param('si', $newPhone, $matricula);
$stmt->execute();
$stmt->close();

// Atualiza na sessão se desejar
header('Location: ../perfil.php?msg=Telefone alterado!');
exit;
?>
