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
define('DEFAULT_TO', 'presupuesto@teacompanamos.com.ar');

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
    // $mail->SMTPDebug = 2; // activar solo para debug

    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@teacompanamos.com.ar';
    $mail->Password   = 'lH$t/a&4^';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->CharSet = 'UTF-8';

    $mail->setFrom('info@teacompanamos.com.ar', 'Web Te Acompañamos');
    $mail->addAddress($to);
    $mail->addReplyTo($email, $responsable);

    $mail->Subject = $subject;

    $mail->Body = <<<MAIL
SOLICITUD DE SERVICIO
Fecha: $fecha

SERVICIO SOLICITADO:
$subject

RESPONSABLE:
$responsable

PACIENTE:
$paciente

DOMICILIO Y LOCALIDAD:
$domicilio

OBRA SOCIAL:
$obraSocial

DIAGNÓSTICO / CUD:
$diagnostico

DESCRIPCIÓN:
$descripcion

EMAIL:
$email

TEL / WHATSAPP:
$telefono
MAIL;

    $mail->send();

    echo json_encode([
        'success' => true
    ]);

} catch (Exception $e) {
    errorResponse('No se pudo enviar la solicitud', 500);
}
