<!-- timetable_parent.php -->
<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['parent_logged_in'])) {
    header("Location: login.php");
    exit();
}

$parent_id = $_SESSION['parent_id'];
$result = $conn->query("SELECT * FROM parents WHERE id = '$parent_id'");
$parent = $result->fetch_assoc();

// Handle date range input
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('monday this week'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('sunday this week'));

// Prepare and execute the SQL statement with date range
$timetableArray = $conn->prepare("SELECT * FROM child_course WHERE parent_id = ? AND DATE(start_datetime) BETWEEN ? AND ?");
$timetableArray->bind_param("sss", $parent_id, $start_date, $end_date);
$timetableArray->execute();
$result = $timetableArray->get_result();

// Check if any rows were returned
$timetable = [];
if ($result->num_rows > 0) {
    // Fetch all rows into an array
    $timetable = $result->fetch_all(MYSQLI_ASSOC);
}

// Close the statement
$timetableArray->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <div class="nav-brand">Progressive Brain Training</div>
        <ul class="nav-links">
            <li><a href="dashboard_parent.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="timetable_parent.php" class="active"><i class="fas fa-calendar-alt"></i> Timetable</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <div class="dashboard-container">
        <h1>Weekly Class Timetable</h1>

        <!-- Date range form -->
        <form method="GET" action="timetable_parent.php" class="date-range-form">
        <label for="start_date">Start Date:</label>
        <input type="date" name="start_date" id="start_date" value="<?= htmlspecialchars($start_date) ?>" required>
        
        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" id="end_date" value="<?= htmlspecialchars($end_date) ?>" required>

        <button type="submit">Filter</button>
    </form>

        <div class="timetable-container">
            <?php
            // Define time slots from 9 AM to 6 PM
            $startHour = 9;
            $endHour = 18;
            $daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

            // Create an associative array to hold the timetable data for easy access
            $timetableData = [];
            foreach ($timetable as $entry) {
                $day = date('l', strtotime($entry['start_datetime']));
                $time = date('H:i:s', strtotime($entry['start_datetime']));
                $timetableData[$day][$time] = 'X';
            }
            ?>

            <table class="timetable">
                <tr>
                    <th>Time</th>
                    <?php foreach ($daysOfWeek as $day): ?>
                        <th><?= $day ?></th>
                    <?php endforeach; ?>
                </tr>
                <?php for ($hour = $startHour; $hour <= $endHour; $hour++): ?>
                    <?php
                    // Format time for display
                    $displayTime = date("g:00 A", strtotime("$hour:00"));
                    ?>
                    <tr>
                        <td><?= $displayTime ?></td>
                        <?php foreach ($daysOfWeek as $day): ?>
                            <td>
                                <?php
                                $timeSlot = sprintf("%02d:00:00", $hour); 
                                echo isset($timetableData[$day][$timeSlot]) ? 'X' : '';
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </table>
        </div>
    </div>
</body>

</html>
