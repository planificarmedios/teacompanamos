<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método inválido'
    ]);
    exit;
}

/* ========= HELPERS ========= */
function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* ========= DATOS ========= */
$to          = clean($_POST['to'] ?? 'presupuesto@teacompanamos.com.ar');
$subject     = clean($_POST['subject'] ?? 'Solicitud de Servicio');

$responsable = clean($_POST['responsable'] ?? '');
$paciente    = clean($_POST['paciente'] ?? '');
$domicilio   = clean($_POST['domicilio'] ?? '');
$obraSocial  = clean($_POST['obra_social'] ?? '');
$diagnostico = clean($_POST['diagnostico'] ?? '');
$descripcion = clean($_POST['descripcion'] ?? '');

$email    = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$telefono = clean($_POST['telefono'] ?? '');
$fecha    = date('d/m/Y H:i');

/* ========= VALIDACIONES ========= */
if (
    !$responsable ||
    !$paciente ||
    !$domicilio ||
    !$email ||
    !$telefono
) {
    echo json_encode([
        'success' => false,
        'message' => 'Faltan campos obligatorios'
    ]);
    exit;
}

/* ========= MAIL ========= */
$mail = new PHPMailer(true);

try {
    // $mail->SMTPDebug = 2;

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

    $mail->Body =
        "SOLICITUD DE SERVICIO\n\n" .
        "Servicio solicitado:\n$subject\n\n" .

        "NOMBRE DEL RESPONSABLE:\n$responsable\n\n" .
        "NOMBRE DEL PACIENTE:\n$paciente\n\n" .
        "DOMICILIO Y LOCALIDAD:\n$domicilio\n\n" .
        "OBRA SOCIAL:\n$obraSocial\n\n" .
        "DIAGNÓSTICO / CUD:\n$diagnostico\n\n" .
        "DESCRIPCIÓN DEL REQUERIMIENTO:\n$descripcion\n\n" .

        "EMAIL:\n$email\n\n" .
        "TEL / WHATSAPP:\n$telefono\n\n";

    $mail->send();

    echo json_encode([
        'success' => true
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo enviar la solicitud',
        'debug' => $mail->ErrorInfo
    ]);
}
