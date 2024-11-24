<?php
// Include the database connection file
include 'db_connection.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $parent_name = $_POST['parent_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $child_name = $_POST['child_name'];
    $child_age = $_POST['child_age'];
    $child_school = $_POST['child_school'];

    // Check for duplicate email
    // showcase error handling
    $email_check_stmt = $conn->prepare("SELECT * FROM parents WHERE email = ?");
    $email_check_stmt->bind_param("s", $email);
    $email_check_stmt->execute();
    $result = $email_check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Duplicate email found
        echo "<h1>Error: Email already registered!</h1>";
        echo '<p><a href="register.php">Go back to registration</a></p>';
    } else {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO parents (parent_name, email, password, child_name, child_age, child_school) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $parent_name, $email, $hashed_password, $child_name, $child_age, $child_school);

        // Execute the statement
        if ($stmt->execute()) {
            echo "<h1>Registration Successful</h1>";
            echo "<p>Parent Name: $parent_name</p>";
            echo "<p>Email: $email</p>";
            echo "<p>Child Name: $child_name</p>";
            echo "<p>Child Age: $child_age</p>";
            echo "<p>Child School: $child_school</p>";
            echo '<p><a href="login.php">Click here to login</a></p>'; // Add login button/link
        } else {
            echo "<h1>Error: " . $stmt->error . "</h1>";
        }

        // Close statement
        $stmt->close();
    }

    // Close email check statement and connection
    $email_check_stmt->close();
    $conn->close();
}
?>
