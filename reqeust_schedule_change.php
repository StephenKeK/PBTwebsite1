<?php
session_start();
include 'db_connection.php';
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the form is submitted and required fields are present
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['start_datetime'], $_POST['end_datetime'])) {
    
    // Sanitize input data
    $course_id = $conn->real_escape_string($_POST['id']);
    $start_datetime = $conn->real_escape_string($_POST['start_datetime']);
    $end_datetime = $conn->real_escape_string($_POST['end_datetime']);

    // Update query
    $update_query = "UPDATE child_course SET 
                        request_start_datetime = '$start_datetime', 
                        request_end_datetime = '$end_datetime',
                        status_id = '0'
                     WHERE id = '$course_id'";

    // Execute query and check if successful
    if ($conn->query($update_query) === TRUE) {
        $read_query = "SELECT * FROM parents p LEFT JOIN child_course c ON p.id = c.parent_id WHERE c.id = ?";
        $stmt = $conn->prepare($read_query);
        $stmt->bind_param('s', $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $parent_email = $row['email'];
                $parent_name = $row['parent_name'];
                $child_name = $row['child_name'];
                $child_course = $row['course_name'];
                $start_datetime = $row['start_datetime'];
                $end_datetime = $row['end_datetime'];
                $request_start_datetime = $row['request_start_datetime'];
                $request_end_datetime = $row['request_end_datetime'];

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
                    $mail->setFrom('stephooi2000@gmail.com', 'Brain Training Admin');
                    $mail->addAddress("stephson2000@gmail.com", "Admin");
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Submission of Course Schedule Change Request';
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; color: #333;'>
                            <p style='font-size: 16px;'>Dear Admin,</p>
                            <p>The parent <strong>$parent_name</strong> has submitted a course schedule change request.</p>
                            <p>Kindly review it for approval. Below are the details of the request:</p>
                            
                            <div style='border: 1px solid #ddd; padding: 10px; margin-top: 10px; background-color: #f9f9f9;'>
                                <p><b>Parent Name:</b> $parent_name</p>
                                <p><b>Child Name:</b> $child_name</p>
                                <p><b>Course Name:</b> $child_course</p>
                                <p><b>Original Start Date & Time:</b> $start_datetime</p>
                                <p><b>Original End Date & Time:</b> $end_datetime</p>
                                <p><b>Requested New Start Date & Time by Parent:</b> $request_start_datetime</p>
                                <p><b>Requested New End Date & Time by Parent:</b> $request_end_datetime</p>
                            </div>
                            
                            <p style='font-size: 14px; color: #555; margin-top: 20px;'>Thank you!</p>
                        </div>
                    ";


                                    
                    $mail->send();
                    $_SESSION['message'] = "Schedule change request submitted successfully!";
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error submitting schedule change request: " . $conn->error;
                }
            }
        }else{
            $_SESSION['error'] = "Error submitting schedule change request.";
        }
    } else {
        $_SESSION['error'] = "Error submitting schedule change request: " . $conn->error;
    }
    

    // Redirect back to the parent dashboard
    header("Location: dashboard_parent.php"); 
    exit();
} else {
    // If accessed incorrectly
    $_SESSION['error'] = "Invalid request.";
    header("Location: dashboard_parent.php");
    exit();
}
?>
