<?php
session_start();
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

/* LOGO NO FUNDO */

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
}

.logo{
font-family:'Playfair Display',serif;
letter-spacing:3px;
font-size:22px;
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
color:#ccc;
}

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
}

.hero-buttons{
margin-top:30px;
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

.section{
padding:100px 20px;
max-width:1100px;
margin:auto;
text-align:center;
}

.section h2{
font-family:'Playfair Display',serif;
font-size:38px;
margin-bottom:20px;
}

.section p{
color:#ccc;
max-width:700px;
margin:auto;
line-height:1.6;
}

/* SERVIÇOS */

.features{
margin-top:40px;
display:grid;
grid-template-columns:repeat(3,1fr);
gap:25px;
}

.feature{
background:#0f0f0f;
padding:30px;
border:1px solid #1a1a1a;
border-radius:10px;
transition:all 0.35s ease;
cursor:pointer;
position:relative;
overflow:hidden;
}

/* LINHA PREMIUM */

.feature::before{
content:"";
position:absolute;
top:0;
left:0;
width:0%;
height:3px;
background:#cfa64b;
transition:0.4s;
}

.feature:hover::before{
width:100%;
}

/* ANIMAÇÃO */

.feature:hover{
transform:translateY(-12px) scale(1.04);
border-color:#444;
box-shadow:0 20px 45px rgba(0,0,0,0.7);
}

.feature-icon{
font-size:40px;
margin-bottom:12px;
transition:0.3s;
}

.feature:hover .feature-icon{
transform:scale(1.25) rotate(-5deg);
}

.feature h3{
margin-bottom:10px;
transition:0.3s;
}

.feature:hover h3{
color:#fff;
}

footer{
border-top:1px solid #1a1a1a;
padding:30px;
text-align:center;
color:#888;
margin-top:80px;
}

@media(max-width:800px){

.features{
grid-template-columns:1fr;
}

.hero h1{
font-size:38px;
}

}

</style>
</head>

<body>

<header>

<nav class="navbar">

<div class="logo">LIGHT'S BARBER</div>

<div class="nav-links">

<a href="index.php">Início</a>
<a href="marcar_corte.php">Marcar Corte</a>
<a href="minhas_marcacoes.php">Minhas Marcações</a>
<a href="about.php">About</a>

<?php if($user_nome!=""): ?>

<span>Olá, <?php echo htmlspecialchars($user_nome); ?></span>
<a href="logout.php">Logout</a>

<?php else: ?>

<a href="login.php">Login</a>

<?php endif; ?>

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

<a href="#sobre" class="btn-outline">
Conhecer Barbearia
</a>

</div>

</section>

<section class="section" id="sobre">

<h2>Tradição e Estilo em Cada Corte</h2>

<p>
Na Light's Barber acreditamos que um corte de cabelo é mais do que estética — é identidade.  
Combinamos técnicas clássicas de barbearia com estilos modernos para garantir que cada cliente saia com confiança e personalidade.
</p>

<div class="features">

<div class="feature">

<div class="feature-icon">✂️</div>
<h3>Cortes Profissionais</h3>
<p>Cortes modernos e clássicos adaptados ao teu estilo.</p>

</div>

<div class="feature">

<div class="feature-icon">🧔</div>
<h3>Cuidados com a Barba</h3>
<p>Modelagem e manutenção profissional da barba.</p>

</div>

<div class="feature">

<div class="feature-icon">⭐</div>
<h3>Experiência Premium</h3>
<p>Ambiente confortável e atendimento de qualidade.</p>

</div>

</div>

</section>

<footer>

© <?php echo date("Y"); ?> Light's Barber

</footer>

</body>
</html>