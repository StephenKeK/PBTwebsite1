<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Progressive Brain Training</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="registration-container">
        <h1>Progressive Brain Training Registration</h1>
        <form action="process_register.php" method="POST" class="registration-form">
            <div class="form-group">
                <label for="parent_name">Parent Name:</label>
                <input type="text" id="parent_name" name="parent_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="child_name">Child Name:</label>
                <input type="text" id="child_name" name="child_name" required>
            </div>
            <div class="form-group">
                <label for="child_age">Child Age:</label>
                <input type="number" id="child_age" name="child_age" min="0" required oninput="validateAgeInput(this)">
            </div>
            <div class="form-group">
                <label for="child_school">Child School:</label>
                <input type="text" id="child_school" name="child_school" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Register" class="registration-btn">
            </div>
        </form>
        <div class="login-links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script>
        function validateAgeInput(input) {
            if (input.value < 0) {
                input.value = '';
            }
        }
    </script>
</body>
</html>
