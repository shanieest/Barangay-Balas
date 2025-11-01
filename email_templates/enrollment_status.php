<?php
// email_templates/enrollment_status.php

function generateEnrollmentConfirmationEmail($guardianName, $childName, $schoolYear, $socialWorkerName, $socialWorkerPosition, $socialWorkerDepartment, $socialWorkerEmail) {
    $subject = "Daycare Enrollment Confirmed - $childName (S.Y. $schoolYear)";

    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
        <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='color: white; margin: 0; font-size: 28px;'>✅ Enrollment Confirmed!</h1>
        </div>
        
        <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
            <div style='background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                <p style='font-size: 16px; color: #333;'>Dear <strong>{$guardianName}</strong>,</p>
                
                <p style='font-size: 15px; color: #555; line-height: 1.6;'>
                    We are delighted to inform you that the enrollment of your child, 
                    <strong>{$childName}</strong>, has been <strong style='color: #28a745;'>confirmed</strong> 
                    for the <strong>School Year {$schoolYear}</strong> at Barangay Balas Daycare Center.
                </p>
                
                <div style='background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                    <h3 style='margin: 0 0 10px 0; color: #2196F3; font-size: 16px;'>📋 Next Steps:</h3>
                    <ul style='margin: 0; padding-left: 20px; color: #555;'>
                        <li>Orientation will be scheduled soon</li>
                        <li>Please prepare the required documents</li>
                        <li>Wait for further instructions from our office</li>
                    </ul>
                </div>
                
                <p style='font-size: 15px; color: #555; line-height: 1.6;'>
                    If you have any questions or concerns, please don't hesitate to contact us.
                </p>
                
                <div style='margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;'>
                    <p style='margin: 5px 0; color: #666;'><strong>Confirmed by:</strong></p>
                    <p style='margin: 5px 0; color: #333; font-size: 16px;'><strong>{$socialWorkerName}</strong></p>
                    <p style='margin: 5px 0; color: #666;'>{$socialWorkerPosition}</p>
                    <p style='margin: 5px 0; color: #666;'>{$socialWorkerDepartment}</p>
                    <p style='margin: 5px 0; color: #666;'>📧 {$socialWorkerEmail}</p>
                </div>
            </div>
            
            <div style='text-align: center; margin-top: 20px; padding: 15px; color: #666; font-size: 13px;'>
                <p style='margin: 0;'>© " . date('Y') . " Barangay Balas Daycare Center</p>
                <p style='margin: 5px 0 0 0;'>Mexico, Pampanga</p>
            </div>
        </div>
    </div>";

    return [
        'subject' => $subject,
        'body' => $body
    ];
}

function sendInitialConfirmationEmail($guardianName, $childName) {
    $subject = "Enrollment Received for $childName - Barangay Balas Daycare Center";

    $content = "
        <p>Dear <strong>{$guardianName}</strong>,</p>
        <p>We are pleased to confirm that your enrollment for <strong>{$childName}</strong> has been successfully received by our daycare center.</p>
        <p>Our staff will review your application shortly. You will receive further updates once it has been approved.</p>
        <p>Thank you for trusting us with your child's early learning and development.</p>
        <p style='margin-top: 15px;'>Sincerely,<br><strong>Barangay Balas Daycare Center</strong></p>
    ";

    $message = "
    <div style='font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px;'>
        <div style='max-width: 600px; margin: auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(90deg, #0033cc, #3a7cb9); color: white; padding: 15px 25px;'>
                <h2 style='margin: 0;'>Barangay Balas Daycare Center</h2>
            </div>
            <div style='padding: 25px; color: #333;'>
                <h3 style='color: #0033cc; margin-top: 0;'>{$subject}</h3>
                {$content}
            </div>
            <div style='background: #f1f1f1; padding: 15px; text-align: center; font-size: 13px; color: #555;'>
                © " . date('Y') . " Barangay Balas Daycare Center, Mexico, Pampanga. All rights reserved.
            </div>
        </div>
    </div>";

    return [
        'subject' => $subject,
        'message' => $message
    ];
}
?>