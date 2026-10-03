<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = 'localhost';
$dbname = 'skill_pro_institute';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get ALL students from prereg_students - using ID
    $pendingQuery = "SELECT 
                        ID,
                        first_name,
                        last_name,
                        email,
                        program,
                        faculty
                    FROM prereg_students 
                    LIMIT 10";
    
    $pendingStmt = $pdo->prepare($pendingQuery);
    $pendingStmt->execute();
    $pendingStudents = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get approved students from student table - using stID
    $approvedQuery = "SELECT 
                        stID,
                        first_name,
                        last_name,
                        email,
                        program,
                        faculty
                    FROM student 
                    LIMIT 10";
    
    $approvedStmt = $pdo->prepare($approvedQuery);
    $approvedStmt->execute();
    $approvedStudents = $approvedStmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response
    echo json_encode([
        'success' => true,
        'pending' => $pendingStudents,
        'approved' => $approvedStudents,
        'debug_info' => [
            'prereg_students_count' => count($pendingStudents),
            'student_table_count' => count($approvedStudents)
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>