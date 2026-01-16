<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

header('Content-Type: application/json; charset=utf-8');

/* ======================================================
   CONFIGURACIÓN
====================================================== */
define('RECAPTCHA_SECRET', '6LfHBUosAAAAAHsxfEI3HqHiK7z9Tv2H0bdiWwfo');
define('RECAPTCHA_MIN_SCORE', 0.5);
define('DEFAULT_TO', 'info@teacompanamos.com.ar');

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
    // $mail->SMTPDebug = 2; // solo para debug

    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@teacompanamos.com.ar';
    $mail->Password   = 'lH$t/a&4^';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->CharSet = 'UTF-8';

    $mail->setFrom('info@teacompanamos.com.ar', 'Web Te Acompañamos');
    $mail->addAddress(DEFAULT_TO);
    $mail->addReplyTo($email, $name);

    $mail->Subject = $subject;

    $mail->Body = <<<MAIL
MENSAJE DESDE LA WEB
Fecha: $fecha

NOMBRE:
$name

EMAIL:
$email

TELÉFONO:
$phone

MENSAJE:
$message
MAIL;

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

        $maxSize = 5 * 1024 * 1024; // 5 MB

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
