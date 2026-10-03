<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student_portal</title>
    <link rel="stylesheet" href="student.css">
    <link rel="stylesheet" href="animation.css">

    <!-- icon link -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="enroledcourses.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>    

</head>
<body>

<?php
session_start();

// Debug: Check what's in session
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in and is a student
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'student') {
    header("Location: Login.html");
    exit();
}

// Database configuration
$host = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "skill_pro_institute";

// Create connection
$conn = mysqli_connect($host, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['student_name'];
$profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'human.png';

// Fetch student data from database
$sql = "SELECT * FROM student WHERE stID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Retrieve profile image from database
if (!empty($student['profile_image'])) {
    $profile_image = $student['profile_image'];
    $_SESSION['profile_image'] = $student['profile_image'];
}

// Check if address and postcode are already set (data is locked)
$data_locked = (!empty($student['address']) && !empty($student['postcode']));

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle profile image upload
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        // Sanitize student ID for filename
        $sanitized_student_id = preg_replace('/[^a-zA-Z0-9]/', '_', $student_id);
        $new_filename = "profile_" . $sanitized_student_id . "_" . time() . "." . $imageFileType;
        $target_file = $target_dir . $new_filename;
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
        if ($check !== false) {
            // Allow certain file formats
            if ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                    // Update profile image in database using username
                    $update_sql = "UPDATE student SET profile_image = ? WHERE username = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ss", $target_file, $_SESSION['username']);
                    
                    if ($update_stmt->execute()) {
                        $_SESSION['profile_image'] = $target_file;
                        $profile_image = $target_file;
                        $student['profile_image'] = $target_file;
                    }
                    
                    $update_stmt->close();
                }
            }
        }
    }
    
    // Handle data update (only if data is not locked yet)
    if (isset($_POST['save_data']) && !$data_locked) {
        $address = $_POST['address'] ?? '';
        $home_district = $_POST['home_district'] ?? '';
        $postcode = $_POST['postcode'] ?? '';
        
        // Update student data in database
        $update_sql = "UPDATE student SET address = ?, home_district = ?, postcode = ? WHERE stID = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $address, $home_district, $postcode, $student_id);
        
        if ($update_stmt->execute()) {
            // Update local student data and lock the form
            $student['address'] = $address;
            $student['home_district'] = $home_district;
            $student['postcode'] = $postcode;
            $data_locked = true;
            
            // Show success message
            echo "<script>alert('Profile data saved successfully!');</script>";
        }
        
        $update_stmt->close();
    }
}

$conn->close();
?>

<header data-aos="zoom-in-down" data-aos-delay="350" data-aos-duration="1000">
    <div class="left">
        <h2> Other Courses </h2>
    </div>
    <div class="right">
        <ul>
            <li> <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Human" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='human.png'"> </li>
            <li> <?php echo htmlspecialchars($student_name); ?> </li>
        </ul>
    </div>
</header>


<div class="Con" id="mainContent">

