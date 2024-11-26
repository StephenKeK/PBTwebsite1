<?php
session_start();
include 'db_connection.php';
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if($action == "create"){
        $parent_id = $conn->real_escape_string($_POST['parent_id']);
        $start_datetime = $conn->real_escape_string($_POST['start_datetime']);
        $end_datetime = $conn->real_escape_string($_POST['end_datetime']);
        $course_name = $conn->real_escape_string($_POST['course_name']);

        $insert_query = "INSERT INTO child_course  (course_name, start_datetime, end_datetime, 
        reject_reason, status_id, parent_id, request_start_datetime, request_end_datetime) 
        VALUES ('$course_name','$start_datetime','$end_datetime',NULL,1,'$parent_id',NULL,NULL)";

        

        if ($conn->query($insert_query) === TRUE) {
            $read_query = "SELECT * FROM parents p WHERE p.id = ?";
            $stmt = $conn->prepare($read_query);
            $stmt->bind_param('s', $parent_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $parent_email = $row['email'];
                    $parent_name = $row['parent_name'];
                    $child_name = $row['child_name'];

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
                        $mail->Subject = 'New Course Created';
                        $mail->Body = "
                            <div style='font-family: Arial, sans-serif; color: #333;'>
                                <p style='font-size: 16px;'>Dear Parent,</p>
                                <p>The admin has scheduled a new course for your child</p>
                                <p>Kindly attend the course based on the new date and time. Below are the course schedule details:</p>
                                
                                <div style='border: 1px solid #ddd; padding: 10px; margin-top: 10px; background-color: #f9f9f9;'>
                                    <p><b>Parent Name:</b> $parent_name</p>
                                    <p><b>Child Name:</b> $child_name</p>
                                    <p><b>Course Name:</b> $course_name</p>
                                    <p><b>Start Date & Time:</b> $start_datetime</p>
                                    <p><b>End Date & Time:</b> $end_datetime</p>
                                </div>
                                
                                <p style='font-size: 14px; color: #555; margin-top: 20px;'>Thank you!</p>
                            </div>
                        ";
                                        
                        $mail->send();
                        $_SESSION['message'] = "Email Sent successfully!";
                    } catch (Exception $e) {
                        $_SESSION['error'] = "Error email: " . $conn->error;
                    }
                }
            }else{
                $_SESSION['error'] = "Error approving schedule change request.";
            }

        }else{
            $_SESSION['error'] = "Error course creation request: " . $conn->error;
        }
    }
    else if($action == "update"){
        $course_id = $conn->real_escape_string($_POST['course_id']);
        $start_datetime = $conn->real_escape_string($_POST['start_datetime']);
        $end_datetime = $conn->real_escape_string($_POST['end_datetime']);
        $course_name = $conn->real_escape_string($_POST['course_name']);

        $select_query = "SELECT * FROM parents WHERE email = $email"; // Update WHERE clause as necessary
        

        $update_query = "UPDATE child_course SET 
            course_name = '$course_name', 
            start_datetime = '$start_datetime',
            end_datetime = '$end_datetime' 
            WHERE id='$course_id'"; // Update WHERE clause as necessary

        // Execute query and check if successful
        if ($conn->query($update_query) === TRUE) {
            $_SESSION['message'] = "Update successfully";
        } else {
            $_SESSION['error'] = "Error approving request: " . $conn->error;
        }
        
    }
    else{
        $course_id = $conn->real_escape_string($_POST['course_id']);
        $delete_query = "DELETE FROM child_course WHERE id = '$course_id'";

        if ($conn->query($delete_query) === TRUE) {
            $_SESSION['message'] = "Course deleted successfully!";
        }else{
            $_SESSION['error'] = "Error course deletion request: " . $conn->error;
        }
    }
    header("Location: dashboard_admin.php"); 
    exit();
}
?>
