<?php
session_start();
require_once 'includes/conexao.php';

if (!isset($_SESSION['matricula'])) {
    header('Location: index.php?msg=Login necessário.');
    exit;
}

$matricula = intval($_SESSION['matricula']);
$cod_carona = intval($_GET['cod_carona'] ?? $_POST['cod_carona'] ?? 0);

if (!$cod_carona) {
    echo "Carona inválida.";
    exit;
}

// Opcional: Validação se esse passageiro realmente está vinculado a essa carona
$stmt = $conn->prepare("
    SELECT cp.cod_passageiro
    FROM CARONA_PASSAGEIRO cp
    JOIN PASSAGEIRO p ON cp.cod_passageiro = p.cod_passageiro
    WHERE cp.cod_carona = ? AND p.matricula = ?
");
$stmt->bind_param('ii', $cod_carona, $matricula);
$stmt->execute();
$stmt->bind_result($cod_passageiro);
$stmt->fetch();
$stmt->close();

if (!$cod_passageiro) {
    echo "Você não está vinculado a essa carona.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Avaliação da Carona</title>
    <style>
        .centered-box { max-width: 520px; margin: 40px auto; background: #fff; padding: 32px; border-radius: 16px; box-shadow: 0 6px 32px rgba(40,60,130,.10); }
        label { font-weight: bold; display: block; margin-top: 1em; }
        input, select, textarea { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc; margin-top: 4px; }
        button { background: #185ada; color: #fff; border: none; border-radius: 8px; padding: 10px 32px; font-weight: 700; margin-top: 1.5em; font-size: 1.1em; cursor: pointer; }
        button:hover { background: #133d89; }
    </style>
</head>
<body>
<div class="centered-box">
    <h2>Avaliar Corrida e Contribuir</h2>
    <form method="POST" action="controllers/salvar_feedback.php">
        <input type="hidden" name="cod_carona" value="<?= htmlspecialchars($cod_carona) ?>">
        <label for="nota">Nota:</label>
        <select name="nota" id="nota" required>
            <option value="">Selecione a nota</option>
            <option value="1">1 - Péssimo</option>
            <option value="2">2 - Ruim</option>
            <option value="3">3 - Regular</option>
            <option value="4">4 - Bom</option>
            <option value="5">5 - Ótimo</option>
        </select>

        <label for="comentario">Comentário (opcional):</label>
        <textarea name="comentario" id="comentario" maxlength="255" placeholder="Deixe seu comentário..."></textarea>

        <label for="valor_ajuda">Ajuda de Custo (R$):</label>
        <input type="number" min="0" step="0.01" name="valor_ajuda" id="valor_ajuda" placeholder="Ex: 10.00">

        <label for="forma_de_pagamento">Forma de Pagamento:</label>
        <select name="forma_de_pagamento" id="forma_de_pagamento">
            <option value="">Não contribuir</option>
            <option value="Dinheiro">Dinheiro</option>
            <option value="Pix">Pix</option>
            <option value="Cartão">Cartão</option>
        </select>

        <button type="submit">Enviar Avaliação</button>
    </form>
    <p><a href="corrida_andamento.php">Voltar</a></p>
</div>
</body>
</html>
