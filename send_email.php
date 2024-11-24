<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Autoload PHPMailer using Composer

function sendPaymentReminder($parent_email, $parent_name, $child_name, $amount_due, $due_date) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
   
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stephooi2000@gmail.com';
        $mail->Password   = 'legimlmvdbqlrpyy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('stephooi2000@gmail.com', 'Brain Training Team');
        $mail->addAddress($parent_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Payment Reminder for Brain Training Program';
        $mail->Body    = "
        <html>
        <body>
            <p>Dear {$parent_name},</p>
            <p>This is a friendly reminder that the payment for {$child_name}'s brain training program is due soon.</p>
            <p><strong>Amount due:</strong> RM{$amount_due}</p>
            <p><strong>Due date:</strong> {$due_date}</p>
            <p>Please ensure that the payment is made before the due date to avoid any interruption in your child's training program.</p>
            <p>If you have already made the payment, please disregard this message.</p>
            <p>Thank you for your prompt attention to this matter.</p>
            <p>Best Regards,<br>Brain Training Team</p>
        </body>
        </html>
        ";

        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send email. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// This part will be used when called from admin_dashboard.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_payment_reminder') {
    $parent_email = filter_var($_POST['parent_email'], FILTER_SANITIZE_EMAIL);
    $parent_name = filter_var($_POST['parent_name'], FILTER_SANITIZE_STRING);
    $child_name = filter_var($_POST['child_name'], FILTER_SANITIZE_STRING);
    $amount_due = filter_var($_POST['amount_due'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $due_date = filter_var($_POST['due_date'], FILTER_SANITIZE_STRING);

    if (filter_var($parent_email, FILTER_VALIDATE_EMAIL)) {
        $result = sendPaymentReminder($parent_email, $parent_name, $child_name, $amount_due, $due_date);
        echo json_encode(['success' => $result]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    }
}