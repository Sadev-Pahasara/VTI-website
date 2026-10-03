<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lecturer Dashboard</title>
  <link rel="stylesheet" href="lecturerDashboard.css">
  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

  <div class="dashboard-container">

    <h2>Welcome, Lecturer</h2>
    <p class="subtitle">Quick overview of your activities</p>

    <div class="dashboard-grid">

      <div class="card">
        <i class="ri-calendar-schedule-line icon"></i>
        <h3>Upcoming Classes</h3>
        <p>3 classes scheduled today</p>
        <a href="myschedule.html" target="contentFrame">View Schedule →</a>
      </div>

      <div class="card">
        <i class="ri-survey-line icon"></i>
        <h3>Assignments</h3>
        <p>12 pending submissions</p>
        <a href="gradebook.php" target="contentFrame">Grade Now →</a>
      </div>

      <div class="card">
        <i class="ri-bar-chart-2-line icon"></i>
        <h3>Performance Reports</h3>
        <p>Latest student progress</p>
        <a href="reports.php" target="contentFrame">View Reports →</a>
      </div>

      <div class="card">
        <i class="ri-megaphone-line icon"></i>
        <h3>Announcements</h3>
        <p>Post updates to students</p>
        <a href="announcements.php" target="contentFrame">Create Announcement →</a>
      </div>

    </div>
  </div>

</body>
</html>
