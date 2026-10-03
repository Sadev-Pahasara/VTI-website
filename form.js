// Password toggle functionality
document.addEventListener("DOMContentLoaded", function () {
  const passwordInput = document.getElementById('password');
  const toggleEye = document.getElementById('togglePassword');

  if (passwordInput && toggleEye) {
    toggleEye.addEventListener('click', () => {
      const isHidden = passwordInput.type === 'password';
      passwordInput.type = isHidden ? 'text' : 'password';
      toggleEye.src = isHidden ? 'Eye.png' : 'eye-crossed.png';
    });
  }

  // Phone number validation - allow only numbers and limit to 10 digits
  const mobileInput = document.querySelector('input[name="mobile_number"]');
  const whatsappInput = document.querySelector('input[name="whatsapp_number"]');
  
  function validatePhoneInput(input) {
    // Remove any non-digit characters
    input.value = input.value.replace(/\D/g, '');
    
    // Limit to 10 digits
    if (input.value.length > 10) {
      input.value = input.value.slice(0, 10);
    }
  }
  
  if (mobileInput) {
    mobileInput.addEventListener('input', function() {
      validatePhoneInput(this);
    });
  }
  
  if (whatsappInput) {
    whatsappInput.addEventListener('input', function() {
      validatePhoneInput(this);
    });
  }
});

// Form validation for password and phone numbers
function validateForm() {
  const password = document.getElementById('password').value;
  const confirmPassword = document.getElementById('confirmpassword').value;
  const mobileNumber = document.querySelector('input[name="mobile_number"]').value;
  const whatsappNumber = document.querySelector('input[name="whatsapp_number"]').value;
  
  // Password validation
  if (password !== confirmPassword) {
    alert("Passwords do not match!");
    return false;
  }
  
  // Password length validation (at least 8 characters)
  if (password.length < 8) {
    alert("Password must be at least 8 characters long!");
    return false;
  }
  
  // Password pattern validation
  const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/;
  if (!passwordPattern.test(password)) {
    alert("Password must contain at least:\n- One uppercase letter\n- One lowercase letter\n- One number\n- One special character (@$!%*?&)");
    return false;
  }
  
  // Mobile number validation - exactly 10 digits
  const mobilePattern = /^\d{10}$/;
  if (!mobilePattern.test(mobileNumber)) {
    alert("Mobile number must contain exactly 10 digits!");
    return false;
  }
  
  // WhatsApp number validation - exactly 10 digits
  if (!mobilePattern.test(whatsappNumber)) {
    alert("WhatsApp number must contain exactly 10 digits!");
    return false;
  }
  
  return true;
}