<?php
session_start();

// se já estiver logado → entra no site
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Light's Barber</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>

html{
scroll-behavior:smooth;
}

*{margin:0;padding:0;box-sizing:border-box;}

body{
font-family:'Poppins',sans-serif;
color:#fff;
background:#000;
overflow-x:hidden;
}

/* FUNDO */
.background{
position:fixed;
inset:0;
background:url('/PAP/Imagem/logo_nova.png') center/cover no-repeat;
filter:contrast(1.1) saturate(1.1);
z-index:-2;
}

/* OVERLAY */
.overlay{
position:fixed;
inset:0;
background:linear-gradient(
to bottom,
rgba(0,0,0,0.25),
rgba(0,0,0,0.6)
);
z-index:-1;
}

/* HERO */

.hero{
height:100vh;
display:flex;
align-items:center;
justify-content:center;
text-align:center;
padding:20px;
}

.content{
max-width:700px;
animation:fadeUp 1s ease;
}

/* BARBERSHOP */
.subtitle{
font-family:'Playfair Display',serif;
font-size:34px;
letter-spacing:4px;
margin-bottom:40px;
color:#f5d27a;
text-shadow:0 0 10px rgba(0,0,0,0.7);
}

/* BOTÕES */

.buttons{
display:flex;
gap:20px;
justify-content:center;
flex-wrap:wrap;
}

.btn{
padding:16px 38px;
font-size:16px;
text-decoration:none;
font-weight:600;
border-radius:4px;
transition:0.3s;
}

.btn-primary{
background:#cfa64b;
color:#000;
}

.btn-primary:hover{
background:#e0b85c;
transform:translateY(-3px);
}

.btn-outline{
border:1px solid #fff;
color:#fff;
}

.btn-outline:hover{
background:#fff;
color:#000;
transform:translateY(-3px);
}

/* SECTIONS */

.section{
padding:100px 20px;
text-align:center;
max-width:1000px;
margin:auto;
}

.section.dark{
background:#0a0a0a;
}

.section h2{
font-family:'Playfair Display',serif;
font-size:36px;
margin-bottom:20px;
}

.section p{
max-width:700px;
margin:auto;
}

/* CARDS */

.cards{
display:flex;
gap:20px;
margin-top:40px;
flex-wrap:wrap;
justify-content:center;
}

.card{
background:#111;
padding:25px;
border-radius:10px;
max-width:280px;
border:1px solid #222;
transition:0.3s;
}

.card:hover{
transform:translateY(-8px);
border-color:#444;
}

.card span{
display:block;
margin-top:10px;
color:#888;
font-size:14px;
}

/* CTA */

.cta{
padding:100px 20px;
text-align:center;
}

.cta h2{
font-size:32px;
margin-bottom:20px;
}

/* ANIMAÇÃO INICIAL */

@keyframes fadeUp{
from{
opacity:0;
transform:translateY(30px);
}
to{
opacity:1;
transform:translateY(0);
}
}

/* 🔥 ANIMAÇÃO AO SCROLL */

.reveal{
opacity:0;
transform:translateY(40px);
transition:all 0.8s ease;
}

.reveal.active{
opacity:1;
transform:translateY(0);
}

/* RESPONSIVO */

@media(max-width:768px){
.subtitle{
font-size:26px;
}
}

</style>
</head>

<body>

<div class="background"></div>
<div class="overlay"></div>

<!-- HERO -->
<section class="hero">
<div class="content">

<div class="subtitle">BARBERSHOP</div>

<div class="buttons">
<a href="login.php" class="btn btn-primary">Entrar</a>
<a href="registo.php" class="btn btn-outline">Criar Conta</a>
</div>

</div>
</section>

<!-- SOBRE -->
<section class="section reveal">
<h2>Sobre Nós</h2>
<p>
Na Light's Barber, cada detalhe importa.  
Combinamos tradição com inovação para oferecer cortes de alta qualidade,  
num ambiente moderno e confortável.
</p>
</section>

<!-- SERVIÇOS -->
<section class="section dark reveal">
<h2>Os Nossos Serviços</h2>

<div class="cards">

<div class="card">
<h3>Corte Clássico</h3>
<p>Estilo tradicional com acabamento perfeito.</p>
</div>

<div class="card">
<h3>Fade Moderno</h3>
<p>Cortes modernos com precisão e detalhe.</p>
</div>

<div class="card">
<h3>Barba Premium</h3>
<p>Tratamento completo para a tua barba.</p>
</div>

</div>
</section>

<!-- TESTEMUNHOS -->
<section class="section reveal">
<h2>O que dizem os clientes</h2>

<div class="cards">

<div class="card">
<p>"Melhor barbearia que já fui. Atendimento incrível!"</p>
<span>- João</span>
</div>

<div class="card">
<p>"Qualidade top e ambiente brutal."</p>
<span>- Miguel</span>
</div>

<div class="card">
<p>"Nunca saí insatisfeito, recomendo a 100%."</p>
<span>- André</span>
</div>

</div>
</section>

<!-- CTA -->
<section class="cta reveal">
<h2>Pronto para mudar o teu visual?</h2>

<div class="buttons">
<a href="registo.php" class="btn btn-primary">Criar Conta</a>
</div>

</section>

<!-- 🔥 SCRIPT SCROLL -->
<script>
function revealOnScroll(){
    const reveals = document.querySelectorAll(".reveal");

    for(let i=0; i<reveals.length; i++){
        const windowHeight = window.innerHeight;
        const elementTop = reveals[i].getBoundingClientRect().top;

        if(elementTop < windowHeight - 100){
            reveals[i].classList.add("active");
        }
    }
}

window.addEventListener("scroll", revealOnScroll);
</script>

</body>
</html>