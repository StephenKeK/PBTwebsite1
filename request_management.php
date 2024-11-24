<?php
session_start();
include 'db_connection.php';
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the form is submitted and required fields are present
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $courseId = $_POST['id'] ?? '';
    
    if($action == "approve"){
        $course_id = $conn->real_escape_string($_POST['id']);
        $request_start_datetime = $conn->real_escape_string($_POST['request_start_datetime']);
        $request_end_datetime = $conn->real_escape_string($_POST['request_end_datetime']);

        $update_query = "UPDATE child_course SET 
        start_datetime = '$request_start_datetime', 
        end_datetime = '$request_end_datetime',
        request_start_datetime = NULL, 
        request_end_datetime = NULL,
        status_id = '1' 
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
                        $mail->addAddress($parent_email, $parent_name);
                        
                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Approve of Course Schedule Change Request';
                        $mail->Body = "
                            <div style='font-family: Arial, sans-serif; color: #333;'>
                                <p style='font-size: 16px;'>Dear Admin,</p>
                                <p>The admin has approved your course schedule change request.</p>
                                <p>Kindly attend the course based on the new date and time. Below are the course new schedule details:</p>
                                
                                <div style='border: 1px solid #ddd; padding: 10px; margin-top: 10px; background-color: #f9f9f9;'>
                                    <p><b>Parent Name:</b> $parent_name</p>
                                    <p><b>Child Name:</b> $child_name</p>
                                    <p><b>Course Name:</b> $child_course</p>
                                    <p><b>Start Date & Time:</b> $request_start_datetime</p>
                                    <p><b>End Date & Time:</b> $request_end_datetime</p>
                                </div>
                                
                                <p style='font-size: 14px; color: #555; margin-top: 20px;'>Thank you!</p>
                            </div>
                        ";
                                        
                        $mail->send();
                        $_SESSION['message'] = "Schedule change request approved successfully!";
                    } catch (Exception $e) {
                        $_SESSION['error'] = "Error approving schedule change request: " . $conn->error;
                    }
                }
            }else{
                $_SESSION['error'] = "Error approving schedule change request.";
            }
        } else {
            $_SESSION['error'] = "Error approve request: " . $conn->error;
        }

        // Redirect back to the parent dashboard
        header("Location: dashboard_admin.php"); 
        exit();
    }
    else
    {
        $course_id = $conn->real_escape_string($_POST['id']);
        $reject_reason = $conn->real_escape_string($_POST['reject_reason']);

        $update_query = "UPDATE child_course SET 
        reject_reason = '$reject_reason', 
        status_id = '-1',
        request_start_datetime = NULL, 
        request_end_datetime = NULL 
        WHERE id = '$course_id'";

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
                    $mail->addAddress($parent_email, $parent_name);
                    
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Reject of Course Schedule Change Request';
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; color: #333;'>
                            <p style='font-size: 16px;'>Dear Admin,</p>
                            <p>The admin has rejected your course schedule change request.</p>
                            <p><b>Reject Reason : </b> $reject_reason</p>
                            <p>Kindly attend the course based on the original date and time. Below are the course schedule details:</p>
                            
                            <div style='border: 1px solid #ddd; padding: 10px; margin-top: 10px; background-color: #f9f9f9;'>
                                <p><b>Parent Name:</b> $parent_name</p>
                                <p><b>Child Name:</b> $child_name</p>
                                <p><b>Course Name:</b> $child_course</p>
                                <p><b>Start Date & Time:</b> $start_datetime</p>
                                <p><b>End Date & Time:</b> $end_datetime</p>
                            </div>
                            
                            <p style='font-size: 14px; color: #555; margin-top: 20px;'>Thank you!</p>
                        </div>
                    ";


                                    
                    $mail->send();
                    $_SESSION['message'] = "Schedule change request rejected successfully!";
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error rejecting schedule change request: " . $conn->error;
                }
            }
        }else{
            $_SESSION['error'] = "Error rejecting schedule change request.";
        }
    } else {
        $_SESSION['error'] = "Error approve request: " . $conn->error;
    }

        // Redirect back to the parent dashboard
        header("Location: dashboard_admin.php"); 
        exit();
    }
    
} else {
    // If accessed incorrectly
    $_SESSION['error'] = "Invalid request.";
    //header("Location: dashboard_admin.php");
    exit();
}
?>
