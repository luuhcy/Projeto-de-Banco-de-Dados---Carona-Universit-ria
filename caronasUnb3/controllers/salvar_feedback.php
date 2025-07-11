<?php
session_start();
require_once '../includes/conexao.php';

$matricula   = $_SESSION['matricula'] ?? 0;
$cod_carona  = intval($_POST['cod_carona'] ?? 0);
$nota        = intval($_POST['nota'] ?? 0);
$comentario  = trim($_POST['comentario'] ?? '');

$valor_ajuda = floatval(str_replace(',', '.', $_POST['valor_ajuda'] ?? 0));
$forma_de_pagamento = trim($_POST['forma_de_pagamento'] ?? '');

// Validações simples
if (!$cod_carona || !$nota) {
    header('Location: ../corrida_andamento.php?msg=Dados inválidos.');
    exit;
}

// Busca cod_passageiro relacionado
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
    header('Location: ../corrida_andamento.php?msg=Passageiro não encontrado.');
    exit;
}

// Evita feedback duplicado
$stmt = $conn->prepare("SELECT 1 FROM AVALIACAO WHERE cod_carona = ? AND cod_passageiro = ?");
$stmt->bind_param('ii', $cod_carona, $cod_passageiro);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header('Location: ../corrida_andamento.php?msg=Você já avaliou essa carona.');
    exit;
}
$stmt->close();

// Começa transação (para garantir atomicidade se usar ajuda de custo)
$conn->begin_transaction();

try {
    // Salva avaliação
    $stmt = $conn->prepare("
        INSERT INTO AVALIACAO (data_avaliacao, comentario, cod_carona, cod_passageiro, nota)
        VALUES (CURDATE(), ?, ?, ?, ?)
    ");
    $stmt->bind_param('siii', $comentario, $cod_carona, $cod_passageiro, $nota);
    $stmt->execute();
    $stmt->close();

    // Se foi informado valor e forma de pagamento válidos, salva ajuda de custo
    if ($valor_ajuda > 0 && $forma_de_pagamento != '') {
        $stmt = $conn->prepare("INSERT INTO AJUDA_DE_CUSTO (valor, forma_de_pagamento) VALUES (?, ?)");
        $stmt->bind_param('ds', $valor_ajuda, $forma_de_pagamento);
        $stmt->execute();
        $cod_transacao = $stmt->insert_id;
        $stmt->close();

        // Atualiza carona para registrar ajuda de custo (caso permitido uma única por carona)
        $stmt = $conn->prepare("UPDATE CARONA SET cod_transacao = ? WHERE cod_carona = ?");
        $stmt->bind_param('ii', $cod_transacao, $cod_carona);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    header('Location: ../corrida_andamento.php?msg=Feedback enviado com sucesso!');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    header('Location: ../corrida_andamento.php?msg=Erro ao registrar o feedback.');
    exit;
}
?>
