<?php
// Inicio a sessão
session_start();

// Limpo todas as variáveis da sessão
$_SESSION = array();

// Destruo a sessão
session_destroy();

// Redireciono para o index com mensagem
header("Location: index.php?logout=success");
exit;
?>