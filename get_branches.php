<?php
$conn = mysqli_connect("localhost","root","","skill_pro_institute");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT branch_Name FROM branches WHERE status = 'Active'";
$result = $conn->query($sql);

$branches = array();
while($row = $result->fetch_assoc()) {
    $branches[] = $row;
}

echo json_encode($branches);
$conn->close();
?>