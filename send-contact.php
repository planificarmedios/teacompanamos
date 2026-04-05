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
   reCAPTCHA v3
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
    $isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);
    $minScore = $isLocalhost ? 0.1 : RECAPTCHA_MIN_SCORE;

    return (
        !empty($result['success']) &&
        ($result['score'] ?? 0) >= $minScore &&
        ($result['action'] ?? '') === $expectedAction
    );
}

/* ======================================================
   VALIDAR reCAPTCHA
====================================================== */
$recaptchaToken = $_POST['recaptcha_token'] ?? '';

if (!validateRecaptcha($recaptchaToken, 'contact')) {
    errorResponse('Validación de seguridad fallida', 403);
}

/* ======================================================
   DATOS DEL FORMULARIO
====================================================== */
$name    = clean($_POST['name'] ?? '');
$email   = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone   = clean($_POST['phone'] ?? '');
$subject = clean($_POST['subject'] ?? 'Nuevo mensaje desde la web');
$message = clean($_POST['message'] ?? '');
$fecha   = date('d/m/Y H:i');

/* ======================================================
   VALIDACIONES
====================================================== */
if (!$name || !$email || !$phone || !$message) {
    errorResponse('Faltan campos obligatorios');
}

/* ======================================================
   ENVÍO DE MAIL
====================================================== */
$mail = new PHPMailer(true);

try {
    // $mail->SMTPDebug = 2; // activar si querés debug

    /* SMTP DESDE CONFIG */
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->CharSet = 'UTF-8';

    $mail->setFrom(SMTP_USER, MAIL_FROM_NAME);
    $mail->addAddress(DEFAULT_TO);
    $mail->addReplyTo($email, $name);

    $mail->Subject = $subject;

    /* ======================================================
       HTML PROFESIONAL
    ====================================================== */
    $mail->isHTML(true);

    $mail->Body = "
    <html>
    <body style='margin:0; padding:0; background:#f4f6f8; font-family:Arial, sans-serif;'>
      <div style='max-width:600px; margin:20px auto; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.1);'>
        
        <div style='background:#2c7be5; color:#ffffff; padding:20px; text-align:center; font-size:20px; font-weight:bold;'>
          📩 Nuevo mensaje desde la web
        </div>

        <div style='padding:20px; color:#333;'>

          <p><strong>📅 Fecha:</strong><br>$fecha</p>

          <p><strong>👤 Nombre:</strong><br>$name</p>

          <p><strong>✉️ Email:</strong><br>
            <a href='mailto:$email'>$email</a>
          </p>

          <p><strong>📞 Teléfono:</strong><br>$phone</p>

          <p><strong>💬 Mensaje:</strong><br>$message</p>

        </div>

        <div style='background:#f1f1f1; text-align:center; padding:10px; font-size:12px; color:#777;'>
          Este mensaje fue enviado desde el formulario web
        </div>

      </div>
    </body>
    </html>
    ";

    /* TEXTO PLANO */
    $mail->AltBody = "
    MENSAJE DESDE LA WEB

    Fecha: $fecha
    Nombre: $name
    Email: $email
    Teléfono: $phone

    Mensaje:
    $message
    ";

    /* ======================================================
       ADJUNTO (OPCIONAL)
    ====================================================== */
    if (
        isset($_FILES['attachment']) &&
        $_FILES['attachment']['error'] === UPLOAD_ERR_OK
    ) {

        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $maxSize = 5 * 1024 * 1024;

        $fileTmp  = $_FILES['attachment']['tmp_name'];
        $fileName = $_FILES['attachment']['name'];
        $fileSize = $_FILES['attachment']['size'];
        $fileType = mime_content_type($fileTmp);

        if (!in_array($fileType, $allowedTypes)) {
            errorResponse('Tipo de archivo no permitido');
        }

        if ($fileSize > $maxSize) {
            errorResponse('El archivo supera los 5MB');
        }

        $mail->addAttachment($fileTmp, $fileName);
    }

    $mail->send();

    echo json_encode([
        'success' => true
    ]);

} catch (Exception $e) {
    errorResponse('No se pudo enviar el mensaje', 500);
}