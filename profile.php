<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student_portal</title>
    <link rel="stylesheet" href="student.css">
    <link rel="stylesheet" href="animation.css">
    <link rel="stylesheet" href="profile.css">

    <!-- icon link -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
</head>
<body>

<?php
session_start();

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

$student_username = $_SESSION['username'];
$student_name = $_SESSION['student_name'];
$profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'human.png';

// Fetch student data from database using the logged-in student's username
$sql = "SELECT * FROM student WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Student record not found!");
}

$student = $result->fetch_assoc();
$stmt->close();

// Check if address and postcode are already set
$address_set = !empty($student['address']);
$postcode_set = !empty($student['postcode']);

// Handle form submission for address and postcode
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_address'])) {
    $address = $_POST['address'];
    $postcode = $_POST['postcode'];
    
    // Update address and postcode in database
    $update_sql = "UPDATE student SET address = ?, postcode = ? WHERE username = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sss", $address, $postcode, $student_username);
    
    if ($update_stmt->execute()) {
        echo "<script>alert('Address information updated successfully!');</script>";
        echo "<script>window.location.href = 'profile.php';</script>";
    } else {
        echo "<script>alert('Error updating address information!');</script>";
    }
    
    $update_stmt->close();
}

// Handle profile image upload only
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_FILES['profile_image']['name'])) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
    // Sanitize student ID for filename
    $sanitized_student_id = preg_replace('/[^a-zA-Z0-9]/', '_', $student['stID']);
    $new_filename = "profile_" . $sanitized_student_id . "_" . time() . "." . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is actual image
    $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
    if ($check !== false) {
        // Allow certain file formats
        if ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                // Update profile image in database using student username
                $update_sql = "UPDATE student SET profile_image = ? WHERE username = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ss", $target_file, $student_username);
                
                if ($update_stmt->execute()) {
                    $_SESSION['profile_image'] = $target_file;
                    $profile_image = $target_file;
                    $student['profile_image'] = $target_file;
                    echo "<script>alert('Profile image updated successfully!');</script>";
                    echo "<script>window.location.href = 'profile.php';</script>";
                } else {
                    echo "<script>alert('Error updating profile image!');</script>";
                }
                
                $update_stmt->close();
            } else {
                echo "<script>alert('Error uploading image file!');</script>";
            }
        } else {
            echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed!');</script>";
        }
    } else {
        echo "<script>alert('File is not a valid image!');</script>";
    }
}

$conn->close();
?>

<header data-aos="zoom-in-down" data-aos-delay="350" data-aos-duration="1000">
    <div class="left">
        <h2> PROFILE </h2>
    </div>
    <div class="right">
        <ul>
            <li> <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Human" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='human.png'"> </li>
            <li> <?php echo htmlspecialchars($student_name); ?> </li>
        </ul>
    </div>
</header>

<div class="container" data-aos="zoom-in-down" data-aos-delay="450" data-aos-duration="1000">
    
