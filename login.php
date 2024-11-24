<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Progressive Brain Training</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h1 class="login-title">Progressive Brain Training Login</h1>
        <?php
        if (isset($_GET['error']) && $_GET['error'] === 'invalid_credentials') {
            echo '<p class="error-message">Invalid email or password. Please try again.</p>';
        }
        ?>
        <form method="POST" action="process_login.php" class="login-form">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Log In" class="login-btn">
            </div>
        </form>
        <div class="login-links">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <!-- <p><a href="reset_password.php" id="reset-password-link">Forgot Password?</a></p> -->
        </div>
    </div>
</body>
</html>
