<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assign Lecturer to Course & Module</title>
  <link rel="stylesheet" href="assign-lecturer.css">
  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">

  <!-- jQuery + Select2 -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>

<div class="assign-container">

  <h2><i class="ri-user-settings-line"></i> Assign Lecturer to Courses & Modules</h2>
  <p class="subtitle">Search and assign lecturers to specific courses and modules.</p>

  <!-- Lecturer Selection -->
  <div class="form-group">
    <label><i class="ri-user-3-line"></i> Lecturer:</label>
    <select id="lecturerSelect" style="width:100%">
      <option value="">-- Select Lecturer --</option>
      <option value="lec1">Dr. Nuwan Perera</option>
      <option value="lec2">Ms. Anusha Silva</option>
      <option value="lec3">Prof. Saman Jayawardena</option>
      <option value="lec4">Dr. Chamara Fernando</option>
      <option value="lec5">Dr. Isuru Ranasinghe</option>
    </select>
  </div>

  <!-- Course Selection -->
  <div class="form-group">
    <label><i class="ri-book-2-line"></i> Course:</label>
    <select id="courseSelect" style="width:100%">
      <option value="">-- Select Course --</option>
      <option value="ict101">ICT101 - Introduction to Computing</option>
      <option value="math202">MATH202 - Discrete Mathematics</option>
      <option value="eng303">ENG303 - Technical English</option>
      <option value="cs405">CS405 - Database Systems</option>
      <option value="se502">SE502 - Software Engineering</option>
      <option value="ai601">AI601 - Artificial Intelligence</option>
      <option value="net701">NET701 - Computer Networks</option>
    </select>
  </div>

  <!-- Module Selection -->
  <div class="form-group">
    <label><i class="ri-stack-line"></i> Module:</label>
    <select id="moduleSelect" style="width:100%">
      <option value="">-- Select Module --</option>
      <option value="mod1">Module 1: Introduction & Basics</option>
      <option value="mod2">Module 2: Advanced Concepts</option>
      <option value="mod3">Module 3: Practical Sessions</option>
      <option value="mod4">Module 4: Research & Projects</option>
      <option value="mod5">Module 5: Final Assessment</option>
    </select>
  </div>

  <!-- Semester -->
  <div class="form-group">
    <label><i class="ri-calendar-event-line"></i> Semester:</label>
    <select>
      <option value="1">Semester 1</option>
      <option value="2">Semester 2</option>
      <option value="3">Semester 3</option>
      <option value="4">Semester 4</option>
    </select>
  </div>

  <!-- Assign Button -->
  <button class="assign-btn"><i class="ri-check-double-line"></i> Assign</button>

</div>

<script>
  // Enable Select2 with search + scroll
  $(document).ready(function() {
    $('#lecturerSelect').select2({
      placeholder: "Search Lecturer",
      allowClear: true
    });
    $('#courseSelect').select2({
      placeholder: "Search Course",
      allowClear: true
    });
    $('#moduleSelect').select2({
      placeholder: "Search Module",
      allowClear: true
    });
  });
</script>

</body>
</html>
