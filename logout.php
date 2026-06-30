<?php
// ============================================================
// LOGOUT.PHP
// Termina a sessão atual do utilizador (cliente ou barbeiro)
// ============================================================

// Inicia a sessão para poder aceder/destruir as variáveis dela
session_start();

// Limpa todas as variáveis guardadas na sessão (user_id, user_nome, etc.)
$_SESSION = array();

// Destrói a sessão por completo no servidor
session_destroy();

// Redireciona o utilizador para a página inicial com um parâmetro
// que pode ser usado para mostrar uma mensagem de "sessão terminada"
header("Location: index.php?logout=success");
exit;
?>