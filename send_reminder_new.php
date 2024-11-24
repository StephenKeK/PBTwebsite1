<?php
include 'db_connection.php'; // Include the database connection
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('Asia/Kuala_Lumpur'); // Set your timezone

// Get the current date and time
$current_day = date('d'); // Format: YYYY-MM-DD
$current_time = date('H:i'); // Format: 24-hour time


// Fetch parents whose scheduled date and time match the current date and time (within a 5-minute window)
$query = "
    SELECT * FROM schedule_reminder sr 
    WHERE sr.schedule_day = ? 
    AND TIME_FORMAT(sr.schedule_time, '%H:%i') = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $current_day, $current_time);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $queryParent = "
    SELECT * FROM parents
    ";
    $stmtParent = $conn->prepare($queryParent);
    $stmtParent->execute();
    $resultParent = $stmtParent->get_result();


    while ($row = $resultParent->fetch_assoc()) {
        $parent_email = $row['email'];
        $parent_name = $row['parent_name'];
        $child_name = $row['child_name'];
        
        // Send email reminder
        $mail = new PHPMailer(true);
        try {
            // SMTP server configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'stephooi2000@gmail.com'; // Your Gmail address
            $mail->Password = 'yerw bczk csna ilir'; // Your app password for Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom('stephson2000@gmail.com', 'Brain Training Admin');
            $mail->addAddress($parent_email, $parent_name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Payment Reminder: Upcoming Brain Training Session';
            $mail->Body    = "<p>Dear $parent_name,</p>
                              <p>This is a reminder that payment for your child $child_name's brain training session is due. Please ensure it is completed at your earliest convenience.</p>
                              <p>Thank you!</p>";
                              
            $mail->send();
            echo 'Payment reminder email sent to ' . $parent_email;
        } catch (Exception $e) {
            echo "Failed to send email to $parent_email. Mailer Error: {$mail->ErrorInfo}";
        }
    }
} else {
    echo 'No payment reminders to send.';
}

$conn->close();
?>
