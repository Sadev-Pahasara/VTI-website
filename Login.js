document.addEventListener("DOMContentLoaded", function () {
  const passwordInput = document.getElementById('password');
  const toggleEye = document.getElementById('togglePassword');

  if (passwordInput && toggleEye) {
    toggleEye.addEventListener('click', () => {
      const isHidden = passwordInput.type === 'password';
      passwordInput.type = isHidden ? 'text' : 'password';
      toggleEye.src = isHidden ? 'Eye.png' : 'eye-crossed.png';
    });
  } else {
    console.error("Password input or toggle eye not found.");
  }
});

window.onload = function () {
  const usernameInput = document.querySelector('input[name="username"]');
  const passwordInput = document.querySelector('input[name="password"]');
  const loginButton = document.querySelector('button[type="submit"]');

  // Disable button at the start
  loginButton.disabled = true;

  function validateInputs() {
    if (usernameInput.value.trim() !== "" && passwordInput.value.trim() !== "") {
      loginButton.disabled = false; // enable button
    } else {
      loginButton.disabled = true; // disable button
    }
  }

  // Run validation on every input change
  usernameInput.addEventListener("input", validateInputs);
  passwordInput.addEventListener("input", validateInputs);

  // Prevent form submission if fields are empty (extra safety)
  document.querySelector("form").addEventListener("submit", function (e) {
    if (usernameInput.value.trim() === "" || passwordInput.value.trim() === "") {
      e.preventDefault();
      alert("⚠️ Please fill in both Username and Password before logging in.");
    }
  });
};

