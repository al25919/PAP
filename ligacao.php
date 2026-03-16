<?php
// Dados para ligar à minha base de dados

$servidor = "localhost"; // estou a trabalhar no meu pc
$utilizador = "root";    // utilizador padrão do XAMPP
$password = "";          // no XAMPP o root não tem password
$base_dados = "lights_barber"; // nome da base de dados que criei

// faço a ligação ao MySQL
$conn = new mysqli($servidor, $utilizador, $password, $base_dados);

// verifico se deu erro na ligação
if ($conn->connect_error) {
    die("Erro na ligação: " . $conn->connect_error);
}
?>