// controllers/alterar_foto.php
<?php
session_start();
if (!isset($_SESSION['matricula'])) {
    header('Location: ../index.php');
    exit;
}
require_once __DIR__ . '/../includes/conexao.php';

$matricula = $_SESSION['matricula'];
if (!isset($_FILES['new_photo']) || $_FILES['new_photo']['error'] !== UPLOAD_ERR_OK) {
    die('Erro no upload da foto. <a href="../perfil.php">Voltar</a>');
}
$fotoData = file_get_contents($_FILES['new_photo']['tmp_name']);

$stmt = $conn->prepare('UPDATE ALUNO SET foto = ? WHERE matricula = ?');
$stmt->bind_param('bi', $null, $matricula);
$stmt->send_long_data(0, $fotoData);
if ($stmt->execute()) {
    $_SESSION['msg'] = 'Foto atualizada com sucesso.';
    header('Location: ../perfil.php');
    exit;
} else {
    die('Erro ao atualizar foto: ' . $stmt->error);
}
?>