<?php
require __DIR__ . '/config/emailer.php';

if (sendEmail('jarshanetolentino@gmail.com', 'Barangay Balas Email Test', '<b>It works!</b>')) {
    echo " Email sent successfully!";
} else {
    echo " Failed to send email. Check logs.";
}
