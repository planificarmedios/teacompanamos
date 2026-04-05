<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'config/mail.php';

header('Content-Type: application/json; charset=utf-8');

/* ======================================================
   CONFIGURACIÓN
====================================================== */
define('RECAPTCHA_SECRET', '6LfHBUosAAAAAHsxfEI3HqHiK7z9Tv2H0bdiWwfo');
define('RECAPTCHA_MIN_SCORE', 0.5);

/* ======================================================
   MÉTODO
====================================================== */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método inválido'
    ]);
    exit;
}

/* ======================================================
   HELPERS
====================================================== */
function clean($value): string {
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

function errorResponse(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

/* ======================================================
   reCAPTCHA
====================================================== */
function validateRecaptcha(string $token, string $expectedAction): bool {

    if (!$token) return false;

    $response = file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify?' .
        http_build_query([
            'secret'   => RECAPTCHA_SECRET,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ])
    );

    if (!$response) return false;

    $result = json_decode($response, true);

    return (
        !empty($result['success']) &&
        ($result['score'] ?? 0) >= RECAPTCHA_MIN_SCORE &&
        ($result['action'] ?? '') === $expectedAction
    );
}

/* ======================================================
   VALIDAR reCAPTCHA
====================================================== */
$recaptchaToken = $_POST['recaptcha_token'] ?? '';

if (!validateRecaptcha($recaptchaToken, 'service')) {
    errorResponse('Validación de seguridad fallida', 403);
}

/* ======================================================
   DATOS DEL FORMULARIO
====================================================== */
$to          = filter_var($_POST['to'] ?? DEFAULT_TO, FILTER_VALIDATE_EMAIL) ?: DEFAULT_TO;
$subject     = clean($_POST['subject'] ?? 'Solicitud de Servicio');

$responsable = clean($_POST['responsable'] ?? '');
$paciente    = clean($_POST['paciente'] ?? '');
$domicilio   = clean($_POST['domicilio'] ?? '');
$obraSocial  = clean($_POST['obra_social'] ?? '');
$diagnostico = clean($_POST['diagnostico'] ?? '');
$descripcion = clean($_POST['descripcion'] ?? '');
$info_taller = clean($_POST['info-taller'] ?? '');

$email       = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$telefono    = clean($_POST['telefono'] ?? '');
$fecha       = date('d/m/Y H:i');

/* ======================================================
   VALIDACIONES
====================================================== */
if (
    !$responsable ||
    !$paciente ||
    !$domicilio ||
    !$email ||
    !$telefono
) {
    errorResponse('Faltan campos obligatorios');
}

/* ======================================================
   ENVÍO DE MAIL
====================================================== */
$mail = new PHPMailer(true);

try {

    // $mail->SMTPDebug = 2;

    /* SMTP DESDE CONFIG */
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port       = SMTP_PORT;

    $mail->CharSet = 'UTF-8';

    $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
    $mail->addAddress($to);
    $mail->addReplyTo($email, $responsable);

    $mail->Subject = $subject;

    /* ======================================================
       HTML PROFESIONAL
    ====================================================== */
    $mail->isHTML(true);

    $mail->Body = "
    <html>
    <body style='margin:0; padding:0; background:#f4f6f8; font-family:Arial, sans-serif;'>
      <div style='max-width:600px; margin:20px auto; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);'>
        
        <div style='background:#28a745; color:#ffffff; padding:20px; text-align:center; font-size:20px; font-weight:bold;'>
          📝 Nueva solicitud de servicio
        </div>

        <div style='padding:20px; color:#333;'>

          <p><strong>📅 Fecha:</strong><br>$fecha</p>
          <p><strong>📌 Servicio solicitado:</strong><br>$subject</p>

          <hr>

          <p><strong>👤 Responsable:</strong><br>$responsable</p>
          <p><strong>🧒 Paciente:</strong><br>$paciente</p>
          <p><strong>📍 Domicilio:</strong><br>$domicilio</p>

          <p><strong>🏥 Obra Social:</strong><br>$obraSocial</p>
          <p><strong>🧠 Diagnóstico:</strong><br>$diagnostico</p>

          <hr>

          <p><strong>💬 Descripción:</strong><br>$descripcion</p>

          <p><strong>🧩 Información del taller:</strong><br>$info_taller</p>

          <hr>

          <p><strong>✉️ Email:</strong><br>
            <a href='mailto:$email'>$email</a>
          </p>

          <p><strong>📞 Tel / WhatsApp:</strong><br>$telefono</p>

        </div>

        <div style='background:#f1f1f1; text-align:center; padding:10px; font-size:12px; color:#777;'>
          Solicitud enviada desde la web - Te Acompañamos
        </div>

      </div>
    </body>
    </html>
    ";

    /* TEXTO PLANO */
    $mail->AltBody = "
    SOLICITUD DE SERVICIO

    Fecha: $fecha
    Servicio: $subject

    Responsable: $responsable
    Paciente: $paciente
    Domicilio: $domicilio

    Obra social: $obraSocial
    Diagnóstico: $diagnostico

    Descripción:
    $descripcion

    Taller:
    $info_taller

    Email: $email
    Teléfono: $telefono
    ";

    $mail->send();

    echo json_encode([
        'success' => true
    ]);

} catch (Exception $e) {
    errorResponse('No se pudo enviar la solicitud', 500);
}