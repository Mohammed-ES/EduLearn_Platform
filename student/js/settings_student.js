// settings_student.js

document.getElementById("settingsForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const fullname = document.getElementById("fullname").value.trim();
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();

  if (!fullname || !username) {
    showMessage("Please fill in all required fields.", true);
    return;
  }

  const formData = {
    fullname,
    username,
    ...(password && { password })
  };

  // Simulated AJAX request (replace with real fetch to PHP backend)
  console.log("Sending update:", formData);

  showMessage("Settings updated successfully.");
});

function showMessage(message, error = false) {
  const msgDiv = document.getElementById("statusMessage");
  msgDiv.textContent = message;
  msgDiv.style.color = error ? "red" : "green";
}
