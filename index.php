<?php
session_start();

// 🔥 SE NÃO ESTIVER LOGADO → VAI PARA LANDING
if (!isset($_SESSION['user_id'])) {
    header("Location: landing.php");
    exit;
}

$user_nome = $_SESSION['user_nome'] ?? "";
?>
<!DOCTYPE html>
<html lang="pt">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Light's Barber</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
background:#000;
color:#fff;
font-family:'Poppins',sans-serif;
overflow-x:hidden;
}

/* LOGO FUNDO */

body::before{
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

/* NAVBAR */

header{
position:fixed;
width:100%;
top:0;
background:rgba(0,0,0,0.85);
backdrop-filter:blur(8px);
border-bottom:1px solid #1a1a1a;
z-index:1000;
}

.navbar{
max-width:1200px;
margin:auto;
padding:16px 25px;
display:flex;
align-items:center;
justify-content:space-between;
position:relative;
}

.logo{
position:absolute;
left:50%;
transform:translateX(-50%);
font-family:'Playfair Display',serif;
letter-spacing:3px;
font-size:22px;
white-space:nowrap;
}

.nav-links{
display:flex;
gap:20px;
align-items:center;
}

.nav-links a{
text-decoration:none;
color:white;
font-size:14px;
transition:0.3s;
}

.nav-links a:hover{
color:#cfcfcf;
}

/* HERO */

.hero{
height:100vh;
display:flex;
flex-direction:column;
justify-content:center;
align-items:center;
text-align:center;
padding:0 20px;
}

.hero h1{
font-family:'Playfair Display',serif;
font-size:52px;
margin-bottom:15px;
}

.hero p{
color:#ccc;
max-width:600px;
margin-bottom:30px;
}

/* BOTÕES */

.hero-buttons{
display:flex;
gap:15px;
flex-wrap:wrap;
justify-content:center;
}

.btn-primary{
background:#fff;
color:#000;
padding:12px 26px;
text-decoration:none;
font-weight:600;
border-radius:3px;
transition:0.3s;
}

.btn-primary:hover{
background:#ddd;
}

.btn-outline{
border:1px solid #fff;
color:#fff;
padding:12px 26px;
text-decoration:none;
border-radius:3px;
transition:0.3s;
}

.btn-outline:hover{
background:#fff;
color:#000;
}

/* FOOTER */

footer{
position:absolute;
bottom:10px;
width:100%;
text-align:center;
color:#666;
font-size:12px;
}

@media(max-width:800px){
.hero h1{
font-size:36px;
}
}

</style>
</head>

<body>

<header>

<nav class="navbar">

<div class="nav-links">
<a href="index.php">Início</a>
<a href="marcar_corte.php">Marcar Corte</a>
<a href="minhas_marcacoes.php">Minhas Marcações</a>
<a href="loja.html">Loja</a>
</div>

<div class="logo">LIGHT'S BARBER</div>

<div class="nav-links">

<a href="about.php">About</a>

<?php if (isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] === 'barbeiro'): ?>
<a href="dashboard_barbeiro.php">Dashboard</a>
<?php endif; ?>

<span>Olá, <?php echo htmlspecialchars($user_nome); ?></span>
<a href="logout.php">Encerrar Sessão</a>

</div>

</nav>

</header>

<section class="hero">

<h1>Estilo Clássico. Corte Moderno.</h1>

<p>
Na Light's Barber combinamos tradição e estilo moderno para criar cortes que refletem personalidade e confiança.
</p>

<div class="hero-buttons">

<a href="marcar_corte.php" class="btn-primary">
Marcar Corte
</a>

<a href="about.php" class="btn-outline">
Conhecer Barbearia
</a>

</div>

</section>

<footer>
© <?php echo date("Y"); ?> Light's Barber
</footer>

</body>
</html>