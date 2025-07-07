// manage_announcements.js

let announcements = [];

function addAnnouncement() {
  const title = document.getElementById("title").value.trim();
  const description = document.getElementById("description").value.trim();
  const startDate = document.getElementById("startDate").value;
  const endDate = document.getElementById("endDate").value;
  const pin = document.getElementById("pin").checked;

  if (!title || !description || !startDate || !endDate) {
    alert("Please fill in all fields.");
    return;
  }

  const newAnn = {
    title,
    description,
    startDate,
    endDate,
    pinned: pin
  };

  announcements.push(newAnn);
  renderAnnouncements();
  clearForm();
}

function renderAnnouncements() {
  const container = document.getElementById("announcementCards");
  container.innerHTML = "";

  announcements.sort((a, b) => b.pinned - a.pinned);

  announcements.forEach((a, i) => {
    const card = document.createElement("div");
    card.className = "announcement-card";
    card.innerHTML = `
      <h3>${a.title} ${a.pinned ? '📌' : ''}</h3>
      <div class="announcement-meta">From: ${a.startDate} | To: ${a.endDate}</div>
      <p>${a.description}</p>
    `;
    container.appendChild(card);
  });
}

function clearForm() {
  document.getElementById("title").value = "";
  document.getElementById("description").value = "";
  document.getElementById("startDate").value = "";
  document.getElementById("endDate").value = "";
  document.getElementById("pin").checked = false;
}

document.addEventListener("DOMContentLoaded", renderAnnouncements);
