<?php
session_start();
session_unset();      // Limpa variáveis de sessão
session_destroy();    // Destroi a sessão
header("Location: ../index.php");
// Redireciona para login
exit;
?>
