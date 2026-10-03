console.log("NEW VERSION LOADED - Connected to Database");

let students = [];
const tableBody = document.querySelector("#studentTable tbody");
const searchInput = document.getElementById("searchInput");

// Edit modal elements
const editModal = document.getElementById("editModal");
const closeEditModal = document.getElementById("closeEditModal");
const editForm = document.getElementById("editForm");
const editId = document.getElementById("editId");
const editFirstName = document.getElementById("editFirstName");
const editLastName = document.getElementById("editLastName");
const editEmail = document.getElementById("editEmail");
const editUsername = document.getElementById("editUsername");
const editPassword = document.getElementById("editPassword");
const editProgram = document.getElementById("editProgram");

// Fetch all students from database
async function fetchStudents() {
    console.log("Fetching from database...");
    try {
        const response = await fetch('allstudent.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log("Raw response:", data);
        
        // Check if we got an error message from PHP
        if (data.error) {
            console.error("PHP Error:", data.error);
            showErrorMessage("Database error: " + data.error);
            students = [];
        } else {
            students = data;
        }
        
        renderTable(students);
    } catch (error) {
        console.error('Error fetching students:', error);
        showErrorMessage("Cannot connect to database. Check PHP configuration.");
        students = [];
        renderTable(students);
    }
}

// Show error message to user
function showErrorMessage(message) {
    let errorDiv = document.getElementById('errorMessage');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'errorMessage';
        errorDiv.style.cssText = 'background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #ef5350;';
        document.querySelector('.container').prepend(errorDiv);
    }
    errorDiv.innerHTML = `<strong>Error:</strong> ${message}`;
}

// Render table
function renderTable(data) {
    console.log("Rendering table with:", data);
    tableBody.innerHTML = "";
    
    if (!data || data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: #666;">No students found in database</td></tr>';
        return;
    }
    
    data.forEach(student => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${student.id}</td>
            <td>${student.name}</td>
            <td>${student.email}</td>
            <td>${student.username}</td>
            <td>${student.password}</td>
            <td>${student.course}</td>
            <td>
                <button class="editBtn">Edit</button>
                <button class="deleteBtn">Delete</button>
            </td>
        `;

        row.querySelector(".deleteBtn").addEventListener("click", () => {
            deleteStudent(student.id);
        });

        row.querySelector(".editBtn").addEventListener("click", () => {
            openEditModal(student);
        });

        tableBody.appendChild(row);
    });
}

// Delete student
async function deleteStudent(id) {
    if (!confirm('Are you sure you want to delete this student?')) return;

    try {
        const response = await fetch('delete_student.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        });
        const result = await response.text();
        if (result === 'success') {
            await fetchStudents();
        } else {
            alert('Error deleting student');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error deleting student');
    }
}

// Search functionality
searchInput.addEventListener("input", () => {
    const query = searchInput.value.toLowerCase();
    const filtered = students.filter(s => 
        s.name.toLowerCase().includes(query) ||
        s.email.toLowerCase().includes(query) ||
        s.username.toLowerCase().includes(query) ||
        s.course.toLowerCase().includes(query) ||
        s.id.toString().includes(query)
    );
    renderTable(filtered);
});

// Open edit modal
function openEditModal(student) {
    editId.value = student.id;
    
    // Split full name into first and last name
    const names = student.name.split(' ');
    editFirstName.value = names[0] || '';
    editLastName.value = names.slice(1).join(' ') || '';
    
    editEmail.value = student.email;
    editUsername.value = student.username;
    editPassword.value = student.password;
    editProgram.value = student.course;
    editModal.style.display = "block";
}

// Close modal
closeEditModal.addEventListener("click", () => {
    editModal.style.display = "none";
});

// Update student
editForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    const id = editId.value;
    const firstName = editFirstName.value;
    const lastName = editLastName.value;
    const email = editEmail.value;
    const username = editUsername.value;
    const password = editPassword.value;
    const program = editProgram.value;

    try {
        const response = await fetch('update_student.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&first_name=${encodeURIComponent(firstName)}&last_name=${encodeURIComponent(lastName)}&email=${encodeURIComponent(email)}&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}&program=${encodeURIComponent(program)}`
        });
        const result = await response.text();
        if (result === 'success') {
            await fetchStudents();
            editModal.style.display = "none";
        } else {
            alert('Error updating student');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error updating student');
    }
});

// Initial render
fetchStudents();