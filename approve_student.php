<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');


$host = 'localhost';
$dbname = 'skill_pro_institute';
$username = 'root';
$password = '';


$student_id = $_POST['student_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$student_id || !$action) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing student ID or action'
    ]);
    exit;
}

try {
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   
    $pdo->beginTransaction();

    if ($action === 'approve') {
        
        $selectQuery = "SELECT * FROM prereg_students WHERE ID = :id";
        $selectStmt = $pdo->prepare($selectQuery);
        $selectStmt->bindParam(':id', $student_id, PDO::PARAM_INT);
        $selectStmt->execute();
        $student = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            throw new Exception('Student not found');
        }

        
        $courseName = $student['program'] ?? '';
        $courseQuery = "SELECT course_ID FROM course WHERE course_Name = :course_name LIMIT 1";
        $courseStmt = $pdo->prepare($courseQuery);
        $courseStmt->bindParam(':course_name', $courseName, PDO::PARAM_STR);
        $courseStmt->execute();
        $course = $courseStmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            throw new Exception('Course not found in course table: ' . $courseName);
        }

        $courseID = $course['course_ID'];
        
        
        $branchCode = $student['branch'];
        $facultyAbbr = getFacultyAbbreviation($student['faculty']);
        $programAbbr = getProgramAbbreviation($student['program']);
        $studentNumber = substr($student['nic'], -6); 
        
        $generatedStID = $branchCode . '/' . $facultyAbbr . '/' . $programAbbr . '/' . $studentNumber;
        
        
        $insertQuery = "INSERT INTO student (
            stID, profile_image, first_name, last_name, email, username, password, 
            mobile_number, whatsapp_number, date_of_birth, nic, home_district, address, postcode,
            gender, faculty, enrollment_method, branch, program, program_id, intake
        ) VALUES (
            :stID, :profile_image, :first_name, :last_name, :email, :username, :password,
            :mobile_number, :whatsapp_number, :date_of_birth, :nic, :home_district, :address, :postcode,
            :gender, :faculty, :enrollment_method, :branch, :program, :program_id, :intake
        )";

        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([
            ':stID' => $generatedStID,
            ':profile_image' => $student['profile_image'],
            ':first_name' => $student['first_name'],
            ':last_name' => $student['last_name'],
            ':email' => $student['email'],
            ':username' => $student['username'],
            ':password' => $student['password'],
            ':mobile_number' => $student['mobile_number'],
            ':whatsapp_number' => $student['whatsapp_number'],
            ':date_of_birth' => $student['date_of_birth'],
            ':nic' => $student['nic'],
            ':home_district' => $student['home_district'],
            ':address' => $student['address'] ?? '',
            ':postcode' => $student['postcode'] ?? 0,
            ':gender' => $student['gender'],
            ':faculty' => $student['faculty'],
            ':enrollment_method' => $student['enrollment_method'],
            ':branch' => $student['branch'],
            ':program' => $student['program'],
            ':program_id' => $courseID,
            ':intake' => $student['intake']
        ]);

        
        $createEnrollmentsTable = "
            CREATE TABLE IF NOT EXISTS enrollments (
                enrollment_id INT(11) NOT NULL AUTO_INCREMENT,
                student_id VARCHAR(100) NOT NULL,
                course_ID VARCHAR(20) NOT NULL,
                enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (enrollment_id),
                FOREIGN KEY (course_ID) REFERENCES course(course_ID),
                FOREIGN KEY (student_id) REFERENCES student(stID),
                UNIQUE KEY unique_enrollment (student_id, course_ID)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ";
        $pdo->exec($createEnrollmentsTable);

        // Enroll student in their main course
        $enrollmentQuery = "INSERT INTO enrollments (student_id, course_ID) VALUES (:student_id, :course_id)";
        $enrollmentStmt = $pdo->prepare($enrollmentQuery);
        $enrollmentStmt->execute([
            ':student_id' => $generatedStID,
            ':course_id' => $courseID
        ]);

        // Auto-enroll in related courses based on program
        $relatedCourses = getRelatedCourses($courseID);
        foreach ($relatedCourses as $relatedCourseID) {
            try {
                $enrollmentStmt->execute([
                    ':student_id' => $generatedStID,
                    ':course_id' => $relatedCourseID
                ]);
            } catch (PDOException $e) {
                
                error_log("Could not enroll in related course $relatedCourseID: " . $e->getMessage());
            }
        }

        // Delete from prereg_students table
        $deleteQuery = "DELETE FROM prereg_students WHERE ID = :id";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->bindParam(':id', $student_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Student approved successfully. Generated Student ID: ' . $generatedStID,
            'student_id' => $generatedStID
        ]);

    } elseif ($action === 'reject') {

        $deleteQuery = "DELETE FROM prereg_students WHERE ID = :id";
        $deleteStmt = $pdo->prepare($deleteQuery);
        $deleteStmt->bindParam(':id', $student_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Student rejected and removed from system'
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}


function getFacultyAbbreviation($facultyName) {
    $abbreviations = [
        'Faculty of Computing' => 'COMP',
        'Faculty of Engineering' => 'ENG',
        'Faculty of Business / Management' => 'BUS',
        'Faculty of Creative Arts' => 'CREATIVE'
    ];
    return $abbreviations[$facultyName] ?? substr(strtoupper($facultyName), 0, 8);
}


function getProgramAbbreviation($programName) {
    $abbreviations = [
        'Software Engineering' => 'SE',
        'Network Security' => 'NS',
        'Robotics' => 'ROB',
        'Business Administration' => 'BBA',
        'Marketing And Finance' => 'MKTFIN',
        'Architecture' => 'ARCH',
        'CS' => 'CS'
    ];
    return $abbreviations[$programName] ?? substr(strtoupper(str_replace(' ', '', $programName)), 0, 6);
}


function getRelatedCourses($mainCourseID) {
    $relatedCourses = [];
    
    
    $courseRelationships = [
        'COMP002' => ['COMP004', 'ENG004'], 
        'COMP004' => ['COMP002'], 
        'ENG004' => ['COMP004'], 
        'BUS001' => ['BUS002'], 
        'BUS002' => ['BUS001'], 
        'CREATIVE002' => [], 
        'ENG005' => [] 
    ];
    
    return $courseRelationships[$mainCourseID] ?? [];
}
?>