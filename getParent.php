<?php
// Database connection
include 'db_connect.php';

$query = "SELECT id, name FROM parents";
$result = $db->query($query);

$options = '';

while ($row = $result->fetch_assoc()) {
    $options .= "<option value='{$row['id']}'>{$row['name']}</option>";
}

echo $options;

$db->close();
?>