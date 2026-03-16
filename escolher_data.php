<?php
session_start();
include("ligacao.php");

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
if (!isset($_SESSION["marcacao_barbeiro_id"])) { header("Location: marcar_corte.php"); exit; }
if (!isset($_SESSION["marcacao_dia"])) { header("Location: escolher_dia.php"); exit; }

$user_nome = $_SESSION['user_nome'] ?? "Utilizador";
$barbeiro_id = (int)$_SESSION["marcacao_barbeiro_id"];
$dia = $_SESSION["marcacao_dia"];

// Nome do barbeiro
$barbeiro_nome = "Barbeiro";
$stmt = $conn->prepare("SELECT nome FROM barbeiros WHERE id = ?");
$stmt->bind_param("i", $barbeiro_id);
$stmt->execute();
$r = $stmt->get_result();
if ($r && $r->num_rows > 0) $barbeiro_nome = $r->fetch_assoc()["nome"];
$stmt->close();

// Buscar horas já ocupadas nesse dia para esse barbeiro (ignorar canceladas)
$ocupadas = [];
$stmt = $conn->prepare("
  SELECT DATE_FORMAT(data_hora, '%H:%i') AS hora
  FROM marcacoes
  WHERE barbeiro_id = ?
    AND DATE(data_hora) = ?
    AND (estado IS NULL OR estado <> 'cancelado')
");
$stmt->bind_param("is", $barbeiro_id, $dia);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  $ocupadas[$row["hora"]] = true;
}
$stmt->close();

// Gerar slots 09:00 -> 17:30 (último início às 17:30 para terminar às 18:00)
$slots = [];
$start = new DateTime($dia . " 09:00");
$end = new DateTime($dia . " 18:00");
$interval = new DateInterval("PT30M");

for ($t = clone $start; $t < $end; $t->add($interval)) {
  $hora = $t->format("H:i");
  $slots[] = [
    "hora" => $hora,
    "livre" => !isset($ocupadas[$hora])
  ];
}

$erro = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $hora = $_POST["hora"] ?? "";

  if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
    $erro = "Escolhe uma hora válida.";
  } elseif (!in_array($hora, array_column($slots, "hora"), true)) {
    $erro = "Hora inválida.";
  } elseif (isset($ocupadas[$hora])) {
    $erro = "Essa hora já está ocupada.";
  } else {
    $_SESSION["marcacao_hora"] = $hora;
    unset($_SESSION["marcacao_tipo"]);
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
body{background:#000;color:#fff;font-family:'Poppins',sans-serif;overflow-x:hidden;}
body::before{content:"";position:fixed;inset:0;background-image:url('Imagem/Logo.png');background-repeat:no-repeat;background-position:center 35%;background-size:min(80vw,700px);opacity:0.10;z-index:-1;pointer-events:none;}
header{position:fixed;width:100%;top:0;background:rgba(0,0,0,0.85);backdrop-filter:blur(8px);border-bottom:1px solid #1a1a1a;z-index:1000;}
.navbar{max-width:1200px;margin:auto;padding:16px 25px;display:flex;align-items:center;justify-content:space-between;position:relative;}
.logo{font-family:'Playfair Display',serif;letter-spacing:3px;font-size:22px;position:absolute;left:50%;transform:translateX(-50%);}
.nav-links{display:flex;gap:20px;align-items:center;}
.nav-links a{text-decoration:none;color:white;font-size:14px;transition:0.3s;}
.nav-links a:hover{color:#cfcfcf;}
.menu-toggle{display:none;font-size:26px;background:none;border:none;color:white;cursor:pointer;}

.container{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:120px 20px 60px;}
.box{width:100%;max-width:820px;background:rgba(0,0,0,0.85);border:1px solid #1a1a1a;padding:40px 30px;text-align:center;}
h2{font-family:'Playfair Display',serif;font-size:34px;margin-bottom:10px;}
.sub{color:#ccc;margin-bottom:18px;font-size:14px;}
.erro{color:#ff4d4d;margin-bottom:12px;}

.grid{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-top:10px;}
.timebtn{
  padding:12px 8px;background:#111;border:1px solid #222;color:#fff;cursor:pointer;
  transition:.2s;font-weight:600;
}
.timebtn:hover{border-color:#555;}
.timebtn.off{opacity:.35;cursor:not-allowed;}
.actions{margin-top:18px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;}
.btn{padding:12px 18px;border:none;cursor:pointer;font-weight:600;background:#fff;color:#000;transition:.2s;}
.btn:hover{background:#ccc;}
.btn-outline{padding:12px 18px;border:1px solid #333;background:transparent;color:#fff;cursor:pointer;text-decoration:none;display:inline-block;}
.btn-outline:hover{border-color:#555;}

@media(max-width:800px){
  .logo{position:static;transform:none;}
  .menu-toggle{display:block;}
  .nav-links{position:absolute;top:100%;left:0;width:100%;background:rgba(0,0,0,0.95);flex-direction:column;align-items:center;gap:25px;padding:30px 0;display:none;}
  .nav-links.active{display:flex;}
}
@media(max-width:820px){ .grid{grid-template-columns:repeat(4,1fr);} }
@media(max-width:520px){ .grid{grid-template-columns:repeat(3,1fr);} }
</style>
</head>
<body>

<header>
  <nav class="navbar">
    <button class="menu-toggle" id="menuToggle">☰</button>
    <div class="nav-links" id="navLinks">
      <a href="index.php">Início</a>
      <a href="marcar_corte.php">Marcar Corte</a>
      <a href="loja.html">Loja</a>
    </div>
    <div class="logo">LIGHT'S BARBER</div>
    <div class="nav-links">
      <a href="about.html">About Us</a>
      <span style="font-size:14px;">Olá, <?php echo htmlspecialchars($user_nome); ?></span>
      <a href="logout.php">Encerrar Sessão</a>
    </div>
  </nav>
</header>

<section class="container">
  <div class="box">
    <h2>3/4 — Escolher Hora</h2>
    <div class="sub">
      Barbeiro: <strong><?php echo htmlspecialchars($barbeiro_nome); ?></strong> —
      Dia: <strong><?php echo htmlspecialchars($dia); ?></strong>
    </div>

    <?php if($erro!=""){ echo "<div class='erro'>".$erro."</div>"; } ?>

    <form method="POST">
      <div class="grid">
        <?php foreach($slots as $s): ?>
          <?php if($s["livre"]): ?>
            <button class="timebtn" type="submit" name="hora" value="<?php echo htmlspecialchars($s["hora"]); ?>">
              <?php echo htmlspecialchars($s["hora"]); ?>
            </button>
          <?php else: ?>
            <button class="timebtn off" type="button" disabled>
              <?php echo htmlspecialchars($s["hora"]); ?>
            </button>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>

      <div class="actions">
        <a class="btn-outline" href="escolher_dia.php">Voltar</a>
      </div>
    </form>
  </div>
</section>

<script>
const toggle=document.getElementById("menuToggle");
const nav=document.getElementById("navLinks");
toggle.addEventListener("click",()=>nav.classList.toggle("active"));
</script>

</body>
</html>