<?php
session_start();
include("ligacao.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION["marcacao_barbeiro_id"])) {
    header("Location: marcar_corte.php");
    exit;
}

$user_nome = $_SESSION["user_nome"];
$barbeiro_id = $_SESSION["marcacao_barbeiro_id"];
$dia = $_SESSION["marcacao_dia"] ?? "";

$hoje = date("Y-m-d");

if ($dia < $hoje) {
    header("Location: escolher_dia.php");
    exit;
}

$barbeiro_nome = "Barbeiro";

$stmt = $conn->prepare("SELECT nome FROM barbeiros WHERE id = ?");
$stmt->bind_param("i", $barbeiro_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $barbeiro_nome = $res->fetch_assoc()["nome"];
}

$stmt->close();

$ocupadas = [];

$stmt = $conn->prepare("
SELECT TIME_FORMAT(data_hora,'%H:%i') as hora
FROM marcacoes
WHERE barbeiro_id = ?
AND DATE(data_hora) = ?
AND (estado IS NULL OR estado <> 'cancelado')
");

$stmt->bind_param("is", $barbeiro_id, $dia);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $ocupadas[] = $row["hora"];
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Escolher Hora - Light's Barber</title>

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
background:rgba(0,0,0,0.85);
border:1px solid #1a1a1a;
padding:40px;
max-width:700px;
width:100%;
text-align:center;
}

h2{
font-family:'Playfair Display',serif;
margin-bottom:20px;
font-size:34px;
}

.info{
color:#ccc;
margin-bottom:20px;
line-height:1.8;
}

.horas{
display:grid;
grid-template-columns:repeat(3,1fr);
gap:10px;
margin-top:20px;
}

.horas form{
margin:0;
}

.horas button{
width:100%;
padding:12px;
border:none;
background:#fff;
color:#000;
cursor:pointer;
font-weight:600;
border-radius:12px;
transition:0.3s;
}

.horas button:hover{
background:#ccc;
}

.voltar{
margin-top:20px;
display:inline-block;
color:#fff;
text-decoration:none;
border:1px solid #333;
padding:12px 18px;
border-radius:12px;
}

.voltar:hover{
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
.horas{
grid-template-columns:repeat(2,1fr);
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

<div class="container">
<div class="box">

<h2>3/4 — Escolher Hora</h2>

<div class="info">
Barbeiro: <strong><?php echo htmlspecialchars($barbeiro_nome); ?></strong><br>
Dia: <strong><?php echo htmlspecialchars($dia); ?></strong>
</div>

<div class="horas">
<?php
$horaAtual = date("H:i");

for ($h = 9; $h < 18; $h++) {
    foreach ([0,30] as $m) {
        $hora = sprintf("%02d:%02d", $h, $m);

        if ($dia == $hoje && $hora <= $horaAtual) {
            continue;
        }

        if (in_array($hora, $ocupadas)) {
            continue;
        }

        echo '<form method="POST" action="conclusao.php">
        <input type="hidden" name="hora" value="'.$hora.'">
        <button>'.$hora.'</button>
        </form>';
    }
}
?>
</div>

<a class="voltar" href="escolher_dia.php">Voltar</a>

</div>
</div>

<script>
const toggle=document.getElementById("menuToggle");
const nav=document.getElementById("navLinks");
toggle.addEventListener("click",()=>{
nav.classList.toggle("active");
});
</script>

</body>
</html>