<?php
session_start();
include("ligacao.php");

// Proteção login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['user_tipo']) || $_SESSION['user_tipo'] !== 'barbeiro') {
    header("Location: index.php");
    exit;
}

$user_nome = $_SESSION['user_nome'] ?? "Barbeiro";

date_default_timezone_set("Europe/Lisbon");

$diaSelecionado = $_GET['data'] ?? date("Y-m-d");


// Atualizar marcações antigas automaticamente
$agora = date("Y-m-d H:i:s");

$updateSql = "
UPDATE marcacoes
SET estado='concluido'
WHERE estado='pendente'
AND data_hora < ?
";

$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("s",$agora);
$updateStmt->execute();
$updateStmt->close();


// ===== ESTATÍSTICAS DIA =====

$sqlHoje="
SELECT COUNT(*) as total
FROM marcacoes
WHERE DATE(data_hora)='$diaSelecionado'
AND estado!='cancelado'
";

$resHoje=$conn->query($sqlHoje);
$rowHoje=$resHoje->fetch_assoc();
$marcacoesHoje=$rowHoje['total']??0;


// Receita dia
$sqlReceita="
SELECT SUM(preco) as receita
FROM marcacoes
WHERE DATE(data_hora)='$diaSelecionado'
AND estado!='cancelado'
";

$resReceita=$conn->query($sqlReceita);
$rowReceita=$resReceita->fetch_assoc();
$receitaDia=$rowReceita['receita']??0;


// ===== SERVIÇOS DO DIA =====

$sqlCorte="
SELECT COUNT(*) as total
FROM marcacoes
WHERE DATE(data_hora)='$diaSelecionado'
AND estado!='cancelado'
AND tipo_corte LIKE '%Corte%'
AND tipo_corte NOT LIKE '%Barba%'
";

$resCorte=$conn->query($sqlCorte);
$rowCorte=$resCorte->fetch_assoc();
$corte=$rowCorte['total']??0;


$sqlBarba="
SELECT COUNT(*) as total
FROM marcacoes
WHERE DATE(data_hora)='$diaSelecionado'
AND estado!='cancelado'
AND tipo_corte LIKE '%Barba%'
AND tipo_corte NOT LIKE '%Corte%'
";

$resBarba=$conn->query($sqlBarba);
$rowBarba=$resBarba->fetch_assoc();
$barba=$rowBarba['total']??0;


$sqlCB="
SELECT COUNT(*) as total
FROM marcacoes
WHERE DATE(data_hora)='$diaSelecionado'
AND estado!='cancelado'
AND tipo_corte LIKE '%Corte%'
AND tipo_corte LIKE '%Barba%'
";

$resCB=$conn->query($sqlCB);
$rowCB=$resCB->fetch_assoc();
$corteBarba=$rowCB['total']??0;


// ===== RECEITA SEMANA =====

$sqlSemana="
SELECT SUM(preco) as receita
FROM marcacoes
WHERE YEARWEEK(data_hora,1)=YEARWEEK(CURDATE(),1)
AND estado!='cancelado'
";

$resSemana=$conn->query($sqlSemana);
$rowSemana=$resSemana->fetch_assoc();
$receitaSemana=$rowSemana['receita']??0;


$sqlGrafico="
SELECT DAYNAME(data_hora) as dia, SUM(preco) as total
FROM marcacoes
WHERE YEARWEEK(data_hora,1)=YEARWEEK(CURDATE(),1)
AND estado!='cancelado'
GROUP BY DAYNAME(data_hora)
";

$resGrafico=$conn->query($sqlGrafico);

$dias=[
"Monday"=>0,
"Tuesday"=>0,
"Wednesday"=>0,
"Thursday"=>0,
"Friday"=>0,
"Saturday"=>0
];

while($row=$resGrafico->fetch_assoc()){
$dias[$row['dia']]=$row['total'];
}


// ===== AGENDA =====

$sqlAgenda="
SELECT 
m.id,
m.data_hora,
u.nome,
m.tipo_corte,
m.estado
FROM marcacoes m
LEFT JOIN utilizadores u ON m.user_id=u.id
WHERE DATE(m.data_hora)='$diaSelecionado'
ORDER BY m.data_hora ASC
";

$resAgenda=$conn->query($sqlAgenda);

$agenda=[];
while($row=$resAgenda->fetch_assoc()){
$agenda[]=$row;
}

?>

<!DOCTYPE html>
<html lang="pt">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard do Barbeiro</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
background:#000;
color:#fff;
font-family:'Poppins',sans-serif;
margin:0;
}

header{
position:fixed;
width:100%;
top:0;
background:#000;
border-bottom:1px solid #1a1a1a;
z-index:1000;
}

.navbar{
max-width:1200px;
margin:auto;
padding:16px 25px;
display:flex;
justify-content:space-between;
align-items:center;
}

.logo{
font-size:22px;
}

.nav-links{
display:flex;
gap:20px;
}

.nav-links a{
color:white;
text-decoration:none;
}

.container{
padding:140px 20px;
max-width:1200px;
margin:auto;
}

.filtro-data{
display:flex;
justify-content:center;
gap:10px;
margin-bottom:30px;
}

.input-data{
background:#111;
color:white;
border:1px solid #1a1a1a;
padding:8px 12px;
border-radius:8px;
}

