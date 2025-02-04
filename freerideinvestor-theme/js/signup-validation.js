document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector(".signup-form");
  const username = document.getElementById("user_login");
  const email = document.getElementById("user_email");
  const password = document.getElementById("password");

  form.addEventListener("submit", (e) => {
    let isValid = true;

    // Username Validation
    if (username.value.length < 3) {
      isValid = false;
      showError(username, "Username must be at least 3 characters.");
    } else {
      clearError(username);
    }

    // Email Validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value)) {
      isValid = false;
      showError(email, "Please enter a valid email address.");
    } else {
      clearError(email);
    }

    // Password Validation
    if (password.value.length < 6) {
      isValid = false;
      showError(password, "Password must be at least 6 characters.");
    } else {
      clearError(password);
    }

    if (!isValid) {
      e.preventDefault();
    }
  });

  function showError(input, message) {
    const errorBanner = input.nextElementSibling;
    errorBanner.textContent = message;
  }

  function clearError(input) {
    const errorBanner = input.nextElementSibling;
    errorBanner.textContent = "";
  }
});
