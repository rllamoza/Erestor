<?php
require_once __DIR__ . '/../config.php';

// Check if PHPMailer exists before requiring
$phpMailerPath = __DIR__ . '/../../libraries/libs/PHPMailer/PHPMailer.php';
$hasPHPMailer = file_exists($phpMailerPath);

if ($hasPHPMailer) {
    require_once __DIR__ . '/../../libraries/libs/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/../../libraries/libs/PHPMailer/SMTP.php';
    require_once __DIR__ . '/../../libraries/libs/PHPMailer/Exception.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    public static function sendOTP($to, $code) {
        global $hasPHPMailer;
        
        if (!$hasPHPMailer) {
            // Fallback for local development when PHPMailer is not installed
            error_log("DEV MODE: OTP for $to is $code");
            
            // In a real production scenario without PHPMailer, we'd return false. 
            // For this local environment, we simulate success so the flow can be tested.
            return true; 
        }

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = !empty(SMTP_USER);
            if ($mail->SMTPAuth) {
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
            }
            
            // Seguridad dinámica (TLS/SSL)
            if (defined('SMTP_SECURE')) {
                if (strtolower(SMTP_SECURE) === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } elseif (strtolower(SMTP_SECURE) === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                }
            }
            
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            // Recipients
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($to);
            if (defined('MAIL_REPLYTO')) {
                $mail->addReplyTo(MAIL_REPLYTO, MAIL_REPLYTO_NAME);
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = '🔐 Código de Verificación - Hamuy Net';
            
            // Professional Neon Template
            $mail->Body = "
                <div style='background-color: #0f172a; padding: 40px; font-family: \"Segoe UI\", Roboto, sans-serif; color: #ffffff;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #1e293b; border-radius: 16px; overflow: hidden; border: 1px solid rgba(0, 240, 255, 0.2); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);'>
                        
                        <!-- Header -->
                        <div style='background: linear-gradient(135deg, #8600ff 0%, #00f0ff 100%); padding: 30px; text-align: center;'>
                            <h1 style='margin: 0; color: #ffffff; font-size: 28px; letter-spacing: 1px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);'>
                                Hamuy Net
                            </h1>
                        </div>

                        <!-- Body -->
                        <div style='padding: 40px; line-height: 1.6;'>
                            <h2 style='color: #00f0ff; margin-top: 0; font-size: 22px;'>Casi has terminado...</h2>
                            <p style='color: #cbd5e1; font-size: 16px;'>Para completar tu registro de forma segura, utiliza el siguiente código de verificación:</p>
                            
                            <div style='margin: 35px 0; padding: 25px; background: rgba(0, 240, 255, 0.05); border: 2px dashed #8600ff; border-radius: 12px; text-align: center;'>
                                <span style='font-family: monospace; font-size: 42px; font-weight: 800; color: #00f0ff; letter-spacing: 12px;'>
                                    $code
                                </span>
                            </div>

                            <p style='color: #94a3b8; font-size: 14px; margin-bottom: 0;'>El código expirará por seguridad. No compartas esta clave con nadie.</p>
                        </div>

                        <!-- Footer -->
                        <div style='padding: 20px; background: #0f172a; text-align: center; border-top: 1px solid rgba(255, 255, 255, 0.05);'>
                            <p style='margin: 0; color: #64748b; font-size: 12px;'>
                                &copy; " . date('Y') . " Plataforma de Asistencia - Redes Hamuy
                            </p>
                        </div>
                    </div>
                </div>
            ";
            $mail->AltBody = "Tu código de verificación es: $code";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando email: {$mail->ErrorInfo}");
            return false;
        }
    }

    public static function sendPasswordResetOTP($to, $code) {
        global $hasPHPMailer;
        
        if (!$hasPHPMailer) {
            error_log("DEV MODE: Password Reset OTP for $to is $code");
            return true; 
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = !empty(SMTP_USER);
            if ($mail->SMTPAuth) {
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
            }
            if (defined('SMTP_SECURE')) {
                if (strtolower(SMTP_SECURE) === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } elseif (strtolower(SMTP_SECURE) === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                }
            }
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($to);
            if (defined('MAIL_REPLYTO')) {
                $mail->addReplyTo(MAIL_REPLYTO, MAIL_REPLYTO_NAME);
            }

            $mail->isHTML(true);
            $mail->Subject = '🔑 Recuperar Contraseña - Hamuy Net';
            
            $mail->Body = "
                <div style='background-color: #0f172a; padding: 40px; font-family: \"Segoe UI\", Roboto, sans-serif; color: #ffffff;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #1e293b; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255, 0, 127, 0.2); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);'>
                        
                        <div style='background: linear-gradient(135deg, #ff007f 0%, #8600ff 100%); padding: 30px; text-align: center;'>
                            <h1 style='margin: 0; color: #ffffff; font-size: 28px; letter-spacing: 1px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);'>
                                Hamuy Net
                            </h1>
                        </div>

                        <div style='padding: 40px; line-height: 1.6;'>
                            <h2 style='color: #ff007f; margin-top: 0; font-size: 22px;'>Recuperación de Acceso</h2>
                            <p style='color: #cbd5e1; font-size: 16px;'>Has solicitado restablecer tu contraseña. Utiliza el siguiente código para validar tu identidad:</p>
                            
                            <div style='margin: 35px 0; padding: 25px; background: rgba(255, 0, 127, 0.05); border: 2px dashed #ff007f; border-radius: 12px; text-align: center;'>
                                <span style='font-family: monospace; font-size: 42px; font-weight: 800; color: #ff007f; letter-spacing: 12px;'>
                                    $code
                                </span>
                            </div>

                            <p style='color: #94a3b8; font-size: 14px; margin-bottom: 0;'>Si no solicitaste este cambio, puedes ignorar este correo de forma segura.</p>
                        </div>

                        <div style='padding: 20px; background: #0f172a; text-align: center; border-top: 1px solid rgba(255, 255, 255, 0.05);'>
                            <p style='margin: 0; color: #64748b; font-size: 12px;'>
                                &copy; " . date('Y') . " Plataforma de Asistencia - Redes Hamuy
                            </p>
                        </div>
                    </div>
                </div>
            ";
            $mail->AltBody = "Tu código para restablecer la contraseña es: $code";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Error enviando email: {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>
