<?php
require_once 'config/emailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        header('Location: index.php?error=1');
        exit;
    }

    $subject = "New Message from Contact Form – Barangay Balas";
    $body = "
        <h2>Contact Message from Barangay Balas Website</h2>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Message:</strong></p>
        <p>{$message}</p>
        <hr>
        <p>This message was sent from the Barangay Balas Contact Form.</p>
    ";

    // Send to barangay official emai
    $to = 'jarshanetolentino@gmail.com';

    $result = sendEmail($to, $subject, $body);

    if ($result['success']) {
        header('Location: index.php?success=1');
    } else {
        header('Location: index.php?error=1');
    }
    exit;
}
