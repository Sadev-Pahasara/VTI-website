<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lecturer Gradebook</title>
  <link rel="stylesheet" href="gradebook.css">
  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

  <div class="gradebook-container">
    <h2><i class="ri-bar-chart-2-line"></i> Gradebook</h2>
    <p class="subtitle">Enter and manage student grades</p>

    <!-- Course Selection -->
    <form method="POST" action="saveGrades.php">
      <label for="course">Select Course</label>
      <select id="course" name="course" required>
        <option value="">-- Choose Course --</option>
        <option value="ICT101">ICT101 - Intro to Computing</option>
        <option value="MATH202">MATH202 - Discrete Math</option>
        <option value="ENG303">ENG303 - Academic English</option>
      </select>

      <!-- Grade Table -->
      <table class="grade-table">
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Assignment</th>
            <th>Midterm</th>
            <th>Final</th>
          </tr>
        </thead>
        <tbody>
          <!-- Example rows (later load from DB) -->
          <tr>
            <td>STU001</td>
            <td>Ayesha Fernando</td>
            <td><input type="number" name="grades[STU001][assignment]" min="0" max="100" required></td>
            <td><input type="number" name="grades[STU001][midterm]" min="0" max="100" required></td>
            <td><input type="number" name="grades[STU001][final]" min="0" max="100" required></td>
          </tr>
          <tr>
            <td>STU002</td>
            <td>Nuwan Perera</td>
            <td><input type="number" name="grades[STU002][assignment]" min="0" max="100" required></td>
            <td><input type="number" name="grades[STU002][midterm]" min="0" max="100" required></td>
            <td><input type="number" name="grades[STU002][final]" min="0" max="100" required></td>
          </tr>
        </tbody>
      </table>

      <button type="submit"><i class="ri-save-3-line"></i> Save Grades</button>
    </form>
  </div>

</body>
</html>
