<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php'; // PHPMailer betöltése
require './secrets.php'; // SMTP hitelesítéshez

function sendTestEmail($recipientEmail) {
    $mail = new PHPMailer(true);

    try {
        // SMTP beállítások
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'smtpfortestingprgr@gmail.com';
        $mail->Password = $GLOBALS['secrets']['reset_email_agent']; // Gmail App jelszó
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = 587;

        // Küldő és címzett
        $mail->setFrom('smtpfortestingprgr@gmail.com', 'SMTP Tester');
        $mail->addAddress($recipientEmail); 

        // Email tartalom
        $mail->isHTML(true);
        $mail->Subject = 'SMTP Test - PHPMailer';
        $mail->Body = '<h3>Sikeresen működik a Gmail SMTP!</h3>';

        $mail->send();
        echo "✅ Email sikeresen elküldve $recipientEmail címre!";
    } catch (Exception $e) {
        echo "❌ Email küldési hiba: " . $mail->ErrorInfo;
    }
}

// Teszteléshez futtasd
if (isset($_GET['to'])) {
    sendTestEmail($_GET['to']);
} else {
    echo "Adj meg egy email címet a `to` paraméterben!";
}
