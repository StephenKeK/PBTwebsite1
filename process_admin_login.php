<?php
session_start(); // Start the session to track logged-in status

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_email = $_POST['admin_email'];
    $admin_password = $_POST['admin_password'];

    // Hardcoded admin credentials
    $hardcoded_email = 'admin@braintraining.com';
    $hardcoded_password = 'admin123';

    // Validate admin credentials
    if ($admin_email === $hardcoded_email && $admin_password === $hardcoded_password) {
        // Store login status in the session
        $_SESSION['admin_logged_in'] = true;

        // Redirect to the admin dashboard
        header('Location: dashboard_admin.php');
        exit();
    } else {
        // If login failed, redirect back to the login page with an error
        header('Location: login.php?error=invalid_credentials');
        exit();
    }
}
?>
