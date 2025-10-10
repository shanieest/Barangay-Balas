<?php
function announcementEmail($subject, $content) {
    $message = "
    <div style='font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px;'>
        <div style='max-width: 600px; margin: auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(90deg, #0033cc, #3a7cb9); color: white; padding: 15px 25px;'>
                <h2 style='margin: 0;'>Barangay Balas, Mexico, Pampanga</h2>
            </div>
            <div style='padding: 25px;'>
                <h3 style='color: #0033cc; margin-top: 0;'>{$subject}</h3>
                <p>{$content}</p>
                <p style='margin-top: 20px;'>Thank you for staying connected with Barangay Balas.</p>
            </div>
            <div style='background: #f1f1f1; padding: 15px; text-align: center; font-size: 13px; color: #555;'>
                © " . date('Y') . " Barangay Balas, Mexico, Pampanga. All rights reserved.
            </div>
        </div>
    </div>";

    return [
        'subject' => "Barangay Balas Announcement: {$subject}",
        'message' => $message
    ];
}
?>
