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

define('LABORALES_TO', 'empleo@teacompanamos.com.ar');

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

function val($value): string {
    return $value !== '' ? $value : '—';
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

if (!validateRecaptcha($recaptchaToken, 'laborales')) {
    errorResponse('Validación de seguridad fallida', 403);
}

/* ======================================================
   DATOS DEL FORMULARIO
====================================================== */
$puesto     = clean($_POST['puesto'] ?? 'POSTULACIÓN ESPONTÁNEA');
$referencia = clean($_POST['referencia'] ?? 'SUBIR CV');

$subject = "Postulación – $puesto | Ref: $referencia";

$nombre   = clean($_POST['nombre'] ?? '');
$apellido = clean($_POST['apellido'] ?? '');

$tipoDoc   = clean($_POST['tipo_documento'] ?? '');
$documento = clean($_POST['documento'] ?? '');
$cuil      = clean($_POST['cuil'] ?? '');
$genero    = clean($_POST['genero'] ?? '');
$fechaNac  = clean($_POST['fecha_nacimiento'] ?? '');
$estadoCivil = clean($_POST['estado_civil'] ?? '');

$celular  = clean($_POST['celular'] ?? '');
$whatsapp = clean($_POST['whatsapp'] ?? '');
$email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$linkedin = clean($_POST['linkedin'] ?? '');

$nivelEdu = clean($_POST['nivel_educativo'] ?? '');
$titulos  = clean($_POST['titulos'] ?? '');

$matricula = clean($_POST['matricula'] ?? '');
$rnp       = clean($_POST['rnp'] ?? '');
$rup       = clean($_POST['rup'] ?? '');
$apnd      = clean($_POST['apnd'] ?? '');

$localidad = clean($_POST['localidad'] ?? '');
$ciudad    = clean($_POST['ciudad'] ?? '');

$fechaEnvio = date('d/m/Y H:i');

/* ======================================================
   VALIDACIONES
====================================================== */
if (
    !$nombre ||
    !$apellido ||
    !$email ||
    !$celular
) {
    errorResponse('Faltan campos obligatorios');
}

if (
    !isset($_FILES['cv']) ||
    $_FILES['cv']['error'] !== UPLOAD_ERR_OK
) {
    errorResponse('Debe adjuntar su CV');
}

/* ======================================================
   VALIDAR CV
====================================================== */
$allowedExt  = ['pdf', 'doc', 'docx'];
$allowedMime = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

$ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt)) {
    errorResponse('Formato de CV no permitido');
}

if ($_FILES['cv']['size'] > 5 * 1024 * 1024) {
    errorResponse('El CV supera el tamaño máximo permitido (5MB)');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $_FILES['cv']['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedMime)) {
    errorResponse('Archivo de CV inválido');
}

/* ======================================================
   ENVÍO DE MAIL
====================================================== */
$mail = new PHPMailer(true);

try {

    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@teacompanamos.com.ar';
    $mail->Password   = 'lH$t/a&4^';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->CharSet = 'UTF-8';

    $mail->setFrom('info@teacompanamos.com.ar', 'Web Te Acompañamos – Empleos');
    $mail->addAddress(LABORALES_TO);
    $mail->addReplyTo($email, "$nombre $apellido");

    $mail->addAttachment($_FILES['cv']['tmp_name'], $_FILES['cv']['name']);

    $mail->Subject = $subject;

    $mail->Body = <<<MAIL
POSTULACIÓN LABORAL
Fecha: $fechaEnvio

PUESTO:
$puesto

REFERENCIA:
$referencia

------------------------------------

DATOS PERSONALES
Nombre: $nombre
Apellido: $apellido
Documento: $tipoDoc $documento
CUIL/CUIT: $cuil
Género: $genero
Fecha Nacimiento: $fechaNac
Estado Civil: $estadoCivil

------------------------------------

CONTACTO
Celular: $celular
WhatsApp: $whatsapp
Email: $email
LinkedIn: $linkedin

------------------------------------

FORMACIÓN
Nivel Educativo: $nivelEdu
Títulos: $titulos

Matrícula: $matricula
RNP: $rnp
RUP: $rup
APND: $apnd

------------------------------------

ZONA DE TRABAJO
Provincia / Localidad: $localidad
Ciudad / Barrio: $ciudad

MAIL;

    $mail->send();

    echo json_encode([
        'success' => true
    ]);

} catch (Exception $e) {
    errorResponse(
        'Mailer Error: ' . $mail->ErrorInfo,
        500
    );
}

