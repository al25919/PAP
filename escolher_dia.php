<?php
session_start();
include("ligacao.php");

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
if (!isset($_SESSION["marcacao_barbeiro_id"])) { header("Location: marcar_corte.php"); exit; }

$user_nome = $_SESSION['user_nome'] ?? "Utilizador";
$barbeiro_id = (int)$_SESSION["marcacao_barbeiro_id"];

$barbeiro_nome = "Barbeiro";
$stmt = $conn->prepare("SELECT nome FROM barbeiros WHERE id = ?");
$stmt->bind_param("i", $barbeiro_id);
$stmt->execute();
$r = $stmt->get_result();
if ($r && $r->num_rows > 0) $barbeiro_nome = $r->fetch_assoc()["nome"];
$stmt->close();

date_default_timezone_set("Europe/Lisbon");

$hoje = new DateTime("today");
$ano = (int)$hoje->format("Y");
$mes = (int)$hoje->format("m");

$meses = [
1=>"Janeiro","Fevereiro","Março","Abril","Maio","Junho",
"Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"
];

$mesNome = $meses[(int)$hoje->format("n")];

$primeiroDiaMes = new DateTime(sprintf("%04d-%02d-01", $ano, $mes));
$ultimoDiaMes = new DateTime($primeiroDiaMes->format("Y-m-t"));

$dias = [];

for ($d = clone $primeiroDiaMes; $d <= $ultimoDiaMes; $d->modify("+1 day")) {
    if ($d < $hoje) continue;

    $diaSemanaNum = (int)$d->format("w");

    $dias[] = [
        "date"=>$d->format("Y-m-d"),
        "dow"=>$diaSemanaNum,
        "day"=>$d->format("d")
    ];
}

$nomesSemana = ["Dom","Seg","Ter","Qua","Qui","Sex","Sáb"];

$erro="";

if ($_SERVER["REQUEST_METHOD"]==="POST"){
    $dia=$_POST["dia"] ?? "";

    if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$dia)){
        $erro="Escolhe um dia válido.";
    }else{
        $diaObj=DateTime::createFromFormat("Y-m-d",$dia);

        if(!$diaObj){
            $erro="Escolhe um dia válido.";
        }elseif($diaObj<$hoje){
            $erro="Não podes escolher um dia no passado.";
        }else{
            $_SESSION["marcacao_dia"]=$dia;
            unset($_SESSION["marcacao_hora"]);
            unset($_SESSION["marcacao_tipo"]);
            header("Location: escolher_hora.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Escolher Dia - Light's Barber</title>
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
font-family:'Playfair Display',serif;
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
max-width:820px;
background:rgba(0,0,0,0.85);
border:1px solid #1a1a1a;
padding:40px 30px;
text-align:center;
}
h2{
font-family:'Playfair Display',serif;
font-size:34px;
margin-bottom:10px;
}
.sub{
color:#ccc;
margin-bottom:18px;
font-size:14px;
}
.erro{
color:#ff4d4d;
margin-bottom:12px;
}
.grid{
display:grid;
grid-template-columns:repeat(7,1fr);
gap:10px;
margin-top:14px;
}
.daybtn{
padding:14px 8px;
background:#111;
border:1px solid #222;
color:#fff;
cursor:pointer;
transition:.2s;
border-radius:14px;
}
.daybtn:hover{
border-color:#555;
transform:translateY(-2px);
}
.dow{
font-size:12px;
color:#bbb;
margin-bottom:6px;
}
.num{
font-size:18px;
font-weight:700;
}
.actions{
margin-top:18px;
display:flex;
gap:10px;
justify-content:center;
flex-wrap:wrap;
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
@media(max-width:820px){
.grid{grid-template-columns:repeat(5,1fr);}
}
@media(max-width:520px){
.grid{grid-template-columns:repeat(4,1fr);}
}
@media(max-width:800px){
.logo{position:static;transform:none;}
.menu-toggle{display:block;}
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
.nav-links.active{display:flex;}
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

<h2>2/4 — Escolher Dia</h2>

<div class="sub">
Barbeiro: <strong><?php echo htmlspecialchars($barbeiro_nome); ?></strong> — <?php echo $mesNome; ?>
</div>

<?php
if($erro!=""){
echo "<div class='erro'>$erro</div>";
}
?>

<form method="POST">
<div class="grid">
<?php foreach($dias as $d): ?>
<button class="daybtn" type="submit" name="dia" value="<?php echo htmlspecialchars($d["date"]); ?>">
<div class="dow"><?php echo $nomesSemana[$d["dow"]]; ?></div>
<div class="num"><?php echo htmlspecialchars($d["day"]); ?></div>
</button>
<?php endforeach; ?>
</div>

<div class="actions">
<a class="btn-outline" href="marcar_corte.php">Voltar</a>
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
</script>

</body>
</html>