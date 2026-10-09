<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

final class GmailMailer
{
    public static function enviarRecuperacion(string $destinatario, string $token): void
    {
        $configPath = __DIR__ . '/mail.local.php';
        if (!is_file($configPath)) {
            throw new RuntimeException('Falta crear config/mail.local.php a partir de config/mail.example.php.');
        }

        $config = require $configPath;
        foreach (['gmail_address', 'app_base_url'] as $key) {
            if (!isset($config[$key]) || !is_string($config[$key]) || trim($config[$key]) === '') {
                throw new RuntimeException("La configuración SMTP requiere un valor válido para {$key}.");
            }
        }

        if (!isset($config['gmail_app_password']) || !is_string($config['gmail_app_password'])
            || trim($config['gmail_app_password']) === '') {
            throw new RuntimeException('Falta configurar la contraseña de aplicación de Gmail en config/mail.local.php.');
        }

        if (!filter_var($config['gmail_address'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('La dirección Gmail configurada no es válida.');
        }

        $baseUrl = rtrim($config['app_base_url'], '/');
        $resetUrl = $baseUrl . '/views/Auth/restablecer.php?token=' . rawurlencode($token);
        $safeUrl = htmlspecialchars($resetUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $config['gmail_address'];
            $mail->Password = preg_replace('/\s+/', '', $config['gmail_app_password']);
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->setFrom($config['gmail_address'], 'Bella Tela · SIGIBET');
            $mail->addAddress($destinatario);
            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de contraseña - Bella Tela';
            $mail->Body = <<<HTML
                <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;color:#292528">
                    <div style="background:#1C191B;border-bottom:3px solid #B9974E;padding:24px;text-align:center;color:#fff">
                        <h1 style="margin:0;font-size:22px">Bella Tela · SIGIBET</h1>
                    </div>
                    <div style="padding:28px 24px;background:#fff">
                        <h2 style="color:#854653">Recupera tu contraseña</h2>
                        <p>Recibimos una solicitud para cambiar la contraseña de tu cuenta.</p>
                        <p>El enlace es válido durante 30 minutos y solo se puede utilizar una vez.</p>
                        <p style="margin:28px 0">
                            <a href="{$safeUrl}" style="background:#A85F6D;color:#fff;padding:13px 20px;border-radius:7px;text-decoration:none;font-weight:bold">
                                Crear nueva contraseña
                            </a>
                        </p>
                        <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
                        <p style="font-size:13px;color:#70696D">Si el botón no funciona, copia este enlace en tu navegador:<br>{$safeUrl}</p>
                    </div>
                </div>
                HTML;
            $mail->AltBody = "Recibimos una solicitud para cambiar la contraseña de tu cuenta Bella Tela.\n"
                . "Abre este enlace dentro de 30 minutos:\n{$resetUrl}\n\n"
                . "Si no solicitaste este cambio, ignora este mensaje.";
            $mail->send();
        } catch (Exception $exception) {
            throw new RuntimeException('Gmail SMTP no pudo enviar el mensaje.', 0, $exception);
        }
    }
}
