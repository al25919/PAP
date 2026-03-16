<?php
session_start();
include("ligacao.php");

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
if (!isset($_SESSION["marcacao_barbeiro_id"])) { header("Location: marcar_corte.php"); exit; }
if (!isset($_SESSION["marcacao_dia"])) { header("Location: escolher_dia.php"); exit; }

if (isset($_POST["hora"]) && !empty($_POST["hora"])) {
    $_SESSION["marcacao_hora"] = $_POST["hora"];
}

if (!isset($_SESSION["marcacao_hora"])) { header("Location: escolher_hora.php"); exit; }

$user_id = (int)$_SESSION["user_id"];
$user_nome = $_SESSION["user_nome"] ?? "Utilizador";

$barbeiro_id = (int)$_SESSION["marcacao_barbeiro_id"];
$dia = $_SESSION["marcacao_dia"];
$hora = $_SESSION["marcacao_hora"];

$data_hora = $dia . " " . $hora . ":00";

$barbeiro_nome = "Barbeiro";
$stmt = $conn->prepare("SELECT nome FROM barbeiros WHERE id = ?");
$stmt->bind_param("i", $barbeiro_id);
$stmt->execute();
$r = $stmt->get_result();
if ($r && $r->num_rows > 0) $barbeiro_nome = $r->fetch_assoc()["nome"];
$stmt->close();

$erro = "";
$sucesso = "";

$servicos = [
  "Corte" => ["preco" => "12€", "emoji" => "✂️"],
  "Corte + Barba" => ["preco" => "17€", "emoji" => "💇‍♂️"],
  "Barba" => ["preco" => "6€", "emoji" => "🧔‍♂️"],
];

