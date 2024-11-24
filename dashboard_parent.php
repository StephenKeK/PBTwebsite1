<?php
session_start();
include 'db_connection.php';

//Capture alert message
$alertMessage = "";
if (isset($_SESSION['message'])) {
    $alertMessage = $_SESSION['message'];
    unset($_SESSION['message']);
} elseif (isset($_SESSION['error'])) {
    $alertMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Parent authentication
if (!isset($_SESSION['parent_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Fetch parent data
$parent_name = $_SESSION['parent_name'];
$parent_id = $_SESSION['parent_id'];

// Fetch courses based on status_id
$result_awaiting = $conn->query("SELECT c.*,status_name,child_name FROM child_course c LEFT JOIN status s ON c.status_id = s.id LEFT JOIN parents p ON c.parent_id = p.id WHERE c.parent_id = '$parent_id' AND c.start_datetime > NOW()");
$result_status_0 = $conn->query("SELECT c.*,status_name,child_name FROM child_course c LEFT JOIN status s ON c.status_id = s.id LEFT JOIN parents p ON c.parent_id = p.id WHERE c.parent_id = '$parent_id' AND c.status_id = 0 AND c.start_datetime > NOW()");
$result_status_neg1 = $conn->query("SELECT c.*,status_name,child_name FROM child_course c LEFT JOIN status s ON c.status_id = s.id LEFT JOIN parents p ON c.parent_id = p.id WHERE c.parent_id = '$parent_id' AND c.status_id = -1 AND c.start_datetime > NOW()");
$result_status_1 = $conn->query("SELECT c.*,status_name,child_name FROM child_course c LEFT JOIN status s ON c.status_id = s.id LEFT JOIN parents p ON c.parent_id = p.id WHERE c.parent_id = '$parent_id' AND c.status_id = 1 AND c.start_datetime > NOW()");
$result_completed = $conn->query("SELECT c.*,status_name,child_name FROM child_course c LEFT JOIN status s ON c.status_id = s.id LEFT JOIN parents p ON c.parent_id = p.id WHERE c.parent_id = '$parent_id' AND c.start_datetime <= NOW()");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-button { cursor: pointer; padding: 10px; margin: 5px; margin-bottom: 0; background-color: #f1f1f1; border: none;margin-left: 0px; }
        .tab-button.active { background-color: #ff8e2b; color:white }
        
    </style>
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
    <h1>Parent Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars($parent_name); ?>!</p>

    <!-- Tabs -->
    <div class="tab-buttons">
        <button class="tab-button active" onclick="showTab('status_awaiting')">Awaiting</button>
        <button class="tab-button" onclick="showTab('status_0')">Pending</button>
        <button class="tab-button" onclick="showTab('status_neg1')">Rejected</button>
        <button class="tab-button" onclick="showTab('status_1')">Approved</button>
        <button class="tab-button" onclick="showTab('status_completed')">Completed</button>
    </div>

     <div id="status_awaiting" class="tab-content active">
        <table class="responsive-table">
            <thead>
                <tr>
                    <th class="hidden-column">ID</th>
                    <th>No.</th>
                    <th>Course Name</th>
                    <th>Status</th>
                    <th>Course Start Date & Time</th>
                    <th>Course End Date & Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $increment = 1;
                while ($row = $result_awaiting->fetch_assoc()): 
                ?>
                <tr>
                    <td class="hidden-column"><?= $row['id'] ?></td>
                    <td><?= $increment++ ?></td>
                    <td><?= $row['course_name'] ?></td>
                    <td><?= $row['status_name'] ?></td>
                    <td><?= $row['start_datetime'] ?></td>
                    <td><?= $row['end_datetime'] ?></td>
                    <?php if (is_null($row['request_start_datetime'])): ?>
                        <td><button class="action-button" onclick="openModal(<?= htmlspecialchars(json_encode($row)) ?>)">Request Change Schedule</button></td>
                    <?php else: ?>
                        <td><button class="disabled-action-button" disabled>Request Submitted</button></td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="status_completed" class="tab-content">
        <table class="responsive-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Course Name</th>
                    <th>Status</th>
                    <th>Course Start Date & Time</th>
                    <th>Course End Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $increment=1;
                while ($row = $result_completed->fetch_assoc()): ?>
                <tr>
                    <td><?= $increment++ ?></td>
                    <td><?= $row['course_name'] ?></td>
                    <td><?= $row['status_name'] ?></td>
                    <td><?= $row['start_datetime'] ?></td>
                    <td><?= $row['end_datetime'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Courses with Status ID 0 -->
    <div id="status_0" class="tab-content">
        <table class="responsive-table">
            <thead>
                <tr>
                <th class="hidden-column">ID</th>
                    <th>No.</th>
                    <th>Course Name</th>
                    <th>Status</th>
                    <th>Course Start Date & Time</th>
                    <th>Course End Date & Time</th>
                    <th>Requested New Start Date & Time</th>
                    <th>Requested New End Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $increment = 1;
                while ($row = $result_status_0->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= $increment++ ?></td>
                    <td><?= $row['course_name'] ?></td>
                    <td><?= $row['status_name'] ?></td>
                    <td><?= $row['start_datetime'] ?></td>
                    <td><?= $row['end_datetime'] ?></td>
                    <td><?= $row['request_start_datetime'] ?></td>
                    <td><?= $row['request_end_datetime'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Courses with Status ID -1 -->
    <div id="status_neg1" class="tab-content">
        <table class="responsive-table">
            <thead>
                <tr>
                    <th class="hidden-column">ID</th>
                    <th>No.</th>
                    <th>Course Name</th>
                    <th>Status</th>
                    <th>Rejected Reason</th>
                    <th>Course Start Date & Time</th>
                    <th>Course End Date & Time</th>
                    <th>Requested New Start Date & Time</th>
                    <th>Requested New End Date & Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $increment=1;
                while ($row = $result_status_neg1->fetch_assoc()): 
                
                ?>
                <tr>
                    <td class="hidden-column"><?= $row['id'] ?></td>
                    <td><?= $increment++ ?></td>
                    <td><?= $row['course_name'] ?></td>
                    <td><?= $row['status_name'] ?></td>
                    <td><?= $row['reject_reason'] ?></td>
                    <td><?= $row['start_datetime'] ?></td>
                    <td><?= $row['end_datetime'] ?></td>
                    <td><?= $row['request_start_datetime'] ?></td>
                    <td><?= $row['request_end_datetime'] ?></td>
                    <td><button class="action-button" onclick="openModal(<?= htmlspecialchars(json_encode($row)) ?>) ">Request Again</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Courses with Status ID 1 -->
    <div id="status_1" class="tab-content">
        <table class="responsive-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Course Name</th>
                    <th>Status</th>
                    <th>Course Start Date & Time</th>
                    <th>Course End Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $increment=1;
                while ($row = $result_status_1->fetch_assoc()): ?>
                <tr>
                    <td><?=  $increment++ ?></td>
                    <td><?= $row['course_name'] ?></td>
                    <td><?= $row['status_name'] ?></td>
                    <td><?= $row['start_datetime'] ?></td>
                    <td><?= $row['end_datetime'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <p><a href="logout.php" class="logout-link">Logout</a></p>
</div>

<!-- Modal Overlay -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>

<!-- Modal -->
<div class="modal" id="modal">
    <form id="modifyForm" action="reqeust_schedule_change.php" method="POST">
        <h2>Modify Course</h2>
        <input type="hidden" name="id" id="courseId">
        <div class="input-container">
            <label for="courseName" class="input-title-request">Course Name:</label>
            <input type="text" name="course_name" id="courseName" class="input-text-request" disabled>
        </div>

        <div class="input-container">
            <label for="startDatetime" class="input-title-request">Start Date & Time:</label>
            <input type="datetime-local" name="start_datetime" id="startDatetime" class="input-text-request" required>
        </div>

        <div class="input-container">
            <label for="endDatetime" class="input-title-request">End Date & Time:</label>
            <input type="datetime-local" name="end_datetime" id="endDatetime" class="input-text-request" required>
        </div>
        
        <div class="modal-button-container">
            <button type="button" onclick="closeModal()" class="close-button">Close</button>
            <button type="submit" class="submit-button">Submit</button>
        </div>
                
    </form>
</div>

<?php if (!empty($alertMessage)): ?>
    <script>
     alert("<?php echo $alertMessage; ?>");
        
    </script>
<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        document.querySelectorAll('.tab-button').forEach(button => button.classList.remove('active'));
        document.querySelector(`[onclick="showTab('${tabId}')"]`).classList.add('active');
    }

    function openModal(rowData) {
        document.getElementById('courseId').value = rowData.id;
        document.getElementById('courseName').value = rowData.course_name;
        document.getElementById('startDatetime').value = rowData.start_datetime;
        document.getElementById('endDatetime').value = rowData.end_datetime;
        document.getElementById('modal').classList.add('active');
        document.getElementById('modalOverlay').classList.add('active');
    }

    function closeModal() {
        document.getElementById('modal').classList.remove('active');
        document.getElementById('modalOverlay').classList.remove('active');
    }

    window.showTab = showTab; // Expose functions to global scope if they are called inline
    window.openModal = openModal;
    window.closeModal = closeModal;
});
</script>

</body>
</html>
