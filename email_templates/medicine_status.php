<?php

function sendMedicineStatusEmail($conn, $request_id, $status, $admin_notes, $disapproval_reason) {
    $stmt = $conn->prepare("
        SELECT mr.request_number, mr.medicine_name, r.email, r.first_name, r.last_name 
        FROM medicine_requests mr 
        JOIN residents r ON mr.resident_id = r.id 
        WHERE mr.id = ?
    ");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $request = $result->fetch_assoc()) {
        $to = $request['email'];
        $subject = "Medicine Request {$status} - " . $request['request_number'];

        $color = $status === 'Approved' ? '#4CAF50' : ($status === 'Disapproved' ? '#E53935' : '#FF9800');

        $disapprovalSection = ($status === 'Disapproved' && !empty($disapproval_reason))
            ? "<p><strong>Reason for Disapproval:</strong> {$disapproval_reason}</p>"
            : '';

        $adminNoteSection = !empty($admin_notes)
            ? "<p><strong>Admin Note:</strong> {$admin_notes}</p>"
            : '';

        $body = "
        <div style='font-family: Arial, sans-serif; background: #f8f9fa; padding: 20px;'>
            <div style='max-width: 600px; margin: auto; background: white; border-radius: 8px; overflow: hidden;'>
                <div style='background: linear-gradient(90deg, #0033cc, #3a7cb9); color: white; padding: 15px 25px;'>
                    <h2 style='margin: 0;'>Barangay Balas, Mexico, Pampanga</h2>
                </div>
                <div style='padding: 25px;'>
                    <h3 style='color: {$color};'>Medicine Request {$status}</h3>
                    <p>Dear <strong>{$request['first_name']} {$request['last_name']}</strong>,</p>
                    <p>Your medicine request <strong>{$request['request_number']}</strong> for <strong>{$request['medicine_name']}</strong> has been <strong>{$status}</strong> by the Barangay Balas administration.</p>
                    {$disapprovalSection}
                    {$adminNoteSection}
                    <p style='margin-top: 20px;'>Thank you for using the Barangay Balas Online Services and Management System.</p>
                </div>
                <div style='background: #f1f1f1; padding: 15px; text-align: center; font-size: 13px; color: #555;'>
                    © " . date('Y') . " Barangay Balas, Mexico, Pampanga. All rights reserved.
                </div>
            </div>
        </div>";

        sendEmail($to, $subject, $body);
    }

    $stmt->close();
}
?>
