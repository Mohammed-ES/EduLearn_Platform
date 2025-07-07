// announcements_student.js

const announcements = [
  {
    id: 1,
    title: "Welcome to EduLearn!",
    date: "2025-06-06",
    content: "We are excited to launch the new platform. Check your dashboard for more.",
    read: false
  },
  {
    id: 2,
    title: "Eid Al-Adha Holiday",
    date: "2025-06-07",
    content: "There will be no classes on Saturday, June 7 for Eid. Enjoy your time!",
    read: true
  }
];

function loadAnnouncements() {
  const container = document.getElementById("announcementsList");
  container.innerHTML = "";

  announcements.forEach(a => {
    const card = document.createElement("div");
    card.className = "announcement-card" + (a.read ? "" : " unread");

    card.innerHTML = `
      <div class="announcement-title">${a.title}</div>
      <div class="announcement-date">${a.date}</div>
      <div class="announcement-content">${a.content}</div>
      ${a.read ? '<div class="read-badge">Read</div>' : ""}
    `;

    card.onclick = () => {
      a.read = true;
      loadAnnouncements();
    };

    container.appendChild(card);
  });
}

function markAllAsRead() {
  announcements.forEach(a => a.read = true);
  loadAnnouncements();
}

document.addEventListener("DOMContentLoaded", loadAnnouncements);
