// Function to validate phone number
function validatePhone() {
  const contactInput = document.getElementById("contact").value.trim();
  const whatsappInput = document.getElementById("whatsapp").value.trim();

  const phonePattern = /^\+94\d{9}$/; // +94 followed by 9 digits = 12 characters

  if (!phonePattern.test(contactInput)) {
    alert("Contact number must start with +94 and be followed by 9 digits");
    return false;
  }

  if (!phonePattern.test(whatsappInput)) {
    alert("Whatsapp number must start with +94 and be followed by 9 digits");
    return false;
  }

  return true;
}

// Function to validate name (firstname + lastname)
function validateName() {
  const firstName = document.getElementById("firstname").value.trim();
  const lastName = document.getElementById("lastname").value.trim();

  if (firstName === "" || lastName === "") {
    alert("First name and Last name cannot be empty");
    return false;
  }

  return true;
}


// Function to validate email
function validateEmail() {
  const emailInput = document.getElementById("email").value.trim();
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (!emailPattern.test(emailInput)) {
    alert("Please enter a valid email address");
    return false;
  }

  return true;
}


window.onload = function() {
  const daySelect = document.getElementById("day");
  const yearSelect = document.getElementById("year");

  // Populate days
  for (let d = 1; d <= 31; d++) {
    let option = document.createElement("option");
    option.value = d;
    option.textContent = d;
    daySelect.appendChild(option);
  }

  // Populate years (2000 to current)
  const currentYear = new Date().getFullYear();
  for (let y = currentYear; y >= 2000; y--) {
    let option = document.createElement("option");
    option.value = y;
    option.textContent = y;
    yearSelect.appendChild(option);
  }

  // Form submission
  const form = document.getElementById("registrationForm");
  form.addEventListener("submit", function(e) {
    // Run all validations
    if (!validateName() || !validateEmail() || !validatePhone()) {
      e.preventDefault(); // Stop submission if any validation fails
      return;
    }

    // All validations passed
    e.preventDefault(); // Remove this line if you want the form to actually submit
    alert("✅ Registration successful!");
    form.reset();
  });
};
