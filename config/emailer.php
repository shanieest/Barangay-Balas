<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';


function sendEmail($to, $subject, $body, $toName = '') {
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jarshanetolentino@gmail.com'; // <-- your Gmail
        $mail->Password = 'hkxe ezxm cxpo xlbt';  // <-- Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->SMTPDebug = 0; // turn off browser output
$mail->Debugoutput = function($str, $level) {
    error_log("Mailer debug [$level]: $str");
};



        // Sender Info
        $mail->setFrom('jarshanetolentino@gmail.com', 'Barangay Balas');

        // Recipient
        $mail->addAddress($to, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>
