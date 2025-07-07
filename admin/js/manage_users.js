// manage_users.js

let users = [
  { id: 1, fullname: "John Doe", email: "john@example.com", username: "john123", deleted: false },
  { id: 2, fullname: "Jane Smith", email: "jane@example.com", username: "jane456", deleted: true }
];

function renderUsers() {
  const tbody = document.getElementById("userList");
  const search = document.getElementById("searchInput").value.toLowerCase();
  tbody.innerHTML = "";

  users.filter(user =>
    user.fullname.toLowerCase().includes(search) ||
    user.email.toLowerCase().includes(search)
  ).forEach(user => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${user.fullname}</td>
      <td>${user.email}</td>
      <td>${user.username}</td>
      <td>${user.deleted ? "Deleted" : "Active"}</td>
      <td>
        <button class="action-btn" onclick="editUser(${user.id})">Edit</button>
        ${user.deleted
          ? `<button class="action-btn restore" onclick="restoreUser(${user.id})">Restore</button>`
          : `<button class="action-btn" onclick="deleteUser(${user.id})">Delete</button>`}
      </td>
    `;
    tbody.appendChild(row);
  });
}

function openAddModal() {
  document.getElementById("modalTitle").textContent = "Add Student";
  document.getElementById("modalFullName").value = "";
  document.getElementById("modalEmail").value = "";
  document.getElementById("modalUsername").value = "";
  document.getElementById("modalPassword").value = "";
  document.getElementById("userModal").style.display = "block";
}

function closeUserModal() {
  document.getElementById("userModal").style.display = "none";
}

function saveUser() {
  // Simulate saving (replace with backend)
  closeUserModal();
  alert("User saved (simulation).");
  renderUsers();
}

function editUser(id) {
  const user = users.find(u => u.id === id);
  if (!user) return;
  document.getElementById("modalTitle").textContent = "Edit Student";
  document.getElementById("modalFullName").value = user.fullname;
  document.getElementById("modalEmail").value = user.email;
  document.getElementById("modalUsername").value = user.username;
  document.getElementById("modalPassword").value = "";
  document.getElementById("userModal").style.display = "block";
}

function deleteUser(id) {
  const user = users.find(u => u.id === id);
  if (user) user.deleted = true;
  renderUsers();
}

function restoreUser(id) {
  const user = users.find(u => u.id === id);
  if (user) user.deleted = false;
  renderUsers();
}

document.getElementById("searchInput").addEventListener("input", renderUsers);
document.addEventListener("DOMContentLoaded", renderUsers);
