<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student_portal</title>
    <link rel="stylesheet" href="student.css">
    <link rel="stylesheet" href="animation.css">
    <link rel="stylesheet" href="studentdashboard.css">

    <!-- icon link -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'student') {
    header("Location: Login.html");
    exit();
}

$student_name = $_SESSION['student_name'];
$profile_image = isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'human.png';
?>

<header data-aos="zoom-in-down" data-aos-delay="350" data-aos-duration="1000">

    <div class="left">
        <h2> DASHBOARD </h2>
    </div>

    <div class="right">
        <ul>
            <li>
                  <div class="video-container">
                    <a href="notification.html">
                        <video id="hoverVideo" muted loop>
                            <source src="notification.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                        </video>
                    </a>
                  </div>
            </li>
            <li> <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Human" style="width: 40px; height: 40px; object-fit: cover;"> </li>
            <li style="font-size: 24px; font-weight: 700;"> <?php echo htmlspecialchars($student_name); ?> </li>
        </ul>
    </div>

</header>

<div class="container" data-aos="zoom-in-down" data-aos-delay="450" data-aos-duration="1000">

<div class="Con" id="mainContent">

        <Section class="analizer">
                <h2>Study Growth Analyzer</h2>
        
                <div class="stpnl">

                    <div class="anaborder">
            
                    <div class="an">
                        <canvas id="workChart" width="2250"></canvas>
                    </div>
        
                    <div class="stats">

                            <ul>
                                <li><i class="ri-time-fill"></i></li>
                                <li><p>Average:<br> <span id="avg">0</span> Grades</p></li>
                            </ul>

                            <ul>
                                <li><i class="ri-line-chart-fill"></i></li>
                                <li><p>Semesterly:<br> <span id="Semesterly">0</span> Grades</p></li>
                            </ul>

                            <ul>
                                <li><i class="ri-calendar-schedule-fill"></i></li>
                                <li><p>Yearly:<br> <span id="Yearly">0</span> Grades</p></li>
                            </ul>

                    </div>
                    
                    </div>

                    <div class="cal">

                        <h3> Event Calendar </h3>
              
                    </div>

                </div>
                

        </Section>
            
        <section class="incomingevents">
            <h3>Incoming Events</h3>

            <div class="cards">

              <div class="card">
                <img src="f1.jpg" alt="event1" style="width: 270px;">
                <div class="info">
                  <h3>Sep 20, 2025</h3>
                  <p>6:00 PM</p>
                  <p>Main Auditorium</p>
                </div>
              </div>

              <div class="card">
                <img src="intersteller.jpg" alt="event2" style="width: 270px;">
                <div class="info">
                  <h3>Sep 25, 2025</h3>
                  <p>6:00 PM</p>
                  <p>Main Auditorium</p>
                </div>
              </div>

              <div class="card">
                <img src="music.jpg" alt="event3" style="width: 270px;">
                <div class="info">
                  <h3>Oct 1, 2025</h3>
                  <p>5:00 PM</p>
                  <p>Main Auditorium</p>
                </div>
              </div>

              <div class="card">
                <img src="camp.webp" alt="event4" style="width: 270px;">
                <div class="info">
                  <h3>Oct 10, 2025</h3>
                  <p>8:00 AM</p>
                  <p>Hulangala Camping site</p>
                </div>
              </div>

            </div>

        </section>

</div>

</div>

<script>
  function loadContent(page) {
    document.getElementById("mainContent").innerHTML = `
      <h1>${page} Page</h1>
      <p>Here is the content for ${page}.</p>
    `;
  }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="Studentportal.js"></script>
<script src="animation.js"></script>

</body>
</html>