<?php

function enviarEmail($para, $nome, $data, $hora, $barbeiro, $servico) {

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

        $mail->setFrom('teuemail@gmail.com', "Light's Barber");
        $mail->addAddress($para, $nome);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Marcação Confirmada — Light's Barber";

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 520px; margin: auto; background: #111; color: #fff; border-radius: 10px; overflow: hidden;'>

            <div style='background: #000; padding: 24px; text-align: center; border-bottom: 2px solid #cfa64b;'>
                <h1 style='margin: 0; font-size: 22px; letter-spacing: 3px; color: #cfa64b;'>LIGHT'S BARBER</h1>
            </div>

            <div style='padding: 30px 28px;'>
                <h2 style='margin: 0 0 8px 0; font-size: 18px; color: #fff;'>Marcação Confirmada 💈</h2>
                <p style='color: #ccc; margin: 0 0 24px 0; font-size: 14px;'>Olá <strong style='color:#fff;'>$nome</strong>, a tua marcação foi confirmada com sucesso.</p>

                <div style='background: #1a1a1a; border-radius: 8px; padding: 20px;'>
                    <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                        <tr>
                            <td style='padding: 10px 0; border-bottom: 1px solid #2a2a2a; color: #999;'>Data</td>
                            <td style='padding: 10px 0; border-bottom: 1px solid #2a2a2a; color: #fff; text-align: right;'><strong>$data</strong></td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; border-bottom: 1px solid #2a2a2a; color: #999;'>Hora</td>
                            <td style='padding: 10px 0; border-bottom: 1px solid #2a2a2a; color: #fff; text-align: right;'><strong>$hora</strong></td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; border-bottom: 1px solid #2a2a2a; color: #999;'>Barbeiro</td>
                            <td style='padding: 10px 0; border-bottom: 1px solid #2a2a2a; color: #fff; text-align: right;'><strong>$barbeiro</strong></td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 0; color: #999;'>Serviço</td>
                            <td style='padding: 10px 0; color: #cfa64b; text-align: right;'><strong>$servico</strong></td>
                        </tr>
                    </table>
                </div>

                <p style='color: #888; font-size: 12px; margin: 24px 0 0 0; text-align: center;'>
                    Caso precise de cancelar, podes fazê-lo na tua área de cliente.<br>
                    Até já! ✂️
                </p>
            </div>

        </div>
        ";

        $mail->AltBody = "Olá $nome, a tua marcação foi confirmada.\nData: $data\nHora: $hora\nBarbeiro: $barbeiro\nServiço: $servico";

        $mail->send();

    } catch (Exception $e) {
        // não quebra o site
    }
}