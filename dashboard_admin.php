<?php
session_start();
include 'db_connection.php'; // Include the database connection

//Capture alert message
$alertMessage = "";
if (isset($_SESSION['message'])) {
    $alertMessage = $_SESSION['message'];
    unset($_SESSION['message']);
} elseif (isset($_SESSION['error'])) {
    $alertMessage = $_SESSION['error'];
    unset($_SESSION['error']);
}


// Admin authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php"); // Redirect to login if not logged in as admin
    exit();
}

// Fetch courses based on status_id
$result_status_0 = $conn->query("SELECT c.*,status_name,parent_name,child_name FROM child_course c LEFT JOIN status s ON c.status_id = s.id LEFT JOIN parents p ON c.parent_id = p.id WHERE c.status_id = 0 AND c.start_datetime > NOW()");
$result_status_neg1 = $conn->query("SELECT c.*,status_name,parent_name,child_name FROM child_course c LEFT JOIN status s ON c.status_id = s.id LEFT JOIN parents p ON c.parent_id = p.id WHERE c.status_id = -1 AND c.start_datetime > NOW()");
$result_status_1 = $conn->query("SELECT c.*,status_name,parent_name,child_name FROM child_course c LEFT JOIN status s ON c.status_id = s.id LEFT JOIN parents p ON c.parent_id = p.id WHERE c.status_id = 1 AND c.start_datetime > NOW()");
$result_all = $conn->query("SELECT c.*,status_name,parent_name,child_name FROM child_course c LEFT JOIN status s ON c.status_id = s.id LEFT JOIN parents p ON c.parent_id = p.id");

