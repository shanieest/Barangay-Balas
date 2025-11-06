<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require __DIR__ . '/../vendor/autoload.php';

/**
 * Send an email using PHPMailer
 *
 * @param string $to         Recipient email address
 * @param string $subject    Email subject
 * @param string $body       HTML email body
 * @param string $toName     (Optional) Recipient name
 * @param string $fromEmail  (Optional) Sender email address (default: system email)
 * @param string $fromName   (Optional) Sender name (default: "Barangay Balas")
 * @return array             ['success' => bool, 'message' => string]
 */
function sendEmail($to, $subject, $body, $toName = '', $fromEmail = 'jarshanetolentino@gmail.com', $fromName = 'Barangay Balas') {
    $mail = new PHPMailer(true);
    $response = ['success' => false, 'message' => ''];

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jarshanetolentino@gmail.com'; // Your Gmail account
        $mail->Password = 'nvzx pliw hmxy iyeh'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender (can be overridden dynamically)
        $mail->setFrom($fromEmail, $fromName);

        // Recipient
        $mail->addAddress($to, $toName);

        // Reply-To (helps when replies go directly to the sender)
        $mail->addReplyTo($fromEmail, $fromName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        // Optional: plain text version
        $mail->AltBody = strip_tags($body);

        // Send
        $mail->send();
        $response['success'] = true;
    } catch (Exception $e) {
        $response['message'] = $mail->ErrorInfo ?: $e->getMessage();
        error_log("Mailer Error: " . $response['message']);
    }

    return $response;
}
?>
