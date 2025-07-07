// planning.js

let events = [];

function addEvent() {
  const title = document.getElementById("eventTitle").value.trim();
  const category = document.getElementById("eventCategory").value;
  const date = document.getElementById("eventDate").value;

  if (!title || !category || !date) {
    alert("Please complete all fields.");
    return;
  }

  const newEvent = {
    title,
    category,
    date
  };

  events.push(newEvent);
  renderEvents();
  clearForm();
}

function renderEvents() {
  const container = document.getElementById("eventsDisplay");
  container.innerHTML = "";

  events.sort((a, b) => new Date(a.date) - new Date(b.date));

  events.forEach((ev, index) => {
    const item = document.createElement("li");
    item.className = "event-item";
    item.innerHTML = `
      <strong>${ev.title}</strong>
      <span>${ev.category.toUpperCase()} — ${new Date(ev.date).toLocaleString()}</span>
      <button class="delete-btn" onclick="deleteEvent(${index})">×</button>
    `;
    container.appendChild(item);
  });
}

function deleteEvent(index) {
  events.splice(index, 1);
  renderEvents();
}

function clearForm() {
  document.getElementById("eventTitle").value = "";
  document.getElementById("eventCategory").value = "";
  document.getElementById("eventDate").value = "";
}

document.addEventListener("DOMContentLoaded", renderEvents);
