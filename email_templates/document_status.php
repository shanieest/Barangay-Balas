<?php
function requestStatusEmail($name, $documentType, $status, $note = '', $downloadUrl = null) {
    $subject = "Your {$documentType} Request has been {$status}";
    $color = $status === 'Approved' ? '#4CAF50' : '#E53935';
    $noteSection = $note ? "<p style='background: #f8f9fa; padding: 12px; border-radius: 4px;'><strong>Admin Note:</strong> {$note}</p>" : '';

    // Download button for approved requests
    $downloadButton = '';
    if ($status === 'Approved' && $downloadUrl) {
        $downloadButton = "
        <div style='text-align: center; margin: 25px 0;'>
            <a href='{$downloadUrl}' 
               style='display: inline-block; background: #2196F3; color: white; 
                      padding: 14px 28px; text-decoration: none; border-radius: 4px; 
                      font-weight: bold; font-size: 16px; border: none; cursor: pointer;'>
               📥 Download Your {$documentType}
            </a>
            <p style='color: #666; font-size: 14px; margin-top: 10px;'>
                <strong>Note:</strong> Digital copy with watermark. Visit office for official hard copy.
            </p>
        </div>";
    }

    $message = "
    <div style='font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px;'>
        <div style='max-width: 600px; margin: auto; background: white; border-radius: 8px; 
            overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
            <div style='background: linear-gradient(90deg, #0033cc, #3a7cb9); color: white; padding: 20px 25px; text-align: center;'>
                <h2 style='margin: 0;'>Barangay Balas, Mexico, Pampanga</h2>
            </div>
            <div style='padding: 30px;'>
                <div style='background: {$color}; color: white; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; text-align: center;'>
                    <h3 style='margin: 0;'>Request {$status}</h3>
                </div>
                
                <p>Hello, <strong>{$name}</strong>,</p>
                <p>Your <strong>{$documentType}</strong> request has been <strong>{$status}</strong> by the Barangay Balas administration.</p>
                
                {$downloadButton}
                {$noteSection}
                
                <div style='background: #e7f3ff; padding: 15px; border-radius: 4px; margin-top: 20px;'>
                    <p style='margin: 0; color: #1976D2; font-size: 14px;'>
                        <strong>🏢 Barangay Balas Office:</strong><br>
                        Visit us for official stamped copies and assistance.
                    </p>
                </div>
                
                <p style='margin-top: 20px; color: #666;'>Thank you for using the Barangay Balas Online Services and Management System.</p>
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