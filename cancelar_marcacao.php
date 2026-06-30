<?php
// ============================================================
// CANCELAR_MARCACAO.PHP
// Permite ao barbeiro (ou cliente) cancelar uma marcação,
// alterando o seu estado na base de dados para "cancelado"
// ============================================================

include("ligacao.php");

// Verifica se foi passado o id da marcação por GET (ex: ?id=5)
if (isset($_GET['id'])) {

    // Converte o id para inteiro, para evitar SQL injection
    $id = intval($_GET['id']);

    // Prepared statement: forma segura de fazer queries com dados externos
    // evita ataques de SQL Injection (em vez de concatenar o id diretamente)
    $stmt = $conn->prepare("UPDATE marcacoes SET estado = 'cancelado' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Depois de cancelar, volta para o dashboard do barbeiro
header("Location: dashboard_barbeiro.php");
exit;
?>