<section class="programs">

  <div class="container" style="padding: 10px;">

    <div class="row  gap-3 mt-5" data-aos="fade-up" data-aos-delay="350" data-aos-duration="1000">
     
    <div class="card" style="width: 19rem;">
                <img src="Business.gif" class="cardimgtop" alt="Business" style="width: 150px; height: auto; margin-left: 65px;"> <hr>
                <div class="card-body"> 
                    <h5 class="card-title"> Faculty of Business / Management </h5>
                    <p class="card-text">
                     <i class="ri-arrow-drop-right-fill"></i> Business Administration <br> 
                     <i class="ri-arrow-drop-right-fill"></i> Marketing And Finance <br> 
                     <i class="ri-arrow-drop-right-fill"></i> HR <br> 
                     <i class="ri-arrow-drop-right-fill"></i> Entrepreneurship</p><br>
                    <div class="d-grid"><a href="bnm.html" style="text-decoration: none; "><button type="button" class="infobtn"><i class="ri-search-2-line"></i> View more...</button></a></div>

                </div>
    </div>

    <div class="card" style="width: 19rem;">
                <img src="Computing.gif" class="cardimgtop" alt="Computing" style="width: 150px; height: auto; margin-left: 65px;"> <hr>
                <div class="card-body">
                    <h5 class="card-title">Faculty of Computing</h5>
                    <p class="card-text">
                     <i class="ri-arrow-drop-right-fill"></i> Computer Science <br> 
                     <i class="ri-arrow-drop-right-fill"></i> Software Engineering <br> 
                     <i class="ri-arrow-drop-right-fill"></i> Information Systems <br> 
                     <i class="ri-arrow-drop-right-fill"></i> Cybersecurity <br>
                     <i class="ri-arrow-drop-right-fill"></i> Networking
                    </p> <br>
                    <div class="d-grid"><a href="foc.html" style="text-decoration: none;"><button type="button" class="infobtn"><i class="ri-search-2-line"></i> View more...</button></a></div>

                </div>
    </div>

    <div class="card" style="width: 19rem;">
                <img src="Engineering.gif" class="cardimgtop" alt="Engineering" style="width: 150px; height: auto; margin-left: 65px;"> <hr>
                <div class="card-body">
                    <h5 class="card-title">Faculty of Engineering</h5>
                    <p class="card-text">
                     <i class="ri-arrow-drop-right-fill"></i> Civil Engineering <br>
                     <i class="ri-arrow-drop-right-fill"></i> Mechanical Engineering <br>
                     <i class="ri-arrow-drop-right-fill"></i> Electrical Engineering <br>
                     <i class="ri-arrow-drop-right-fill"></i> Electronic Engineering <br>
                     <i class="ri-arrow-drop-right-fill"></i> Robotics <br>
                     <i class="ri-arrow-drop-right-fill"></i> Mechatronics
                    </p>
                    <div class="d-grid"><a href="foe.html" style="text-decoration: none;"><button type="button" class="infobtn"><i class="ri-search-2-line"></i> View more...</button></a></div>

                </div>
    </div>

        <div class="card" style="width: 19rem;">
                <img src="Law.gif" class="cardimgtop" alt="Law" style="width: 150px; height: auto; margin-left: 65px;"> <hr>
                <div class="card-body">
                    <h5 class="card-title">Faculty of Law</h5>
                    <p class="card-text">
                     <i class="ri-arrow-drop-right-fill"></i> Law <br>
                     <i class="ri-arrow-drop-right-fill"></i> International Law <br>
                     <i class="ri-arrow-drop-right-fill"></i> Business Law
                    </p> <br> <br> <br>
                    <div class="d-grid"><a href="fol.html" style="text-decoration: none;"><button type="button" class="infobtn"><i class="ri-search-2-line"></i> View more...</button></a></div>
                </div>
    </div>

    </div>

    <div class="row  gap-3 mt-4 mb-4" style="margin-left: 100px;" data-aos="fade-up" data-aos-delay="350" data-aos-duration="1000">
     
    <div class="card" style="width: 19rem;">
                <img src="HealthSciences.gif" class="cardimgtop" alt="HealthSciences" style="width: 150px; height: auto; margin-left: 65px;"> <hr>
                <div class="card-body">
                    <h5 class="card-title">Faculty of Health Sciences</h5>
                    <p class="card-text"> 
                     <i class="ri-arrow-drop-right-fill"></i> Nursing <br>
                     <i class="ri-arrow-drop-right-fill"></i> Pharmacy <br>
                     <i class="ri-arrow-drop-right-fill"></i> Public Health <br>
                     <i class="ri-arrow-drop-right-fill"></i> Physiotherapy
                    </p>
                    <div class="d-grid"><a href="form.html" style="text-decoration: none;"><button type="button" class="infobtn"><i class="ri-search-2-line"></i> View more...</button></a></div>

                </div>
    </div>

    <div class="card" style="width: 19rem;">
                <img src="Arts.gif" class="cardimgtop" alt="Arts" style="width: 150px; height: auto; margin-left: 65px;"> <hr>
                <div class="card-body">
                    <h5 class="card-title">Faculty of Arts / Humanities</h5>
                    <p class="card-text">
                     <i class="ri-arrow-drop-right-fill"></i> Psychology <br>
                     <i class="ri-arrow-drop-right-fill"></i> English <br>
                     <i class="ri-arrow-drop-right-fill"></i> Communication <br>
                     <i class="ri-arrow-drop-right-fill"></i> Sociology
                    </p>
                    <div class="d-grid"><a href="form.html" style="text-decoration: none;"><button type="button" class="infobtn"><i class="ri-search-2-line"></i> View more...</button></a></div>

                </div>
    </div>

    <div class="card" style="width: 19rem;">
                <img src="Architecture.gif" class="cardimgtop" alt="Architecture"  style="width: 150px; height: auto; margin-left: 65px;"> <hr>
                <div class="card-body">
                    <h5 class="card-title">Faculty of Creative Arts</h5>
                    <p class="card-text">
                     <i class="ri-arrow-drop-right-fill"></i> Interior Design <br>
                     <i class="ri-arrow-drop-right-fill"></i> Architecture <br>
                     <i class="ri-arrow-drop-right-fill"></i> Graphic Design <br>
                     <i class="ri-arrow-drop-right-fill"></i> Fashion
                    </p> <br>
                    <div class="d-grid"><a href="form.html" style="text-decoration: none;"><button type="button" class="infobtn"><i class="ri-search-2-line"></i> View more...</button></a></div>

                </div>
    </div>

    </div>

  </div>

</section>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<script src="Studentportal.js"></script>
<script src="animation.js"></script>

</body>
</html>