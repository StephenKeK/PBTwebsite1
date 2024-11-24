<?php
session_start();
include 'db_connection.php';

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
            $_SESSION['message'] = "Course created successfully!";
        }else{
            $_SESSION['error'] = "Error course creation request: " . $conn->error;
        }
    }else{
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
