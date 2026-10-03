    document.addEventListener("DOMContentLoaded", () => {
      const students = document.getElementById("students");
      let count = 0;
      const interval = setInterval(() => {
        if (count < 2400) {
          count += 20;
          students.textContent = count;
        } else {
          clearInterval(interval);
        }
      }, 30);
    });

  const ctx = document.getElementById('myChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
      datasets: [{
        label: 'Student Enrollments',
        data: [120, 200, 150, 220, 180, 250],
        backgroundColor: '#22c55e',
        borderRadius: 8
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true },
        tooltip: { enabled: true }
      },
      scales: {
        y: { beginAtZero: true }
      }
    }
  });

