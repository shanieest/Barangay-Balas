<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';


function sendEmail($to, $subject, $body, $toName = '') {
    $mail = new PHPMailer(true);
    $response = ['success' => false, 'message' => ''];

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jarshanetolentino@gmail.com';
        $mail->Password = 'hkxe ezxm cxpo xlbt'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('jarshanetolentino@gmail.com', 'Barangay Balas');
        $mail->addAddress($to, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        $response['success'] = true;
    } catch (Exception $e) {
        $response['message'] = $mail->ErrorInfo ?: $e->getMessage();
        error_log("Mailer Error: " . $response['message']);
    }

    return $response;
}

?>
