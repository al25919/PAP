<?php
session_start();
include("ligacao.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$user_nome = $_SESSION['user_nome'] ?? "Utilizador";

date_default_timezone_set("Europe/Lisbon");

if (isset($_POST['cancelar_id'])) {
    $id = (int)$_POST['cancelar_id'];

    $stmtCheck = $conn->prepare("
        SELECT data_hora, estado
        FROM marcacoes
        WHERE id = ?
        AND user_id = ?
        LIMIT 1
    ");
    $stmtCheck->bind_param("ii", $id, $user_id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    $podeCancelar = false;

    if ($resCheck && $resCheck->num_rows > 0) {
        $marc = $resCheck->fetch_assoc();

        if ($marc['estado'] === 'pendente') {
            $agoraTs = time();
            $marcacaoTs = strtotime($marc['data_hora']);
            $diffSegundos = $marcacaoTs - $agoraTs;

            if ($diffSegundos > (4 * 60 * 60)) {
                $podeCancelar = true;
            }
        }
    }

    $stmtCheck->close();

    if ($podeCancelar) {
        $stmt = $conn->prepare("
            UPDATE marcacoes
            SET estado = 'cancelado'
            WHERE id = ?
            AND user_id = ?
            AND estado = 'pendente'
        ");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: minhas_marcacoes.php");
    exit;
}

$agora = date("Y-m-d H:i:s");

$updateSql = "
UPDATE marcacoes
SET estado = 'concluido'
WHERE user_id = ?
AND estado = 'pendente'
AND data_hora < ?
";

$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("is", $user_id, $agora);
$updateStmt->execute();
$updateStmt->close();

$sql = "
SELECT 
    m.id,
    m.data_hora,
    m.tipo_corte,
    m.preco,
    m.estado,
    b.nome AS barbeiro_nome
FROM marcacoes m
LEFT JOIN barbeiros b ON m.barbeiro_id = b.id
WHERE m.user_id = ?
AND m.estado != 'cancelado'
ORDER BY 
    CASE 
        WHEN m.estado = 'pendente' THEN 1
        WHEN m.estado = 'concluido' THEN 2
        ELSE 3
    END,
    m.data_hora ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$marcacoes = [];
while ($row = $result->fetch_assoc()) {
    $marcacoes[] = $row;
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Minhas Marcações - Light's Barber</title>

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
max-width:1100px;
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

.info-cancelamento{
max-width:900px;
margin:0 auto 18px;
padding:12px 14px;
border:1px solid #2a2a2a;
border-radius:12px;
background:rgba(255,255,255,0.03);
color:#ccc;
font-size:13px;
text-align:center;
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
min-width:950px;
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

.btn-cancelar{
background:#ff4d4d;
border:none;
color:white;
padding:8px 12px;
cursor:pointer;
border-radius:8px;
font-size:12px;
font-weight:600;
transition:0.3s;
}

.btn-cancelar:hover{
background:#cc0000;
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
margin-bottom:20px;
}

.btn{
display:inline-block;
padding:12px 18px;
background:#fff;
color:#000;
text-decoration:none;
font-weight:600;
border-radius:12px;
transition:0.3s;
}

.btn:hover{
background:#ccc;
}

.modal-backdrop{
position:fixed;
inset:0;
background:rgba(0,0,0,0.7);
display:none;
align-items:center;
justify-content:center;
z-index:5000;
padding:20px;
}

.modal{
background:#111;
padding:30px;
border-radius:16px;
text-align:center;
width:100%;
max-width:460px;
border:1px solid #222;
}

.modal p{
color:#ddd;
line-height:1.6;
margin-bottom:20px;
}

.modal-actions{
display:flex;
justify-content:center;
gap:10px;
flex-wrap:wrap;
}

.modal button{
padding:10px 14px;
border:none;
cursor:pointer;
border-radius:10px;
font-weight:600;
}

.btn-confirm{
background:#ff4d4d;
color:white;
}

.btn-confirm:hover{
background:#cc0000;
}

.btn-voltar{
background:#333;
color:white;
}

.btn-voltar:hover{
background:#444;
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

<span style="font-size:14px;">Olá, <?php echo htmlspecialchars($user_nome); ?></span>
<a href="logout.php">Encerrar Sessão</a>
</div>

</nav>
</header>

<section class="container">

<div class="page-title">
<h1>Minhas Marcações</h1>
<p>Aqui podes ver todas as tuas marcações.</p>
</div>

<div class="info-cancelamento">
Podes cancelar uma marcação apenas até 4 horas antes do horário marcado.
</div>

<?php if (count($marcacoes) > 0): ?>

<div class="tabela-box">
<div class="tabela-scroll">
<table>
<thead>
<tr>
<th>Barbeiro</th>
<th>Data</th>
<th>Hora</th>
<th>Serviço</th>
<th>Preço</th>
<th>Estado</th>
<th>Ação</th>
</tr>
</thead>
<tbody>

<?php foreach($marcacoes as $m): ?>
<tr>

<td><?php echo htmlspecialchars($m['barbeiro_nome'] ?? 'Não definido'); ?></td>
<td><?php echo date("d/m/Y", strtotime($m['data_hora'])); ?></td>
<td><?php echo date("H:i", strtotime($m['data_hora'])); ?></td>
<td><?php echo htmlspecialchars($m['tipo_corte']); ?></td>
<td><?php echo htmlspecialchars($m['preco']); ?></td>

<td>
<?php
$estado = $m['estado'] ?? 'pendente';
$classe = "estado-" . strtolower($estado);
?>
<span class="estado <?php echo $classe; ?>">
<?php echo htmlspecialchars($estado); ?>
</span>
</td>

<td>
<?php
$podeCancelar = false;

if ($estado === "pendente") {
    $agoraTs = time();
    $marcacaoTs = strtotime($m['data_hora']);
    $diffSegundos = $marcacaoTs - $agoraTs;

    if ($diffSegundos > (4 * 60 * 60)) {
        $podeCancelar = true;
    }
}
?>

<?php if($podeCancelar): ?>
<form method="POST" class="formCancelar" style="margin:0;">
<input type="hidden" name="cancelar_id" value="<?php echo (int)$m['id']; ?>">
<button type="button" class="btn-cancelar abrirModal">Cancelar</button>
</form>
<?php else: ?>
—
<?php endif; ?>
</td>

</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>
</div>

<?php else: ?>

<div class="sem-marcacoes">
<h2>Ainda não tens marcações</h2>
<p>Quando fizeres uma marcação, ela vai aparecer aqui.</p>
<a href="marcar_corte.php" class="btn">Fazer Marcação</a>
</div>

<?php endif; ?>

</section>

<div class="modal-backdrop" id="modal">
<div class="modal">
<p>Tens a certeza que queres cancelar a marcação?</p>

<div class="modal-actions">
<button class="btn-confirm" id="confirmarCancelamento">Sim, cancelar</button>
<button class="btn-voltar" id="fecharModal">Voltar</button>
</div>
</div>
</div>

<script>
const toggle = document.getElementById("menuToggle");
const nav = document.getElementById("navLinks");

toggle.addEventListener("click", () => {
  nav.classList.toggle("active");
});

const modal = document.getElementById("modal");
const botoes = document.querySelectorAll(".abrirModal");

let formSelecionado = null;

botoes.forEach(btn => {
  btn.addEventListener("click", () => {
    formSelecionado = btn.closest(".formCancelar");
    modal.style.display = "flex";
  });
});

document.getElementById("fecharModal").onclick = () => {
  modal.style.display = "none";
  formSelecionado = null;
};

document.getElementById("confirmarCancelamento").onclick = () => {
  if(formSelecionado){
    formSelecionado.submit();
  }
};

modal.addEventListener("click", (e) => {
  if (e.target === modal) {
    modal.style.display = "none";
    formSelecionado = null;
  }
});
</script>

</body>
</html>