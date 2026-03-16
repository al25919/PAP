<?php
session_start();
include("ligacao.php");

// Proteção de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Só barbeiros podem entrar
if (!isset($_SESSION['user_tipo']) || $_SESSION['user_tipo'] !== 'barbeiro') {
    header("Location: index.php");
    exit;
}

$user_nome = $_SESSION['user_nome'] ?? "Barbeiro";

date_default_timezone_set("Europe/Lisbon");

// Atualizar automaticamente marcações passadas
$agora = date("Y-m-d H:i:s");

$updateSql = "
UPDATE marcacoes
SET estado = 'concluido'
WHERE estado = 'pendente'
AND data_hora < ?
";

$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("s", $agora);
$updateStmt->execute();
$updateStmt->close();

// Buscar todas as marcações
$sql = "
SELECT 
    m.id,
    m.data_hora,
    m.tipo_corte,
    m.preco,
    m.estado,
    u.nome AS cliente_nome,
    b.nome AS barbeiro_nome
FROM marcacoes m
LEFT JOIN utilizadores u ON m.user_id = u.id
LEFT JOIN barbeiros b ON m.barbeiro_id = b.id
ORDER BY 
    CASE 
        WHEN m.estado = 'pendente' THEN 1
        WHEN m.estado = 'concluido' THEN 2
        WHEN m.estado = 'cancelado' THEN 3
        ELSE 4
    END,
    m.data_hora ASC
";

$result = $conn->query($sql);

$marcacoes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $marcacoes[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard do Barbeiro - Light's Barber</title>

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
padding:130px 20px 60px;
max-width:1200px;
margin:auto;
}

.page-title{
text-align:center;
margin-bottom:30px;
}

.page-title h1{
font-family:'Playfair Display', serif;
font-size:38px;
margin-bottom:8px;
}

.page-title p{
color:#ccc;
font-size:15px;
}

.tabela-box{
background:rgba(0,0,0,0.85);
border:1px solid #1a1a1a;
border-radius:16px;
overflow:hidden;
}

.tabela-scroll{
overflow-x:auto;
}

table{
width:100%;
border-collapse:collapse;
min-width:1050px;
}

thead{
background:#0f0f0f;
}

th, td{
padding:16px 14px;
text-align:left;
border-bottom:1px solid #1a1a1a;
font-size:14px;
}

th{
color:#ddd;
font-weight:600;
}

td{
color:#fff;
}

tr:hover td{
background:rgba(255,255,255,0.02);
}

.estado{
display:inline-block;
padding:6px 10px;
border-radius:999px;
font-size:12px;
font-weight:600;
text-transform:capitalize;
}

.estado-pendente{
background:rgba(255,193,7,0.15);
color:#ffd666;
border:1px solid rgba(255,193,7,0.30);
}

.estado-confirmado{
background:rgba(0,123,255,0.15);
color:#7db7ff;
border:1px solid rgba(0,123,255,0.30);
}

.estado-concluido{
background:rgba(40,167,69,0.15);
color:#7dff9b;
border:1px solid rgba(40,167,69,0.30);
}

.estado-cancelado{
background:rgba(220,53,69,0.15);
color:#ff8a96;
border:1px solid rgba(220,53,69,0.30);
}

.sem-marcacoes{
background:rgba(0,0,0,0.85);
border:1px solid #1a1a1a;
border-radius:16px;
padding:40px 25px;
text-align:center;
}

.sem-marcacoes h2{
font-family:'Playfair Display', serif;
font-size:28px;
margin-bottom:10px;
}

.sem-marcacoes p{
color:#ccc;
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
</style>
</head>

<body>

<header>
<nav class="navbar">

<button class="menu-toggle" id="menuToggle">☰</button>

<div class="nav-links" id="navLinks">
<a href="dashboard_barbeiro.php">Dashboard</a>
<a href="index.php">Site</a>
</div>

<div class="logo">LIGHT'S BARBER</div>

<div class="nav-links">
<span style="font-size:14px;">Barbeiro: <?php echo htmlspecialchars($user_nome); ?></span>
<a href="logout.php">Encerrar Sessão</a>
</div>

</nav>
</header>

<section class="container">

<div class="page-title">
<h1>Dashboard do Barbeiro</h1>
<p>Aqui podes acompanhar todas as marcações da barbearia.</p>
</div>

<?php if (count($marcacoes) > 0): ?>

<div class="tabela-box">
<div class="tabela-scroll">
<table>
<thead>
<tr>
<th>Cliente</th>
<th>Barbeiro</th>
<th>Data</th>
<th>Hora</th>
<th>Serviço</th>
<th>Preço</th>
<th>Estado</th>
</tr>
</thead>
<tbody>

<?php foreach($marcacoes as $m): ?>
<tr>
<td><?php echo htmlspecialchars($m['cliente_nome'] ?? 'Não definido'); ?></td>
<td><?php echo htmlspecialchars($m['barbeiro_nome'] ?? 'Não definido'); ?></td>
<td><?php echo date("d/m/Y", strtotime($m['data_hora'])); ?></td>
<td><?php echo date("H:i", strtotime($m['data_hora'])); ?></td>
<td><?php echo htmlspecialchars($m['tipo_corte']); ?></td>
<td><?php echo htmlspecialchars($m['preco']); ?></td>
<td>
<?php
$estado = $m['estado'] ?? 'pendente';
$classeEstado = "estado-" . strtolower($estado);
?>
<span class="estado <?php echo $classeEstado; ?>">
<?php echo htmlspecialchars($estado); ?>
</span>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>
</div>

<?php else: ?>

<div class="sem-marcacoes">
<h2>Ainda não existem marcações</h2>
<p>Quando houver marcações, vão aparecer aqui.</p>
</div>

<?php endif; ?>

</section>

<script>
const toggle = document.getElementById("menuToggle");
const nav = document.getElementById("navLinks");

toggle.addEventListener("click", () => {
  nav.classList.toggle("active");
});
</script>

</body>
</html>