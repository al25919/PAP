<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Light's Barber</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
  background-color:#000;
  color:#fff;
  font-family:'Poppins', sans-serif;
  overflow-x:hidden;
}

body::before {
  content:"";
  position:fixed;
  inset:0;
  background-image:url('Imagem/Logo.png');
  background-repeat:no-repeat;
  background-position:center 35%;
  background-size:min(80vw,700px);
  opacity:0.10;
  z-index:-1;
  pointer-events:none;
}

header {
  position:fixed;
  width:100%;
  top:0;
  background:rgba(0,0,0,0.85);
  backdrop-filter:blur(8px);
  border-bottom:1px solid #1a1a1a;
  z-index:1000;
}

.navbar {
  max-width:1200px;
  margin:auto;
  padding:16px 25px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  position:relative;
}

.logo {
  font-family:'Playfair Display', serif;
  letter-spacing:3px;
  font-size:22px;
  position:absolute;
  left:50%;
  transform:translateX(-50%);
  white-space:nowrap;
}

.nav-links {
  display:flex;
  gap:20px;
  align-items:center;
}

.nav-links a {
  text-decoration:none;
  color:white;
  font-size:14px;
  transition:0.3s;
}

.nav-links a:hover { color:#cfcfcf; }

.menu-toggle {
  display:none;
  font-size:26px;
  background:none;
  border:none;
  color:white;
  cursor:pointer;
}

.header-msg {
  width:100%;
  text-align:center;
  padding:8px 0;
  font-size:13px;
  background:rgba(255,255,255,0.05);
  border-top:1px solid rgba(255,255,255,0.1);
  animation:fadeDown 0.4s ease;
}

@keyframes fadeDown {
  from { opacity:0; transform:translateY(-5px); }
  to { opacity:1; transform:translateY(0); }
}

.hero {
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  text-align:center;
  padding:160px 20px 60px;
}

.hero h1 {
  font-family:'Playfair Display', serif;
  font-size:clamp(32px,5vw,56px);
  margin-bottom:15px;
}

.hero p {
  font-size:clamp(14px,2.5vw,18px);
  color:#ccc;
  max-width:600px;
  margin:auto;
}

@media (max-width:800px){
  .logo { position:static; transform:none; }
  .menu-toggle { display:block; }
  .nav-links {
    position:absolute;
    top:100%;
    left:0;
    width:100%;
    background:rgba(0,0,0,0.95);
    flex-direction:column;
    align-items:center;
    gap:25px;
    padding:30px 0;
    display:none;
  }
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
      <a href="minhas_marcacoes.php">Minhas Marcações</a>
      <a href="loja.html">Loja</a>
    </div>

    <div class="logo">LIGHT'S BARBER</div>

    <div class="nav-links">
      <a href="about.php">About Us</a>

      <?php if (isset($_SESSION['user_id'])): ?>

        <?php if (isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] === 'barbeiro'): ?>
          <a href="dashboard_barbeiro.php">Dashboard</a>
        <?php endif; ?>

        <span style="font-size:14px;">
          Olá, <?php echo htmlspecialchars($_SESSION['user_nome']); ?>
        </span>

        <a href="logout.php">Encerrar Sessão</a>

      <?php else: ?>

        <a href="login.php">Login</a>

      <?php endif; ?>
    </div>
  </nav>

  <?php if (isset($_GET['logout']) && $_GET['logout'] == 'success'): ?>
    <div class="header-msg" id="logoutMsg">
      Sessão encerrada com sucesso.
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['login']) && $_GET['login'] == 'success'): ?>
    <div class="header-msg" id="loginMsg">
      Login efetuado com sucesso. Bem-vindo(a), <?php echo htmlspecialchars($_SESSION['user_nome']); ?>!
    </div>
  <?php endif; ?>

</header>

<section class="hero">
  <div>
    <h1>Estilo Clássico. Corte Moderno.</h1>
    <p>Um visual elegante, profissional e totalmente responsivo para a PAP.</p>
  </div>
</section>

<script>
const toggle = document.getElementById("menuToggle");
const nav = document.getElementById("navLinks");
toggle.addEventListener("click", () => {
  nav.classList.toggle("active");
});

setTimeout(() => {
  const logoutMsg = document.getElementById("logoutMsg");
  if (logoutMsg) {
    logoutMsg.style.opacity = "0";
    setTimeout(() => logoutMsg.remove(), 400);
  }
  const loginMsg = document.getElementById("loginMsg");
  if (loginMsg) {
    loginMsg.style.opacity = "0";
    setTimeout(() => loginMsg.remove(), 400);
  }
}, 4000);
</script>

</body>
</html>