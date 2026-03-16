<?php
session_start();
include("ligacao.php");

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_nome = $_SESSION['user_nome'] ?? "Utilizador";

$barbeiros = [];
$res = $conn->query("SELECT id, nome FROM barbeiros ORDER BY id ASC");
if ($res) {
  while ($row = $res->fetch_assoc()) $barbeiros[] = $row;
}

$erro = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $barbeiro_id = isset($_POST["barbeiro_id"]) ? (int)$_POST["barbeiro_id"] : 0;

  if ($barbeiro_id <= 0) {
    $erro = "Escolhe um barbeiro.";
  } else {
    $_SESSION["marcacao_barbeiro_id"] = $barbeiro_id;
    unset($_SESSION["marcacao_dia"], $_SESSION["marcacao_hora"], $_SESSION["marcacao_tipo"]);
    header("Location: escolher_dia.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Escolher Barbeiro - Light's Barber</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;}

body{
background:#000;
color:#fff;
font-family:'Poppins',sans-serif;
overflow-x:hidden;
}

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
position:relative;
}

.logo{
font-family:'Playfair Display', serif;
letter-spacing:3px;
font-size:22px;
position:absolute;
left:50%;
transform:translateX(-50%);
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

.menu-toggle{
display:none;
font-size:26px;
background:none;
border:none;
color:white;
cursor:pointer;
}

.container{
min-height:100vh;
display:flex;
align-items:center;
justify-content:center;
padding:120px 20px 60px;
}

.box{
width:100%;
max-width:800px;
background:rgba(0,0,0,0.85);
border:1px solid #1a1a1a;
padding:40px 30px;
text-align:center;
}

h2{
font-family:'Playfair Display', serif;
font-size:34px;
margin-bottom:10px;
}

.sub{
color:#ccc;
margin-bottom:20px;
font-size:14px;
}

.erro{
color:#ff4d4d;
margin-bottom:10px;
}

.grid{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:25px;
margin-top:20px;
}

.card{
position:relative;
border:1px solid #222;
background:#0b0b0b;
cursor:pointer;
overflow:hidden;
border-radius:20px;
height:360px;
transition:0.25s;
}

.card:hover{
transform:translateY(-4px);
border-color:#444;
}

.cover{
position:absolute;
top:0;
left:0;
width:100%;
height:70%;
overflow:hidden;
}

.cover img{
width:100%;
height:100%;
object-fit:cover;
filter:grayscale(30%);
transition:0.3s;
}

.card:hover img{
filter:grayscale(0%);
transform:scale(1.08);
}

.content{
position:absolute;
bottom:0;
width:100%;
padding:20px;
text-align:left;
}

.name{
font-size:22px;
font-weight:700;
}

.hint{
margin-top:6px;
font-size:13px;
color:#ccc;
}

.card.selected{
border-color:#fff;
box-shadow:0 0 0 1px rgba(255,255,255,0.4);
}

.actions{
margin-top:20px;
display:flex;
gap:10px;
justify-content:center;
}

.btn{
padding:12px 18px;
border:none;
cursor:pointer;
font-weight:600;
background:#fff;
color:#000;
}

.btn:hover{
background:#ccc;
}

.btn-outline{
padding:12px 18px;
border:1px solid #333;
background:transparent;
color:#fff;
cursor:pointer;
text-decoration:none;
}

.btn-outline:hover{
border-color:#555;
}

@media (max-width:800px){
.logo{
position:static;
transform:none;
}

.menu-toggle{
display:block;
}

.nav-links{
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

.nav-links.active{
display:flex;
}
}

@media (max-width:600px){
.grid{
grid-template-columns:1fr;
}
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

<?php if (isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] === 'barbeiro'): ?>
<a href="dashboard_barbeiro.php">Dashboard</a>
<?php endif; ?>

<span>Olá, <?php echo htmlspecialchars($user_nome); ?></span>
<a href="logout.php">Encerrar Sessão</a>
</div>

</nav>
</header>

<section class="container">

<div class="box">

<h2>1/4 — Escolher Barbeiro</h2>

<div class="sub">
Seleciona um barbeiro para continuar
</div>

<?php
if($erro!=""){
echo "<div class='erro'>$erro</div>";
}
?>

<form method="POST">

<input type="hidden" name="barbeiro_id" id="barbeiro_id">

<div class="grid">

<?php foreach($barbeiros as $b): ?>
<div class="card" data-id="<?php echo (int)$b['id']; ?>">
<div class="cover">
<img src="Imagem/placeholder_barbeiro.jpg" alt="Barbeiro">
</div>

<div class="content">
<div class="name">
<?php echo htmlspecialchars($b['nome']); ?>
</div>

<div class="hint">
Clique para selecionar
</div>
</div>
</div>
<?php endforeach; ?>

</div>

<div class="actions">
<button type="submit" class="btn">
Continuar
</button>

<a class="btn-outline" href="index.php">
Cancelar
</a>
</div>

</form>

</div>
</section>

<script>
const toggle=document.getElementById("menuToggle");
const nav=document.getElementById("navLinks");

toggle.addEventListener("click",()=>{
nav.classList.toggle("active");
});

const cards=document.querySelectorAll(".card");
const input=document.getElementById("barbeiro_id");

cards.forEach(card=>{
card.addEventListener("click",()=>{
cards.forEach(c=>c.classList.remove("selected"));
card.classList.add("selected");
input.value=card.dataset.id;
});
});
</script>

</body>
</html>