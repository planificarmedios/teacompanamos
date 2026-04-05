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

if (!validateRecaptcha($recaptchaToken, 'laborales')) {
    errorResponse('Validación de seguridad fallida', 403);
}

/* ======================================================
   DATOS
====================================================== */
$puesto     = clean($_POST['puesto'] ?? 'POSTULACIÓN ESPONTÁNEA');
$referencia = clean($_POST['referencia'] ?? 'SIN REFERENCIA');
$subject    = "Postulación / Referencia – $puesto ";

$nombre   = clean($_POST['nombre'] ?? '');
$apellido = clean($_POST['apellido'] ?? '');

$tipoDoc   = clean($_POST['tipo_documento'] ?? '');
$documento = clean($_POST['documento'] ?? '');
$cuil      = clean($_POST['cuil'] ?? '');
$genero    = clean($_POST['genero'] ?? '');
$fechaNacRaw = $_POST['fecha_nacimiento'] ?? '';

if ($fechaNacRaw && strtotime($fechaNacRaw)) {
    $fechaNac = date("d/m/Y", strtotime($fechaNacRaw));
} else {
    $fechaNac = '—';
}

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
if (!$nombre || !$apellido || !$email || !$celular) {
    errorResponse('Faltan campos obligatorios');
}

if (!isset($_FILES['cv']) || $_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
    errorResponse('Debe adjuntar su CV');
}

/* VALIDAR CV */
$allowedExt = ['pdf', 'doc', 'docx'];
$ext = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt)) {
    errorResponse('Formato de CV no permitido');
}

if ($_FILES['cv']['size'] > 5 * 1024 * 1024) {
    errorResponse('El CV supera los 5MB');
}

/* ======================================================
   MAIL
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
    $mail->addAddress(LABORALES_TO);
    $mail->addReplyTo($email, "$nombre $apellido");

    $mail->addAttachment($_FILES['cv']['tmp_name'], $_FILES['cv']['name']);

    $mail->Subject = $subject;

    /* ======================================================
       HTML PRO
    ====================================================== */
    $mail->isHTML(true);

    $mail->Body = "
    <html>
    <body style='margin:0; background:#f4f6f8; font-family:Arial;'>
      <div style='max-width:650px; margin:20px auto; background:#fff; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.1);'>

        <div style='background:#343a40; color:#fff; padding:20px; text-align:center; font-size:20px; font-weight:bold;'>
          👔 Nueva Postulación Laboral
        </div>

        <div style='padding:20px; color:#333;'>

          <p><strong>📅 Fecha:</strong> $fechaEnvio</p>
          <p><strong>📌 Puesto:</strong> $puesto</p>
          <p><strong>🔎 Referencia:</strong> $referencia</p>

          <hr>

          <h3>👤 Datos personales</h3>
          <p><strong>Nombre:</strong> $nombre $apellido</p>
          <p><strong>Documento:</strong> $tipoDoc $documento</p>
          <p><strong>CUIL:</strong> $cuil</p>
          <p><strong>Género:</strong> $genero</p>
          <p><strong>Nacimiento:</strong> $fechaNac</p>
          <p><strong>Estado civil:</strong> $estadoCivil</p>

          <hr>

          <h3>📞 Contacto</h3>
          <p><strong>Celular:</strong> $celular</p>
          <p><strong>WhatsApp:</strong> $whatsapp</p>
          <p><strong>Email:</strong> <a href='mailto:$email'>$email</a></p>
          <p><strong>LinkedIn:</strong> $linkedin</p>

          <hr>

          <h3>🎓 Formación</h3>
          <p><strong>Nivel:</strong> $nivelEdu</p>
          <p><strong>Títulos:</strong> $titulos</p>

          <p><strong>Matrícula:</strong> $matricula</p>
          <p><strong>RNP:</strong> $rnp</p>
          <p><strong>RUP:</strong> $rup</p>
          <p><strong>APND:</strong> $apnd</p>

          <hr>

          <h3>📍 Zona de trabajo</h3>
          <p>$localidad - $ciudad</p>

        </div>

        <div style='background:#f1f1f1; text-align:center; padding:10px; font-size:12px; color:#777;'>
          CV adjunto | Enviado desde la web
        </div>

      </div>
    </body>
    </html>
    ";

    /* TEXTO */
    $mail->AltBody = "Postulación de $nombre $apellido - $puesto";

    $mail->send();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    errorResponse('No se pudo enviar la postulación', 500);
}