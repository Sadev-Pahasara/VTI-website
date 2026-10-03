const pendingTable = document.querySelector("#pendingTable tbody");
const approvedTable = document.querySelector("#approvedTable tbody");

// Load students from backend
async function loadStudents() {
    console.log("Starting to load students...");
    
    try {
        // Show loading message
        pendingTable.innerHTML = '<tr><td colspan="6" style="text-align: center;">Loading...</td></tr>';
        approvedTable.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading...</td></tr>';
        
        const response = await fetch('get_students.php');
        
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log("Full data response:", data);
        
        displayStudents(data);
        
    } catch (error) {
        console.error('Error loading students:', error);
        pendingTable.innerHTML = '<tr><td colspan="6" style="text-align: center; color: red;">Error loading data: ' + error.message + '</td></tr>';
        approvedTable.innerHTML = '<tr><td colspan="5" style="text-align: center; color: red;">Error loading data</td></tr>';
    }
}

function displayStudents(data) {
    console.log("Displaying students:", data);
    
    // Display pending students
    if (data.pending && data.pending.length > 0) {
        pendingTable.innerHTML = "";
        data.pending.forEach(student => {
            console.log("Processing pending student:", student);
            
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${student.ID || 'N/A'}</td>
                <td>${(student.first_name || '') + ' ' + (student.last_name || '')}</td>
                <td>${student.email || 'No email'}</td>
                <td>${student.program || 'No program'}</td>
                <td>${student.faculty || 'No faculty'}</td>
                <td>
                    <button class="approve-btn" data-id="${student.ID}">Approve</button>
                    <button class="reject-btn" data-id="${student.ID}">Reject</button>
                </td>
            `;
            pendingTable.appendChild(row);
        });
    } else {
        console.log("No pending students found");
        pendingTable.innerHTML = '<tr><td colspan="6" style="text-align: center;">No pending students</td></tr>';
    }

    // Display approved students
    if (data.approved && data.approved.length > 0) {
        approvedTable.innerHTML = "";
        data.approved.forEach(student => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${student.stID || 'N/A'}</td>
                <td>${student.first_name} ${student.last_name}</td>
                <td>${student.email}</td>
                <td>${student.program_id || 'No ID'}</td> <!-- Show course_ID from database -->
                <td>${student.faculty}</td>
            `;
            approvedTable.appendChild(row);
        });
    } else {
        approvedTable.innerHTML = '<tr><td colspan="5" style="text-align: center;">No approved students</td></tr>';
    }
}

// Handle Approve & Reject
document.addEventListener("click", async (e) => {
    if (e.target.classList.contains("approve-btn") || e.target.classList.contains("reject-btn")) {
        const studentId = e.target.getAttribute('data-id');
        const action = e.target.classList.contains("approve-btn") ? 'approve' : 'reject';
        
        console.log("Button clicked - Student ID:", studentId, "Action:", action);
        
        if (confirm(`Are you sure you want to ${action} this student?`)) {
            try {
                const formData = new FormData();
                formData.append('student_id', studentId);
                formData.append('action', action);
                
                const response = await fetch('approve_student.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    loadStudents(); // Reload the data
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error processing request: ' + error.message);
            }
        }
    }
});

// Load students when page loads
document.addEventListener('DOMContentLoaded', loadStudents);

// Optional: Auto-refresh every 30 seconds to check for new pending students
setInterval(loadStudents, 30000);