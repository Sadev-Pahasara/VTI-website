<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Lecture Material</title>
  <link rel="stylesheet" href="lectureUpload.css">
  <!-- Remix Icons -->
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

  <div class="upload-container">
    
    <div class="top1">

      <div class="top2">
        <h2><i class="ri-upload-cloud-line"></i> Upload Lecture Materials</h2>
        <p class="subtitle">Share files and resources with your students</p>
      </div>

      <img src="logo1.1.png" alt="logo">

    </div>

    <form action="uploadMaterial.php" method="POST" enctype="multipart/form-data">

      <!-- Course -->
      <label for="course">Select Course</label>
      <select id="course" name="course" required>
        <option value="">-- Choose Course --</option>
        <option value="ICT101">ICT101 - Intro to Computing</option>
        <option value="MATH202">MATH202 - Discrete Math</option>
        <option value="ENG303">ENG303 - English for Academic Purposes</option>
      </select>

      <!-- Title -->
      <label for="title">Material Title</label>
      <input type="text" id="title" name="title" placeholder="Enter material title" required>

      <!-- Description -->
      <label for="description">Description</label>
      <textarea id="description" name="description" placeholder="Brief description..." rows="4"></textarea>

      <!-- File Upload -->
      <label for="file">Choose File</label>
      <input type="file" id="file" name="file" accept=".pdf,.ppt,.pptx,.doc,.docx,.zip" required>

      <!-- Submit -->
      <button type="submit"><i class="ri-upload-2-line"></i> Upload</button>

    </form>
  </div>

</body>
</html>
