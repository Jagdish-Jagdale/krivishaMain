<?php
$conn = new mysqli('localhost', 'root', '', 'krivisha');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, task_id, date, last_updated_date, created_on, department_id, order_department_status FROM tbl_auto_task_list WHERE task_id = 'ORD-2473'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "0 results";
}
$conn->close();
?>
