<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$host = 'localhost';
$dbname = 'skill_pro_institute';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check all status values in prereg_students
    $statusQuery = "SELECT status, COUNT(*) as count FROM prereg_students GROUP BY status";
    $stmt = $pdo->prepare($statusQuery);
    $stmt->execute();
    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check total students and some sample data
    $sampleQuery = "SELECT ID, first_name, last_name, email, status FROM prereg_students LIMIT 5";
    $sampleStmt = $pdo->prepare($sampleQuery);
    $sampleStmt->execute();
    $sampleStudents = $sampleStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'status_distribution' => $statusCounts,
        'sample_students' => $sampleStudents,
        'total_students' => count($sampleStudents)
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>