//Get Parent
$parent_list = $conn->query("SELECT * FROM parents");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .tab-button { cursor: pointer; padding: 10px; margin: 5px; margin-bottom: 0; background-color: #f1f1f1; border: none;margin-left: 0px; }
        .tab-button.active { background-color: #ff8e2b; color:white }
        
    </style>
</head>
<body>
<div class="dashboard-admin-container">
    <div class="admin-header-container ">
    <h1>Admin Dashboard</h1>
    <div >
        <button class="action-button" onclick="openCreateModal()" style="padding:15px">Add New Course</button>
        <button class="action-button" onclick="openSchedulerModal()" style="padding:15px">Set Payment Scheduler</button>
    </div>
    </div>
    <p>Welcome, Admin!</p>

    <!-- Tabs -->
    <div class="tab-buttons">
        <button class="tab-button active" onclick="showTab('status_0')">Pending</button>
        <button class="tab-button" onclick="showTab('status_neg1')">Rejected</button>
        <button class="tab-button" onclick="showTab('status_1')">Approved</button>
        <button class="tab-button" onclick="showTab('status_all')">All</button>
    </div>

    <div id="status_all" class="tab-content">
        <table class="responsive-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Parent Name</th>
                    <th>Child Name</th>
                    <th>Course Name</th>
                    <th>Status</th>
                    <th>Course Start Date & Time</th>
                    <th>Course End Date & Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $increment=1;
                while ($row = $result_all->fetch_assoc()): ?>
                <tr>
                    <td><?= $increment++ ?></td>
                    <td><?= $row['parent_name'] ?></td>
                    <td><?= $row['child_name'] ?></td>
                    <td><?= $row['course_name'] ?></td>
                    <td><?= $row['status_name'] ?></td>
                    <td><?= $row['start_datetime'] ?></td>
                    <td><?= $row['end_datetime'] ?></td>
                    <td>
                    <button class="reject-button" onclick="openDeleteModal(<?= htmlspecialchars(json_encode($row)) ?>) ">Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Courses with Status ID 0 -->
    <div id="status_0" class="tab-content active">
        <table class="responsive-table">
            <thead>
                <tr>
                <th class="hidden-column">ID</th>
                    <th>No.</th>
                    <th>Parent Name</th>
                    <th>Child Name</th>
                    <th>Course Name</th>
                    <th>Status</th>
                    <th>Course Start Date Time</th>
                    <th>Course End Date Time</th>
                    <th>Requested Start Date Time</th>
                    <th>Requested End Date Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $increment = 1;
                while ($row = $result_status_0->fetch_assoc()): 
                ?>
                <tr>
                    <td><?= $increment++ ?></td>
                    <td><?= $row['parent_name'] ?></td>
                    <td><?= $row['child_name'] ?></td>
                    <td><?= $row['course_name'] ?></td>
                    <td><?= $row['status_name'] ?></td>
                    <td><?= $row['start_datetime'] ?></td>
                    <td><?= $row['end_datetime'] ?></td>
                    <td><?= $row['request_start_datetime'] ?></td>
                    <td><?= $row['request_end_datetime'] ?></td>
                    <td>
                        <button class="reject-button" onclick="openRejectModal(<?= htmlspecialchars(json_encode($row)) ?>) ">Reject</button>
                        <button class="action-button" onclick="openApproveModal(<?= htmlspecialchars(json_encode($row)) ?>) ">Approve</button>

                    </td>
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
                    <th>Parent Name</th>
                    <th>Child Name</th>
                    <th>Course Name</th>
                    <th>Status</th>
                    <th>Rejected Reason</th>
                    <th>Course Start Date & Time</th>
                    <th>Course End Date & Time</th>
                    <th>Requested New Start Date & Time</th>
                    <th>Requested New End Date & Time</th>
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
                    <td><?= $row['parent_name'] ?></td>
                    <td><?= $row['child_name'] ?></td>
                    <td><?= $row['course_name'] ?></td>
                    <td><?= $row['status_name'] ?></td>
                    <td><?= $row['reject_reason'] ?></td>
                    <td><?= $row['start_datetime'] ?></td>
                    <td><?= $row['end_datetime'] ?></td>
                    <td><?= $row['request_start_datetime'] ?></td>
                    <td><?= $row['request_end_datetime'] ?></td>
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
                    <th>Parent Name</th>
                    <th>Child Name</th>
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
                    <td><?= $increment++ ?></td>
                    <td><?= $row['parent_name'] ?></td>
                    <td><?= $row['child_name'] ?></td>
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
<div class="modal-overlay" id="approve-modalOverlay" onclick="closeApproveModal()"></div>

<div class="modal-overlay" id="create-modalOverlay" onclick="closeCreateModal()"></div>

<div class="modal-overlay" id="reject-modalOverlay" onclick="closeRejectModal()"></div>

<div class="modal-overlay" id="delete-modalOverlay" onclick="closeDeleteModal()"></div>

<div class="modal-overlay" id="scheduler-modalOverlay" onclick="closeSchedulerModal()"></div>

<!-- Modal -->
<div class="modal" id="create-modal">
    <form id="modifyForm" action="course_management.php" method="POST">
        <p class="modal-title">Create the new course</p>
        <input type="hidden" name="status-id" id="statusId" value="1">
        <input type="hidden" name="action" value="create">  
        <div class="input-container">
            <label for="parentSelect" class="input-title-request">Select Parent: </label>
            <select id="parentSelect" name="parent_id" class="input-text-request    ">
            <?php
            while ($row = $parent_list->fetch_assoc()) {
                echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['parent_name']) .' ('
                .htmlspecialchars($row['child_name']).')'. '</option>';
            }
            ?>
            </select>
        </div> 

        <div class="input-container">
            <label for="courseName" class="input-title-request">Course Name</label>
            <input type="text" name="course_name" id="courseName" class="input-text-request" required>
        </div>
    
        <div class="input-container">
            <label for="courseStartDatetime" class="input-title-request">Start Date & Time</label>
            <input type="datetime-local" name="start_datetime" id="coureStartDatetime" class="input-text-request" required>
        </div>

        <div class="input-container">
            <label for="courseEndDatetime" class="input-title-request">End Date & Time</label>
            <input type="datetime-local" name="end_datetime" id="courseEndDatetime" class="input-text-request" required>
        </div>

        <div class="modal-button-container">
            <button type="button" onclick="closeModal()" class="close-button">Close</button>
            <button type="submit" class="submit-button">Submit</button>
        </div>             
    </form>
</div>

<div class="modal" id="delete-modal">
    <form id="modifyForm" action="course_management.php" method="POST">
        <p class="modal-title">Do you want to delete the selected course?</p>
        <input type="hidden" name="course_id" id="deleteCourseId">
        <input type="hidden" name="action" value="delete">   
        <div class="modal-button-container">
            <button type="button" onclick="closeDeleteModal()" class="close-button">Cancel</button>
            <button type="submit" class="submit-button">Confirm</button>
        </div>       
    </form>
</div>

<div class="modal" id="approve-modal">
    <form id="modifyForm" action="request_management.php" method="POST">
        <p class="modal-title">Do you want to approve the new schedule date & time requested by parent?</p>
        <input type="hidden" name="id" id="approveCourseId">
        <input type="hidden" name="action" value="approve">
        <input type="hidden" name="request_start_datetime" id="startDatetime">
        <input type="hidden" name="request_end_datetime" id="endDatetime">        
        <div class="modal-button-container">
            <button type="button" onclick="closeApproveModal()" class="close-button">Cancel</button>
            <button type="submit" class="submit-button">Confirm</button>
        </div>
        
                
    </form>
</div>

<div class="modal" id="reject-modal">
    <form id="modifyForm" action="request_management.php" method="POST">
        <p class="modal-title">Do you want to reject the new schedule date & time requested by parent?</p>
        <input type="hidden" name="id" id="rejectCourseId">
        <input type="hidden" name="action" value="reject">  
        <div class="input-container">
            <label for="rejectReason" class="input-title-request">Reject Reason:</label>
            <input type="text" name="reject_reason" id="rejectReason" class="input-text-request" required>
        </div> 
        <div class="modal-button-container">
            <button type="button" onclick="closeRejectModal()" class="close-button">Cancel</button>
            <button type="submit" class="submit-reject-button">Confirm</button>
        </div>
                
    </form>
</div>

<div class="modal" id="scheduler-modal">
    <form id="modifyForm" action="process_email_schedule.php" method="POST">
        <h2 class="modal-title">Set Schedule Date for Email Payment Reminder</h2>
        <div class="input-container">
            <label for="reminderDay" class="input-title-request">Day in every month for reminder trigerring:</label>
            <select name="reminder_day" id="reminderDay" class="input-text-request" required>
                <?php
                    for ($day = 1; $day <= 28; $day++) {
                        echo "<option value='$day'>$day</option>";
                    }
                ?>
            </select>
        </div> 
   
        <div class="input-container">
            <label for="startTime" class="input-title-request">Time in every month for reminder trigerring:</label>
            <input type="time" name="start_time" id="startTime" class="input-text-request" required>
        </div>

        <div class="modal-button-container">
            <button type="button" onclick="closeSchedulerModal()" class="close-button">Cancel</button>
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

    function openCreateModal() {
        document.getElementById('create-modal').classList.add('active');
        document.getElementById('create-modalOverlay').classList.add('active');
    }

    function openDeleteModal(rowData) {
        document.getElementById('deleteCourseId').value = rowData.id;
        document.getElementById('delete-modal').classList.add('active');
        document.getElementById('delete-modalOverlay').classList.add('active');
    }

    function openApproveModal(rowData) {
        document.getElementById('approveCourseId').value = rowData.id;
        document.getElementById('startDatetime').value = rowData.request_start_datetime;
        document.getElementById('endDatetime').value = rowData.request_end_datetime;
        document.getElementById('approve-modal').classList.add('active');
        document.getElementById('approve-modalOverlay').classList.add('active');
    }

    function openRejectModal(rowData) {
        document.getElementById('rejectCourseId').value = rowData.id;
        document.getElementById('rejectReason').value = "";
        document.getElementById('reject-modal').classList.add('active');
        document.getElementById('reject-modalOverlay').classList.add('active');
    }

    function openSchedulerModal(){
        document.getElementById('scheduler-modal').classList.add('active');
        document.getElementById('scheduler-modalOverlay').classList.add('active');
    }

    function closeCreateModal() {
        document.getElementById('create-modal').classList.remove('active');
        document.getElementById('create-modalOverlay').classList.remove('active');
    }

    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.remove('active');
        document.getElementById('delete-modalOverlay').classList.remove('active');
    }

    function closeApproveModal() {
        document.getElementById('approve-modal').classList.remove('active');
        document.getElementById('approve-modalOverlay').classList.remove('active');
    }

    function closeRejectModal() {
        document.getElementById('reject-modal').classList.remove('active');
        document.getElementById('reject-modalOverlay').classList.remove('active');
    }

    function closeSchedulerModal() {
        document.getElementById('scheduler-modal').classList.remove('active');
        document.getElementById('scheduler-modalOverlay').classList.remove('active');
    }


    window.showTab = showTab; // Expose functions to global scope if they are called inline
    window.openCreateModal = openCreateModal;
    window.closeCreateModal = closeCreateModal;
    window.openDeleteModal = openDeleteModal;
    window.closeDeleteModal = closeDeleteModal;
    window.openApproveModal = openApproveModal;
    window.closeApproveModal = closeApproveModal;
    window.openRejectModal = openRejectModal;
    window.closeRejectModal = closeRejectModal;
    window.openSchedulerModal = openSchedulerModal;
    window.closeSchedulerModal = closeSchedulerModal;
});
</script>

</body>
</html>