.btn-ver{
background:#1a1a1a;
color:white;
border:1px solid #333;
padding:8px 16px;
border-radius:8px;
cursor:pointer;
}

.stats{
display:flex;
gap:20px;
flex-wrap:wrap;
margin-bottom:40px;
}

.stat-card{
flex:1;
min-width:180px;
background:#111;
border:1px solid #1a1a1a;
border-radius:14px;
padding:20px;
text-align:center;
}

.chart-box{
background:#111;
border:1px solid #1a1a1a;
border-radius:14px;
padding:25px;
margin-bottom:40px;
}

.agenda{
background:#111;
border:1px solid #1a1a1a;
border-radius:14px;
padding:25px;
}

.pesquisa-agenda{
width:100%;
background:#111;
color:white;
border:1px solid #1a1a1a;
padding:10px;
border-radius:8px;
margin-bottom:15px;
}

.agenda-item{
padding:10px 0;
border-bottom:1px solid #222;
}

.estado{
font-size:12px;
margin-left:10px;
padding:3px 8px;
border-radius:6px;
}

.pendente{background:#ffc10733;color:#ffd666;}
.concluido{background:#28a74533;color:#7dff9b;}
.cancelado{background:#dc354533;color:#ff8a96;}

.btn-concluir{
background:#28a745;
color:white;
padding:4px 8px;
border-radius:6px;
font-size:12px;
margin-left:10px;
text-decoration:none;
}

.btn-cancelar{
background:#dc3545;
color:white;
padding:4px 8px;
border-radius:6px;
font-size:12px;
margin-left:10px;
text-decoration:none;
}

</style>

</head>

<body>

<header>

<nav class="navbar">

<div class="nav-links">
<a href="dashboard_barbeiro.php">Dashboard</a>
<a href="index.php">Site</a>
</div>

<div class="logo">LIGHT'S BARBER</div>

<div class="nav-links">
<span><?php echo $user_nome; ?></span>
<a href="logout.php">Logout</a>
</div>

</nav>

</header>


<section class="container">


<form method="GET" class="filtro-data">

<input type="date" name="data" value="<?php echo $diaSelecionado; ?>" class="input-data">

<button type="submit" class="btn-ver">Ver</button>

</form>


<div class="stats">

<div class="stat-card">
<h2><?php echo $marcacoesHoje; ?></h2>
<p>Marcações Hoje</p>
</div>

<div class="stat-card">
<h2><?php echo $receitaDia; ?>€</h2>
<p>Receita do Dia</p>
</div>

<div class="stat-card">
<h2><?php echo $receitaSemana; ?>€</h2>
<p>Receita da Semana</p>
</div>

</div>


<div class="chart-box">
<h3>Serviços do Dia</h3>
<canvas id="graficoServicos"></canvas>
</div>


<div class="chart-box">
<h3>Receita da Semana</h3>
<canvas id="graficoSemana"></canvas>
</div>


<div class="agenda">

<h2>Agenda do Dia</h2>

<input type="text" id="pesquisaCliente" placeholder="Pesquisar cliente..." class="pesquisa-agenda">

<?php foreach($agenda as $a): ?>

<div class="agenda-item" data-cliente="<?php echo strtolower($a['nome']); ?>">

<strong><?php echo date("H:i",strtotime($a['data_hora'])); ?></strong>

- <?php echo htmlspecialchars($a['nome']); ?>

(<?php echo $a['tipo_corte']; ?>)

<span class="estado <?php echo $a['estado']; ?>">
<?php echo $a['estado']; ?>
</span>

<?php if($a['estado']=="pendente"): ?>

<a class="btn-concluir" href="concluir_marcacao.php?id=<?php echo $a['id']; ?>">Concluir</a>

<a class="btn-cancelar" href="cancelar_marcacao.php?id=<?php echo $a['id']; ?>">Cancelar</a>

<?php endif; ?>

</div>

<?php endforeach; ?>

</div>


</section>


<script>

new Chart(document.getElementById('graficoServicos'),{
type:'bar',
data:{
labels:['Corte','Barba','Corte + Barba'],
datasets:[{
label:'Serviços',
data:[
<?php echo $corte;?>,
<?php echo $barba;?>,
<?php echo $corteBarba;?>
],
backgroundColor:['#4e79a7','#59a14f','#f28e2b']
}]
}
});


new Chart(document.getElementById('graficoSemana'),{
type:'bar',
data:{
labels:['Seg','Ter','Qua','Qui','Sex','Sab'],
datasets:[{
label:'Receita (€)',
data:[
<?php echo $dias['Monday']; ?>,
<?php echo $dias['Tuesday']; ?>,
<?php echo $dias['Wednesday']; ?>,
<?php echo $dias['Thursday']; ?>,
<?php echo $dias['Friday']; ?>,
<?php echo $dias['Saturday']; ?>
],
backgroundColor:'#59a14f'
}]
}
});


const input=document.getElementById("pesquisaCliente");
const agendaItems=document.querySelectorAll(".agenda-item");

input.addEventListener("keyup",function(){

let pesquisa=input.value.toLowerCase();

agendaItems.forEach(function(item){

let cliente=item.getAttribute("data-cliente");

if(cliente.includes(pesquisa)){
item.style.display="block";
}else{
item.style.display="none";
}

});

});

</script>

</body>
</html>