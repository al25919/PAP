<?php
session_start();
include("ligacao.php");

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
if (!isset($_SESSION["marcacao_barbeiro_id"])) { header("Location: marcar_corte.php"); exit; }
if (!isset($_SESSION["marcacao_dia"])) { header("Location: escolher_dia.php"); exit; }

$user_nome = $_SESSION['user_nome'] ?? "Utilizador";
$barbeiro_id = (int)$_SESSION["marcacao_barbeiro_id"];
$dia = $_SESSION["marcacao_dia"];

date_default_timezone_set("Europe/Lisbon");

/* HORÁRIOS */

$horarios = [];

$hora = new DateTime("09:00");
$fim = new DateTime("18:00");

while ($hora < $fim) {
    $horarios[] = $hora->format("H:i");
    $hora->modify("+30 minutes");
}

/* OCUPADOS */

$horariosOcupados = [];

$stmt = $conn->prepare("
SELECT TIME_FORMAT(data_hora,'%H:%i') as hora 
FROM marcacoes 
WHERE barbeiro_id=? AND DATE(data_hora)=? AND estado!='cancelado'
");

$stmt->bind_param("is",$barbeiro_id,$dia);
$stmt->execute();
$res=$stmt->get_result();

while($row=$res->fetch_assoc()){
    $horariosOcupados[]=$row["hora"];
}

$stmt->close();

/* HORA ATUAL */

$agora = new DateTime();
$horaAtual = $agora->format("H:i");

/* SUBMIT */

if($_SERVER["REQUEST_METHOD"]==="POST"){
    $horaEscolhida=$_POST["hora"] ?? "";

    if($horaEscolhida!=""){
        $_SESSION["marcacao_hora"]=$horaEscolhida;
        header("Location: conclusao.php");
        exit;
    }
}
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
}

.left{ justify-content:flex-start; }
.right{ justify-content:flex-end; }

/* CONTEÚDO */

.container{
min-height:100vh;
display:flex;
align-items:center;
justify-content:center;
padding:130px 20px;
}

.box{
width:100%;
max-width:800px;
background:#0f0f0f;
padding:40px;
border-radius:10px;
text-align:center;
}

.grid{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:10px;
margin-top:20px;
}

.hourbtn{
padding:14px;
background:#111;
border:1px solid #222;
color:#fff;
cursor:pointer;
border-radius:10px;
}

.hourbtn:hover{
border-color:#555;
}

/* BLOQUEADO */

.hourbtn:disabled{
text-decoration:line-through;
opacity:0.4;
cursor:not-allowed;
}

.actions{
margin-top:20px;
}

.btn-outline{
padding:12px 18px;
border:1px solid #333;
background:transparent;
color:#fff;
text-decoration:none;
}
</style>
</head>

<body>

<header>

<nav class="navbar">

<div class="nav-links left">
<a href="index.php">Início</a>
<a href="marcar_corte.php">Marcar Corte</a>
<a href="minhas_marcacoes.php">Minhas Marcações</a>
<a href="loja.html">Loja</a>
</div>

<div class="logo">LIGHT'S BARBER</div>

<div class="nav-links right">

<a href="about.php">About</a>

<?php if (isset($_SESSION['user_tipo']) && $_SESSION['user_tipo'] === 'barbeiro'): ?>
<a href="dashboard_barbeiro.php">Dashboard</a>
<?php endif; ?>

<?php if($user_nome!=""): ?>
<span>Olá, <?php echo htmlspecialchars($user_nome); ?></span>
<a href="logout.php">Encerrar Sessão</a>
<?php else: ?>
<a href="login.php">Login</a>
<?php endif; ?>

</div>

</nav>

</header>

<section class="container">
<div class="box">

<h2>3/4 — Escolher Hora</h2>

<form method="POST">

<div class="grid">

<?php foreach($horarios as $h):

$ocupado = in_array($h,$horariosOcupados);

/* BLOQUEAR HORAS PASSADAS */
$horaPassada = false;

if($dia == date("Y-m-d") && $h <= $horaAtual){
    $horaPassada = true;
}

?>

<button
class="hourbtn"
type="<?php echo ($ocupado || $horaPassada) ? 'button' : 'submit'; ?>"
name="hora"
value="<?php echo $h; ?>"
<?php echo ($ocupado || $horaPassada) ? 'disabled' : ''; ?>
>

<?php echo $h; ?>

</button>

<?php endforeach; ?>

</div>

<div class="actions">
<a class="btn-outline" href="escolher_dia.php">Voltar</a>
</div>

</form>

</div>
</section>

</body>
</html>