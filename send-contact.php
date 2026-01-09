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

$name    = trim($_POST['name'] ?? '');
$email   = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$subject = trim($_POST['subject'] ?? 'Nuevo mensaje desde la web');
$phone = $_POST['phone'] ?? '';
$message = trim($_POST['message'] ?? '');

if (!$email || !$message || !$phone) {
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos'
    ]);
    exit;
}

$mail = new PHPMailer(true);

try {
    // $mail->SMTPDebug = 2; // solo para pruebas

    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'info@teacompanamos.com.ar';
    $mail->Password   = 'lH$t/a&4^';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->CharSet = 'UTF-8';

    $mail->setFrom('info@teacompanamos.com.ar', 'Web');
    $mail->addAddress('info@teacompanamos.com.ar');
    $mail->addReplyTo($email, $name);

    $mail->Subject = $subject;
    $mail->Body =
        "Nombre: $name\n" .
        "Email: $email\n\n" .
        "Teléfono: $phone\n" .
        "Mensaje:\n$message";

    /*
    |--------------------------------------------------------------------------
    | Archivo adjunto (opcional)
    |--------------------------------------------------------------------------
    */
    if (!empty($_FILES['attachment']['name'])) {

        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $maxSize = 5 * 1024 * 1024; // 5 MB

        $fileTmp  = $_FILES['attachment']['tmp_name'];
        $fileName = $_FILES['attachment']['name'];
        $fileType = $_FILES['attachment']['type'];
        $fileSize = $_FILES['attachment']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode([
                'success' => false,
                'message' => 'Tipo de archivo no permitido'
            ]);
            exit;
        }

        if ($fileSize > $maxSize) {
            echo json_encode([
                'success' => false,
                'message' => 'El archivo supera los 5MB'
            ]);
            exit;
        }

        $mail->addAttachment($fileTmp, $fileName);
    }

    $mail->send();

    echo json_encode([
        'success' => true
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo enviar el mensaje',
        'debug' => $mail->ErrorInfo
    ]);
}
