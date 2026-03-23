<?php

function enviarEmail($para, $nome, $data, $hora, $barbeiro, $servico) {

    // 🔥 se PHPMailer não existir, não faz nada
    if (!file_exists('phpmailer/src/PHPMailer.php')) {
        return;
    }

    require_once 'phpmailer/src/PHPMailer.php';
    require_once 'phpmailer/src/SMTP.php';
    require_once 'phpmailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'teuemail@gmail.com';
        $mail->Password = 'PASSWORD_APP';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('teuemail@gmail.com', 'Lights Barber');
        $mail->addAddress($para, $nome);

        $mail->isHTML(true);
        $mail->Subject = 'Confirmacao de Marcacao';

        $mail->Body = "
            <h2>Marcacao Confirmada 💈</h2>
            <p>Olá <b>$nome</b>,</p>
            <p>A tua marcacao foi confirmada:</p>
            <ul>
                <li><b>Data:</b> $data</li>
                <li><b>Hora:</b> $hora</li>
                <li><b>Barbeiro:</b> $barbeiro</li>
                <li><b>Servico:</b> $servico</li>
            </ul>
        ";

        $mail->send();

    } catch (Exception $e) {
        // 🔥 não quebra o site
    }
}