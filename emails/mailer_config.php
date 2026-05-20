<?php
// Configuración SMTP reutilizable para PHPMailer.
// En hosting gratuito (InfinityFree, etc.) debes usar un servidor SMTP externo
// como SendGrid, Mailgun, SMTP2GO, o Gmail con App Password.

return [
    'host' => 'smtp.sendgrid.net',
    'username' => 'apikey',
    'password' => 'TU_API_KEY_O_PASSWORD_SMTP',
    'port' => 587,
    'SMTPSecure' => 'tls',
    'fromEmail' => 'no-reply@tudominio.com',
    'fromName' => 'SMARTPARK',
    'replyToEmail' => 'info@tudominio.com',
    'replyToName' => 'SMARTPARK Info',
    'ccEmail' => 'urian1213viera@gmail.com'
];
