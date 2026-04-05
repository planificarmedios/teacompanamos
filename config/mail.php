<?php

use PHPMailer\PHPMailer\PHPMailer;

/* SMTP */
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', PHPMailer::ENCRYPTION_STARTTLS);

// define('SMTP_USER', 'planificarmedios@gmail.com');
// define('SMTP_PASS', 'silyctezfjpubdys');

define('SMTP_USER', 'info@teacompanamos.com.ar');
define('SMTP_PASS', 'gwiqiwhvphfohcil');


/* DATOS MAIL */
define('MAIL_FROM_NAME', 'Web Te Acompañamos');
define('MAIL_FROM_EMAIL', SMTP_USER);

/* DESTINOS */
define('DEFAULT_TO', 'info@teacompanamos.com.ar');
define('LABORALES_TO', 'empleo@teacompanamos.com.ar');