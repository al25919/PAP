<?php
// ============================================================
// CONCLUIR_MARCACAO.PHP
// Permite ao barbeiro marcar uma marcação como "concluída"
// depois de atender o cliente
// ============================================================

session_start();
include("ligacao.php");

// Verifica se foi passado o id da marcação por GET (ex: ?id=5)
if (isset($_GET['id'])) {

    // Converte o id para inteiro, para evitar SQL injection
    $id = intval($_GET['id']);

    // Prepared statement: forma segura de atualizar o estado da marcação
    $stmt = $conn->prepare("UPDATE marcacoes SET estado = 'concluido' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Depois de concluir, volta para o dashboard do barbeiro
header("Location: dashboard_barbeiro.php");
exit;
?>