unset($_SESSION["marcacao_servico"], $_SESSION["marcacao_preco"]);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["tipo_corte"])) {
  $tipo = $_POST["tipo_corte"] ?? "";

  if (!array_key_exists($tipo, $servicos)) {
    $erro = "Escolhe um serviço válido.";
  } else {

    $preco = $servicos[$tipo]["preco"];

    $stmt = $conn->prepare("
      SELECT id FROM marcacoes
      WHERE barbeiro_id = ?
        AND data_hora = ?
        AND (estado IS NULL OR estado <> 'cancelado')
      LIMIT 1
    ");
    $stmt->bind_param("is", $barbeiro_id, $data_hora);
    $stmt->execute();
    $res = $stmt->get_result();
    $ocupado = ($res && $res->num_rows > 0);
    $stmt->close();

    if ($ocupado) {
      $erro = "Essa hora acabou de ser ocupada. Volta e escolhe outra hora.";
    } else {

      $stmt = $conn->prepare("
        INSERT INTO marcacoes (user_id, data_hora, tipo_corte, preco, barbeiro_id)
        VALUES (?, ?, ?, ?, ?)
      ");
      $stmt->bind_param("isssi", $user_id, $data_hora, $tipo, $preco, $barbeiro_id);

      if ($stmt->execute()) {
        $_SESSION["marcacao_servico"] = $tipo;
        $_SESSION["marcacao_preco"] = $preco;

        $sucesso = "Marcação concluída com sucesso!";

        unset($_SESSION["marcacao_barbeiro_id"], $_SESSION["marcacao_dia"], $_SESSION["marcacao_hora"], $_SESSION["marcacao_tipo"]);
      } else {
        $erro = "Erro ao guardar a marcação.";
      }
      $stmt->close();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Conclusão - Light's Barber</title>
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
.box{width:100%;max-width:640px;background:rgba(0,0,0,0.85);border:1px solid #1a1a1a;padding:40px 30px;text-align:center;}
h2{font-family:'Playfair Display',serif;font-size:34px;margin-bottom:10px;}
.sub{color:#ccc;margin-bottom:18px;font-size:14px;}
.erro{color:#ff4d4d;margin-bottom:12px;}
.sucesso{color:#4dff88;margin-bottom:12px;}

.sum{border:1px solid #222;background:#0b0b0b;padding:14px;margin:12px 0 18px;text-align:left;border-radius:14px;}
.sum div{margin:6px 0;color:#ddd;}

.section-title{ text-align:left; font-size:16px; font-weight:600; margin:4px 0 10px; }

.services{display:flex;flex-direction:column;gap:12px;margin-top:6px;}
.service-item{
  display:flex;align-items:center;justify-content:space-between;gap:14px;
  padding:14px 14px;background:rgba(255,255,255,0.03);
  border:1px solid #222;border-radius:14px;cursor:pointer;transition:.2s;
}
.service-item:hover{border-color:#444;transform:translateY(-1px);}
.service-left{display:flex;align-items:center;gap:14px;min-width:0;}
.service-icon{
  width:44px;height:44px;border-radius:12px;background:#fff;color:#000;
  display:flex;align-items:center;justify-content:center;font-size:20px;flex:0 0 auto;
}
.service-text{text-align:left;min-width:0;}
.service-name{font-size:15px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.service-price{margin-top:4px;font-size:13px;color:#cfcfcf;}
.service-arrow{opacity:0.7;font-size:20px;flex:0 0 auto;}
.service-item.selected{border-color:#fff;background:rgba(255,255,255,0.06);}
.services input[type="radio"]{position:absolute;opacity:0;pointer-events:none;}

.actions{margin-top:16px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;}
.btn{padding:12px 18px;border:none;cursor:pointer;font-weight:600;background:#fff;color:#000;transition:.2s;border-radius:12px;}
.btn:hover{background:#ccc;}
.btn:disabled{opacity:.5;cursor:not-allowed;}
.btn-outline{padding:12px 18px;border:1px solid #333;background:transparent;color:#fff;cursor:pointer;text-decoration:none;display:inline-block;border-radius:12px;}
.btn-outline:hover{border-color:#555;}

.modal-backdrop{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.65);
  display:none;
  align-items:center;
  justify-content:center;
  z-index:5000;
  padding:20px;
}

.modal{
  width:100%;
  max-width:520px;
  background:#0b0b0b;
  border:1px solid #222;
  border-radius:16px;
  overflow:hidden;
}

.modal-header{
  padding:16px 16px 12px;
  border-bottom:1px solid #1a1a1a;
  text-align:left;
}

.modal-title{
  font-family:'Playfair Display',serif;
  font-size:22px;
}

.modal-body{
  padding:16px;
  text-align:left;
  color:#ddd;
}

.modal-row{
  display:flex;
  justify-content:space-between;
  gap:14px;
  padding:10px 0;
  border-bottom:1px dashed rgba(255,255,255,0.10);
}

.modal-row:last-child{border-bottom:none;}

.modal-label{color:#bbb;}
.modal-value{font-weight:600; text-align:right;}

.modal-actions{
  padding:14px 16px 16px;
  display:flex;
  gap:10px;
  justify-content:flex-end;
  border-top:1px solid #1a1a1a;
}

@media(max-width:800px){
  .logo{position:static;transform:none;}
  .menu-toggle{display:block;}
  .nav-links{position:absolute;top:100%;left:0;width:100%;background:rgba(0,0,0,0.95);flex-direction:column;align-items:center;gap:25px;padding:30px 0;display:none;}
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

      <span style="font-size:14px;">Olá, <?php echo htmlspecialchars($user_nome); ?></span>
      <a href="logout.php">Encerrar Sessão</a>
    </div>
  </nav>
</header>

<section class="container">
  <div class="box">
    <h2>4/4 — Conclusão</h2>
    <div class="sub">Confirma os dados e escolhe o serviço</div>

    <?php if($erro!=""){ echo "<div class='erro'>".$erro."</div>"; } ?>
    <?php if($sucesso!=""){ echo "<div class='sucesso'>".$sucesso."</div>"; } ?>

    <div class="sum">
      <div><strong>Barbeiro:</strong> <?php echo htmlspecialchars($barbeiro_nome); ?></div>
      <div><strong>Dia:</strong> <?php echo htmlspecialchars($dia); ?></div>
      <div><strong>Hora:</strong> <?php echo htmlspecialchars($hora); ?></div>

      <?php if($sucesso!="" && isset($_SESSION["marcacao_servico"]) && isset($_SESSION["marcacao_preco"])): ?>
        <div><strong>Serviço:</strong> <?php echo htmlspecialchars($_SESSION["marcacao_servico"]); ?></div>
        <div><strong>Preço:</strong> <?php echo htmlspecialchars($_SESSION["marcacao_preco"]); ?></div>
      <?php else: ?>
        <div id="resumoServico" style="display:none;"><strong>Serviço:</strong> <span id="servicoNome"></span></div>
        <div id="resumoPreco" style="display:none;"><strong>Preço:</strong> <span id="servicoPreco"></span></div>
      <?php endif; ?>
    </div>

    <?php if($sucesso==""): ?>
      <form method="POST" id="formServico">
        <div class="section-title">Serviços</div>

        <div class="services">
          <?php foreach($servicos as $nome => $info): ?>
            <label class="service-item"
                   data-servico="<?php echo htmlspecialchars($nome); ?>"
                   data-preco="<?php echo htmlspecialchars($info["preco"]); ?>">
              <input type="radio" name="tipo_corte" value="<?php echo htmlspecialchars($nome); ?>" required>
              <div class="service-left">
                <div class="service-icon"><?php echo $info["emoji"]; ?></div>
                <div class="service-text">
                  <div class="service-name"><?php echo htmlspecialchars($nome); ?></div>
                  <div class="service-price"><?php echo htmlspecialchars($info["preco"]); ?></div>
                </div>
              </div>
              <div class="service-arrow">›</div>
            </label>
          <?php endforeach; ?>
        </div>

        <div class="actions">
          <a class="btn-outline" href="escolher_hora.php">Voltar</a>
          <button type="submit" class="btn" id="btnConcluir" disabled>Concluir Marcação</button>
        </div>
      </form>
    <?php else: ?>
      <div class="actions">
        <a class="btn" href="index.php" style="text-decoration:none;display:inline-block;">Voltar ao Início</a>
        <a class="btn-outline" href="minhas_marcacoes.php">Ver Minhas Marcações</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<div class="modal-backdrop" id="modalBackdrop" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-header">
      <div class="modal-title">Confirmar Marcação</div>
    </div>

    <div class="modal-body">
      <div class="modal-row">
        <div class="modal-label">Barbeiro</div>
        <div class="modal-value"><?php echo htmlspecialchars($barbeiro_nome); ?></div>
      </div>
      <div class="modal-row">
        <div class="modal-label">Dia</div>
        <div class="modal-value"><?php echo htmlspecialchars($dia); ?></div>
      </div>
      <div class="modal-row">
        <div class="modal-label">Hora</div>
        <div class="modal-value"><?php echo htmlspecialchars($hora); ?></div>
      </div>
      <div class="modal-row">
        <div class="modal-label">Serviço</div>
        <div class="modal-value" id="mServico">—</div>
      </div>
      <div class="modal-row">
        <div class="modal-label">Preço</div>
        <div class="modal-value" id="mPreco">—</div>
      </div>
    </div>

    <div class="modal-actions">
      <button class="btn-outline" type="button" id="btnCancelarModal">Cancelar</button>
      <button class="btn" type="button" id="btnConfirmarModal">Confirmar</button>
    </div>
  </div>
</div>

<script>
const toggle=document.getElementById("menuToggle");
const nav=document.getElementById("navLinks");
toggle.addEventListener("click",()=>nav.classList.toggle("active"));

const serviceItems = document.querySelectorAll(".service-item");
const btnConcluir = document.getElementById("btnConcluir");

const resumoServico = document.getElementById("resumoServico");
const resumoPreco = document.getElementById("resumoPreco");
const servicoNome = document.getElementById("servicoNome");
const servicoPreco = document.getElementById("servicoPreco");

let escolhidoNome = "";
let escolhidoPreco = "";

serviceItems.forEach(item => {
  item.addEventListener("click", () => {
    serviceItems.forEach(i => i.classList.remove("selected"));
    item.classList.add("selected");

    escolhidoNome = item.dataset.servico || "";
    escolhidoPreco = item.dataset.preco || "";

    if (btnConcluir) btnConcluir.disabled = false;

    if (resumoServico && resumoPreco && servicoNome && servicoPreco) {
      servicoNome.textContent = escolhidoNome;
      servicoPreco.textContent = escolhidoPreco;
      resumoServico.style.display = "block";
      resumoPreco.style.display = "block";
    }
  });
});

const form = document.getElementById("formServico");
const backdrop = document.getElementById("modalBackdrop");
const btnCancelarModal = document.getElementById("btnCancelarModal");
const btnConfirmarModal = document.getElementById("btnConfirmarModal");

const mServico = document.getElementById("mServico");
const mPreco = document.getElementById("mPreco");

function abrirModal(){
  if (!backdrop) return;
  mServico.textContent = escolhidoNome || "—";
  mPreco.textContent = escolhidoPreco || "—";
  backdrop.style.display = "flex";
  backdrop.setAttribute("aria-hidden","false");
}

function fecharModal(){
  if (!backdrop) return;
  backdrop.style.display = "none";
  backdrop.setAttribute("aria-hidden","true");
}

if (form) {
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    if (!escolhidoNome || !escolhidoPreco) return;
    abrirModal();
  });
}

if (btnCancelarModal) {
  btnCancelarModal.addEventListener("click", fecharModal);
}

if (btnConfirmarModal && form) {
  btnConfirmarModal.addEventListener("click", () => {
    fecharModal();
    form.submit();
  });
}

if (backdrop) {
  backdrop.addEventListener("click", (e) => {
    if (e.target === backdrop) fecharModal();
  });
}

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") fecharModal();
});
</script>

</body>
</html>