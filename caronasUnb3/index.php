<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login | Sistema de Caronas UnB</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($_GET['erro'])): ?>
        <p style="color: red;">Matrícula ou senha inválidos.</p>
    <?php endif; ?>
    <form method="POST" action="controllers/login.php">
        <label for="matricula">Matrícula:</label>
        <input type="number" name="matricula" required><br>
        <label for="senha">Senha:</label>
        <input type="password" name="senha" required placeholder="12345678"><br>
        <button type="submit">Entrar</button>
    </form>
    <p><a href="cadastro.php">Não tem cadastro? Cadastre-se</a></p>
</body>
</html>
