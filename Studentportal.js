const video = document.getElementById("hoverVideo");

video.addEventListener("mouseenter", () => {
  video.play();
});

video.addEventListener("mouseleave", () => {
  video.pause();
  video.currentTime = 0; // reset to beginning (remove if you want resume)
});

let startTime = null;
let sessions = JSON.parse(localStorage.getItem("sessions")) || [];

// Chart.js setup
// Load saved grades or start fresh
let grades = JSON.parse(localStorage.getItem("grades")) || [0,0,0,0,0,0,0,0];

// Chart.js setup
const ctx = document.getElementById("workChart");
const workChart = new Chart(ctx, {
  type: "bar",
  data: {
    labels: [
      "1st Sem","2nd Sem","3rd Sem","4th Sem",
      "5th Sem","6th Sem","7th Sem","8th Sem"
    ],
    datasets: [{
      label: "Grades",
      data: grades,
      backgroundColor: "rgba(54,162,235,0.6)"
    }]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true,
        max: 100 // grades are out of 100
      }
    }
  }
});

// Function to add/update a grade
function setGrade(semester, value) {
  grades[semester - 1] = Number(value);
  localStorage.setItem("grades", JSON.stringify(grades));
  updateStats();
}

// Update stats and chart
function updateStats() {
  let validGrades = grades.filter(g => g > 0);
  let avg = validGrades.length ? 
              (validGrades.reduce((a,b) => a+b, 0) / validGrades.length).toFixed(2) 
              : 0;

  let best = validGrades.length ? Math.max(...validGrades) : 0;
  let worst = validGrades.length ? Math.min(...validGrades) : 0;

  document.getElementById("avg").innerText = avg;
  document.getElementById("weekly").innerText = best;   // Best Grade
  document.getElementById("monthly").innerText = worst; // Worst Grade

  workChart.data.datasets[0].data = grades;
  workChart.update();
}

// Run once on page load
updateStats();


const li = document.querySelectorAll('.menu ul li');

li.forEach((item) => {
  item.addEventListener('click', () => {
    li.forEach((v) => v.classList.remove('active'));
    item.classList.add('active');
  });
});

