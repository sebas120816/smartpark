<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$config = require __DIR__ . '/mailer_config.php';

// Instancia de PHPMailer
$mail = new PHPMailer(true);
$mail->setLanguage("es"); // establecer el idioma Español


try {
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host       = $config['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['username'];
    $mail->Password   = $config['password'];
    $mail->SMTPSecure = $config['SMTPSecure'];
    $mail->Port       = $config['port'];
    $mail->CharSet = 'UTF-8';


    $emailUser = trim($_REQUEST['emailUser']);

    $mail->setFrom($config['fromEmail'], $config['fromName']);  // Quien envía este mensaje
    $mail->addAddress($emailUser, '');  // Destinatario
    //$mail->addAddress('urian1213viera@gmail.com', 'Joe User');

    // Copia y respuesta
    $mail->addReplyTo($config['replyToEmail'], $config['replyToName']);
    $mail->addCC($config['ccEmail']); // Copia


    //Content
    $mail->isHTML(true);
    //Asunto                             
    $mail->Subject = 'Parking';
    $mail->Body .= "<section style='margin-top: 10px; font-size: 18px; line-height: 7px;'>";
    $mail->Body .= "<p>En <strong style='color:#ff6d0c;'>Parking</strong> le damos la bienvenida y estamos encantados de tenerte como nuestro cliente.</p>";
    $mail->Body .= "<p>Tu cuenta se ha creado exitosamente.</p>";
    $mail->Body .= "<p>Para acceder con tu cuenta a la plataforma, haga clic en el siguiente enlace:</p><br>";
    $mail->Body .= "<a href='https://parking.com/app/' style='background: #ff6d0c; font-size:15px; padding: 10px 20px; border-radius: 25px;text-decoration: unset; color:#fff;'>Acceder Ahora</a>";
    $mail->Body .= "</section>";

    $mail->Body .= "<section style='margin-top: 50px; margin-bottom: 70px; font-size: 18px; line-height: 7px;'>";
    $mail->Body .= "<p>Gracias de nuevo por elegir <strong style='color:#ff6d0c;'>Parking</strong>.</p>";
    $mail->Body .= "<p>Si tienes alguna pregunta o necesitas asistencia, no dudes en contactarnos.</p>";
    $mail->Body .= '<p>¡Esperamos que tengas una experiencia increíble!</p>';
    $mail->Body .= "</section>";


    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
        header("location:../?successC=1");
    }
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
