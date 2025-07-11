<?php
require_once 'includes/conexao.php';

// Buscar departamentos do banco
$departamentos = [];
$sql = "SELECT cod_dep, nome FROM DEPARTAMENTO";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departamentos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro | Sistema de Caronas UnB</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Cadastro de Aluno</h2>
    <form 
        method="POST" 
        action="controllers/cadastrar_usuario.php" 
        enctype="multipart/form-data"
    >
        <label for="matricula">Matrícula:</label>
        <input type="number" name="matricula" id="matricula" required><br>

        <label for="nome">Nome:</label>
        <input type="text" name="nome" id="nome" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br>

        <label for="telefone">Telefone:</label>
        <input type="tel" name="telefone" id="telefone" required><br>

        <label for="curso">Curso:</label>
        <input type="text" name="curso" id="curso" required><br>

        <label for="cod_dep">Departamento:</label>
        <select name="cod_dep" id="cod_dep" required>
            <option value="">Selecione...</option>
            <?php foreach ($departamentos as $dep): ?>
                <option value="<?= htmlspecialchars($dep['cod_dep']) ?>">
                    <?= htmlspecialchars($dep['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="genero">Gênero:</label>
        <select name="genero" id="genero" required>
            <option value="">Selecione...</option>
            <option value="M">Masculino</option>
            <option value="F">Feminino</option>
            <option value="O">Outro</option>
        </select><br>

        <label for="senha">Senha:</label>
        <input type="password" name="senha" id="senha" required><br>

        <label for="foto">Foto de Perfil:</label>
        <input type="file" name="foto" id="foto" accept="image/*"><br>

        <label>Tipo de Cadastro:</label><br>
        <input type="radio" name="tipo_usuario" value="motorista" id="tp_motorista" required>
        <label for="tp_motorista">Motorista</label>
        <input type="radio" name="tipo_usuario" value="passageiro" id="tp_passageiro" required>
        <label for="tp_passageiro">Passageiro</label>
        <br><br>

        <button type="submit">Cadastrar</button>
    </form>
    <p><a href="index.php">Voltar ao login</a></p>
</body>
</html>
