<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require __DIR__ . '/../vendor/autoload.php';

function sendEmail($to, $subject, $body, $toName = '', $fromEmail = 'balasmexico2026@gmail.com', $fromName = 'Barangay Balas') {
    $mail = new PHPMailer(true);
    $response = ['success' => false, 'message' => ''];

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'uhiouhiojk'; // Use consistent email
        $mail->Password = 'llllllllllllllllllllllllllll'; // Your app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 0; // Set to 2 for debugging

        // Recipients
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to, $toName);
        $mail->addReplyTo($fromEmail, $fromName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        $response['success'] = true;
        $response['message'] = 'Email sent successfully';
        
    } catch (Exception $e) {
        $response['message'] = "Mailer Error: " . $mail->ErrorInfo;
        error_log("Email Error: " . $response['message']);
    }

    return $response;
}
?>
