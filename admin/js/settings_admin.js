// settings_admin.js

document.getElementById("adminSettingsForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const data = {
    fullname: document.getElementById("fullname").value.trim(),
    username: document.getElementById("username").value.trim(),
    email: document.getElementById("email").value.trim(),
    phone: document.getElementById("phone").value.trim(),
    birthday: document.getElementById("birthday").value,
    password: document.getElementById("password").value.trim()
  };

  if (!data.fullname || !data.username || !data.email) {
    showMessage("Please complete all required fields.", true);
    return;
  }

  // Simulate API save
  console.log("Saving admin settings:", data);
  showMessage("Settings saved successfully.");
});

function showMessage(msg, error = false) {
  const div = document.getElementById("statusMessage");
  div.textContent = msg;
  div.style.color = error ? "red" : "green";
}
