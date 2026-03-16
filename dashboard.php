<?php
// Inicio sessão
session_start();

// Se não estiver logado, redireciona para login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Pego o nome do utilizador da sessão
$nome = $_SESSION['user_nome'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - Light's Barber</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

  <style>
    body { background-color: #000; color: #fff; font-family: 'Poppins', sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; margin:0; }
    h1 { font-family: 'Playfair Display', serif; font-size: 32px; margin-bottom: 20px; }
    a { color: #fff; text-decoration: underline; font-size: 16px; margin-top: 20px; }
    a:hover { color: #ccc; }
  </style>
</head>
<body>

  <h1>Bem-vindo, <?php echo htmlspecialchars($nome); ?>!</h1>
  <p>Aqui podes ver informações da tua conta e futuras funcionalidades.</p>

  <!-- Link para logout -->
  <a href="logout.php">Sair da Conta</a>

</body>
</html>