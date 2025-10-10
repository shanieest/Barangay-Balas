<?php
function requestStatusEmail($name, $documentType, $status, $note = '') {
    $subject = "Your {$documentType} Request has been {$status}";
    $color = $status === 'Approved' ? '#4CAF50' : '#E53935';
    $noteSection = $note ? "<p><strong>Admin Note:</strong> {$note}</p>" : '';

    $message = "
    <div style='font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px;'>
        <div style='max-width: 600px; margin: auto; background: white; border-radius: 8px; 
            overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(90deg, #0033cc, #3a7cb9); color: white; padding: 15px 25px;'>
                <h2 style='margin: 0;'>Barangay Balas, Mexico, Pampanga</h2>
            </div>
            <div style='padding: 25px;'>
                <h3 style='color: {$color};'>Request {$status}</h3>
                <p>Hello, <strong>{$name}</strong>,</p>
                <p>Your <strong>{$documentType}</strong> request has been <strong>{$status}</strong> by the Barangay Balas administration.</p>
                {$noteSection}
                <p style='margin-top: 20px;'>Thank you for using the Barangay Balas Online Services and Management System.</p>
            </div>
            <div style='background: #f1f1f1; padding: 15px; text-align: center; font-size: 13px; color: #555;'>
                © " . date('Y') . " Barangay Balas, Mexico, Pampanga. All rights reserved.
            </div>
        </div>
    </div>";

    return [
        'subject' => $subject,
        'message' => $message
    ];
}
?>