<div class="PCon" id="mainContent" Data-aos="zoom-in-up" data-aos-delay="350" data-aos-duration="1000">

    <form method="post" action="" enctype="multipart/form-data">

        <div class="form-row">
            <div class="profile-header">
                <div class="profile-pic" id="profilePreview" style="cursor: pointer;" onclick="document.getElementById('imageUpload').click()">
                    <?php
                    $profile_img_src = 'human.png';
                    if (!empty($student['profile_image'])) {
                        // Check if it's a full path or just filename
                        if (file_exists($student['profile_image'])) {
                            $profile_img_src = $student['profile_image'];
                        } elseif (file_exists('uploads/' . $student['profile_image'])) {
                            $profile_img_src = 'uploads/' . $student['profile_image'];
                        } else {
                            $profile_img_src = $student['profile_image'];
                        }
                    }
                    ?>
                    <img id="uploadedImg" src="<?php echo htmlspecialchars($profile_img_src); ?>" alt="Profile" style="width: 100px; height: 100px; object-fit: cover;" onerror="this.src='human.png'" />
                    <div style="margin-top: 10px; font-size: 0.8rem; color: #666;">Click to change profile picture</div>
                </div>

                <div class="profile-actions">
                    <div class="rightside">
                        <h4><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                        <h5>Student ID: <?php echo htmlspecialchars($student['stID']); ?></h5>
                    </div>
                </div>
            </div>

            <input type="file" id="imageUpload" name="profile_image" accept="image/*" style="display:none" />

            <script>
                const imageInput = document.getElementById("imageUpload");
                const uploadedImg = document.getElementById("uploadedImg");

                imageInput.addEventListener("change", function () {
                    const file = this.files[0];
                    if (file && file.type.startsWith("image/")) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            uploadedImg.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                        
                        // Auto-submit the form when image is selected
                        setTimeout(function() {
                            document.forms[0].submit();
                        }, 500);
                    }
                });
            </script>
        </div>

        <div class="abt">
            <h4>ABOUT</h4>    

            <div class="form-row">
                <label for="Phone"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-phone"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" /></svg> Phone :</label>
                <input type="text" value="<?php echo htmlspecialchars($student['mobile_number'] ?? 'Not set'); ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;" />
            </div>

            <div class="form-row">
                <label for="Email"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-mail"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6"/></svg> Email :</label>
                <input type="text" value="<?php echo htmlspecialchars($student['email'] ?? 'Not set'); ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;" />
            </div>

            <div class="form-row">
                <label for="User_Name"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-user"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg> User Name :</label>
                <input type="text" value="<?php echo htmlspecialchars($student['username'] ?? 'Not set'); ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;" />
            </div>

            <hr>
        </div>

        <div class="addrss">
            <h4>ADDRESS</h4> 

            <div class="form-row">
                <label for="Address"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-map-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5" /><path d="M9 4v13" /><path d="M15 7v5.5" /><path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z" /><path d="M19 18v.01" /></svg> Address :</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($student['address'] ?? ''); ?>" 
                    <?php echo $address_set ? 'readonly style="background-color: #f8f9fa; cursor: not-allowed;"' : ''; ?> 
                    placeholder="<?php echo $address_set ? '' : 'Enter your address'; ?>" />
            </div>

            <div class="form-row">
                <label for="City_State"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-buildings"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 21v-15c0 -1 1 -2 2 -2h5c1 0 2 1 2 2v15" /><path d="M16 8h2c1 0 2 1 2 2v11" /><path d="M3 21h18" /><path d="M10 12v0" /><path d="M10 16v0" /><path d="M10 8v0" /><path d="M7 12v0" /><path d="M7 16v0" /><path d="M7 8v0" /><path d="M17 12v0" /><path d="M17 16v0" /></svg> Home District :</label>
                <input type="text" value="<?php echo htmlspecialchars($student['home_district'] ?? 'Not set'); ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;" />
            </div>

            <div class="form-row">
                <label for="Post_Code"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-map-pin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg> Post Code :</label>
                <input type="text" name="postcode" value="<?php echo htmlspecialchars($student['postcode'] ?? ''); ?>" 
                    <?php echo $postcode_set ? 'readonly style="background-color: #f8f9fa; cursor: not-allowed;"' : ''; ?> 
                    placeholder="<?php echo $postcode_set ? '' : 'Enter your post code'; ?>" />
            </div>

            <?php if (!$address_set || !$postcode_set): ?>
            <div class="form-row" style="text-align: center; margin-top: 20px;">
                <button type="submit" name="update_address" class="save-btn" style="  width: 100px; font-size: 15px; background: #05b34a; color: #eee; border-radius: 10px; padding: 5px; cursor: pointer;">Save</button>
            </div>
            <?php endif; ?>

            <hr>
        </div>

        <div class="stdetail">
            <h4>STUDENT DETAILS</h4> 

            <div class="form-row">
                <label for="DOB"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-week"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M7 14h.013" /><path d="M10.01 14h.005" /><path d="M13.01 14h.005" /><path d="M16.015 14h.005" /><path d="M13.015 17h.005" /><path d="M7.01 17h.005" /><path d="M10.01 17h.005" /></svg> Date Of Birth :</label>
                <input type="text" value="<?php echo htmlspecialchars($student['date_of_birth'] ?? 'Not set'); ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;" />
            </div>

            <div class="form-row">
                <label for="NIC"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-users"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg> National ID :</label>
                <input type="text" value="<?php echo htmlspecialchars($student['nic'] ?? 'Not set'); ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;" />
            </div>

            <div class="form-row">
                <label for="Program"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-briefcase"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" /><path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" /><path d="M12 12l0 .01" /><path d="M3 13a20 20 0 0 0 18 0" /></svg> Program :</label>
                <input type="text" value="<?php echo htmlspecialchars($student['program'] ?? 'Not set'); ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;" />
            </div>

            <div class="form-row">
                <label for="stdate"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-calendar-week"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16 2c.183 0 .355 .05 .502 .135l.033 .02c.28 .177 .465 .49 .465 .845v1h1a3 3 0 0 1 2.995 2.824l.005 .176v12a3 3 0 0 1 -2.824 2.995l-.176 .005h-12a3 3 0 0 1 -2.995 -2.824l-.005 -.176v-12a3 3 0 0 1 2.824 -2.995l.176 -.005h1v-1a1 1 0 0 1 .514 -.874l.093 -.046l.066 -.025l.1 -.029l.107 -.019l.12 -.007q .083 0 .161 .013l.122 .029l.04 .012l.06 .023c.328 .135 .568 .44 .61 .806l.007 .117v1h6v-1a1 1 0 0 1 1 -1m3 7h-14v9.625c0 .705 .386 1.286 .883 1.366l.117 .009h12c.513 0 .936 -.53 .993 -1.215l.007 -.16z" /><path d="M9.015 13a1 1 0 0 1 -1 1a1.001 1.001 0 1 1 -.005 -2c.557 0 1.005 .448 1.005 1" /><path d="M13.015 13a1 1 0 0 1 -1 1a1.001 1.001 0 1 1 -.005 -2c.557 0 1.005 .448 1.005 1" /><path d="M17.02 13a1 1 0 0 1 -1 1a1.001 1.001 0 1 1 -.005 -2c.557 0 1.005 .448 1.005 1" /><path d="M12.02 15a1 1 0 0 1 0 2a1.001 1.001 0 1 1 -.005 -2z" /><path d="M9.015 16a1 1 0 0 1 -1 1a1.001 1.001 0 1 1 -.005 -2c.557 0 1.005 .448 1.005 1" /></svg> Intake :</label>
                <input type="text" value="<?php echo htmlspecialchars($student['intake'] ?? 'Not set'); ?>" readonly style="background-color: #f8f9fa; cursor: not-allowed;" />
            </div>

        </div>

    </form>

    <div class="secondContent" Data-aos="zoom-in-up" data-aos-delay="350" data-aos-duration="1000">
    
        <div class="one">

        <h2> <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-pencil"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4" /><path d="M13.5 6.5l4 4" /></svg> ACADEMIC INFORMATION</h2>
          
        <div class="onep2">

            <form action="" style="border: none;">

                <div class="form-row">
                    
                    <div class="addrss">

                        <div class="form-row">
                        <label for="Address"> Program :</label>
                        <input type="text" value="<?php echo htmlspecialchars($student['program'] ?? 'Not set'); ?>" readonly style="width: 300px; background-color: #f8f9fa; cursor: not-allowed;"/>
                        </div>

                        <div class="form-row">
                        <label for="City_State"> GPA :</label>
                        <input type="text" placeholder="GPA will be displayed here" style="width: 300px; background-color: #f8f9fa; cursor: not-allowed;" readonly />
                        </div>

                        <div class="form-row">
                        <label for="Post_Code"> Semester :</label>
                        <input type="text" placeholder="Current semester will be displayed here" style="width: 300px; background-color: #f8f9fa; cursor: not-allowed;" readonly />
                        </div>

                    </div>

                </div>
    
            </form>

            <img src="logo1.1.png" alt="Human" style="width: 250px; margin-left: 250px; margin-top: 10px; filter: drop-shadow(0 6px 6px rgba(0, 0, 0, 0.3));">

        </div>

        </div>

        <div class="two">

            <h2> <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-medal"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 4v3m-4 -3v6m8 -6v6" /><path d="M12 18.5l-3 1.5l.5 -3.5l-2 -2l3 -.5l1.5 -3l1.5 3l3 .5l-2 2l.5 3.5z" /></svg> ACADEMIC BADGES</h2>
            
            <div class="secmain">

                <div class="fsem">
                    <h4>First Year</h4>
                    <ul>
                        <li><img src="b1.png" alt=""></li>
                        <li><img src="b2.png" alt="" style="filter: grayscale(100%);"></li>
                        <li><img src="b3.png" alt="" style="filter: grayscale(100%);"></li>
                        <li><img src="b4.png" alt="" style="filter: grayscale(100%);"></li>
                    </ul>
                </div>

                <div class="ssem">
                    <h4>Second Year</h4>
                    <ul>
                        <li><img src="b1.png" alt="" style="filter: grayscale(100%);"></li>
                        <li><img src="b2.png" alt="" style="filter: grayscale(100%);"></li>
                        <li><img src="b3.png" alt="" style="filter: grayscale(100%);"></li>
                        <li><img src="b4.png" alt="" style="filter: grayscale(100%);"></li>
                    </ul>
                </div>

            </div>

        </div>

    </div>

</div>

</div>

<script src="Studentportal.js"></script>
<script src="animation.js"></script>

</body>
</html>