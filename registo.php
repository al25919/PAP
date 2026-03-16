<?php
session_start();
include("ligacao.php");

// só processa quando submeter o formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmar = $_POST["confirmar_password"];

    // verifico se as passwords coincidem
    if ($password !== $confirmar) {
        $erro = "As passwords não coincidem.";
    } else {
        // verifico se email já existe
        $verificar = $conn->prepare("SELECT id FROM utilizadores WHERE email = ?");
        $verificar->bind_param("s", $email);
        $verificar->execute();
        $verificar->store_result();

        if ($verificar->num_rows > 0) {
            $erro = "Este email já está registado.";
        } else {
            // encripto a password
            $password_segura = password_hash($password, PASSWORD_DEFAULT);

            // insiro o utilizador
            $stmt = $conn->prepare("INSERT INTO utilizadores (nome, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $password_segura);

            if ($stmt->execute()) {
                $sucesso = "Conta criada com sucesso! <a href='login.php'>Fazer Login</a>";
            } else {
                $erro = "Erro ao criar conta.";
            }

            $stmt->close();
        }
        $verificar->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registo - Light's Barber</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
    /* ===== RESET ===== */
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background:#000; color:#fff; font-family:'Poppins', sans-serif; overflow-x:hidden; }
    body::before { content:""; position:fixed; inset:0; background-image:url('Imagem/Logo.png'); background-repeat:no-repeat; background-position:center 35%; background-size:min(80vw,700px); opacity:0.10; z-index:-1; pointer-events:none; }

    /* ===== HEADER ===== */
    header { position:fixed; width:100%; top:0; background:rgba(0,0,0,0.85); backdrop-filter:blur(8px); border-bottom:1px solid #1a1a1a; z-index:1000; }
    .navbar { max-width:1200px; margin:auto; padding:16px 25px; display:flex; align-items:center; justify-content:space-between; position:relative; }
    .logo { font-family:'Playfair Display', serif; letter-spacing:3px; font-size:22px; position:absolute; left:50%; transform:translateX(-50%); }
    .nav-links { display:flex; gap:20px; align-items:center; }
    .nav-links a { text-decoration:none; color:white; font-size:14px; transition:0.3s; }
    .nav-links a:hover { color:#cfcfcf; }
    .menu-toggle { display:none; font-size:26px; background:none; border:none; color:white; cursor:pointer; }

    /* ===== REGISTO ===== */
    .register { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:120px 20px 60px; }
    .register-box { background:rgba(0,0,0,0.85); border:1px solid #1a1a1a; padding:40px 30px; width:100%; max-width:400px; text-align:center; }
    .register-box h2 { font-family:'Playfair Display', serif; margin-bottom:25px; font-size:32px; }
    .register-box input { width:100%; padding:12px; margin-bottom:15px; background:#111; border:1px solid #222; color:#fff; font-family:'Poppins', sans-serif; }
    .register-box input::placeholder { color:#777; }
    .register-box button { width:100%; padding:12px; background:#fff; color:#000; border:none; cursor:pointer; font-weight:600; margin-top:10px; transition:0.3s; }
    .register-box button:hover { background:#ccc; }
    .register-box p { margin-top:20px; font-size:14px; color:#ccc; }
    .register-box a { color:#fff; text-decoration:underline; }
    .mensagem { color:#c9a24d; margin-bottom:10px; }

    @media (max-width:800px) {
      .logo { position:static; transform:none; }
      .menu-toggle { display:block; }
      .nav-links { position:absolute; top:100%; left:0; width:100%; background:rgba(0,0,0,0.95); flex-direction:column; align-items:center; gap:25px; padding:30px 0; display:none; }
      .nav-links.active { display:flex; }
    }
  </style>
</head>

<body>

<header>
  <nav class="navbar">
    <button class="menu-toggle" id="menuToggle">☰</button>
    <div class="nav-links" id="navLinks">
      <a href="index.php">Início</a>
      <a href="marcar_corte.php">Marcar Corte</a>
      <a href="loja.html">Loja</a>
    </div>
    <div class="logo">LIGHT'S BARBER</div>
    <div class="nav-links">
      <a href="about.html">About Us</a>
      <a href="login.php">Login</a>
    </div>
  </nav>
</header>

<section class="register">
  <div class="register-box">

    <h2>Criar Conta</h2>

    <!-- MENSAGENS DE ERRO OU SUCESSO -->
    <?php
      if (isset($erro)) {
          echo "<div class='mensagem'>$erro</div>";
      } elseif (isset($sucesso)) {
          echo "<div class='mensagem'>$sucesso</div>";
      }
    ?>

    <!-- FORMULÁRIO COM PHP -->
    <form method="POST">
      <input type="text" name="nome" placeholder="Nome" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirmar_password" placeholder="Confirmar Password" required>
      <button type="submit">Criar Conta</button>
    </form>

    <p>Já tens conta? <a href="login.php">Fazer login</a></p>

  </div>
</section>

<script>
  const toggle = document.getElementById("menuToggle");
  const nav = document.getElementById("navLinks");
  toggle.addEventListener("click", () => { nav.classList.toggle("active"); });
</script>

</body>
</html>