<?php
session_start();
include 'db_connection.php';

// Check if the form is submitted and required fields are present
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if reminder_day and start_time are set
    if (isset($_POST['reminder_day']) && isset($_POST['start_time'])) {
        
        $schedule_day = $conn->real_escape_string($_POST['reminder_day']);
        $schedule_time = $conn->real_escape_string($_POST['start_time']);
        
        $id = 1; // Replace 1 with the actual id or use a variable

        $update_query = "UPDATE schedule_reminder SET 
            schedule_day = '$schedule_day', 
            schedule_time = '$schedule_time'  
            WHERE 1"; // Update WHERE clause as necessary

        // Execute query and check if successful
        if ($conn->query($update_query) === TRUE) {
            $_SESSION['message'] = "Requested schedule change has been approved";
        } else {
            $_SESSION['error'] = "Error approving request: " . $conn->error;
        }

        // Redirect back to the admin dashboard
        header("Location: dashboard_admin.php");
        exit();
    } else {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: dashboard_admin.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: dashboard_admin.php");
    exit();
}
?>