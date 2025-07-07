// admin_dashboard.js

document.addEventListener("DOMContentLoaded", () => {
  // Simulated data fetching
  document.getElementById("studentCount").textContent = "126";
  document.getElementById("quizCount").textContent = "42";
  document.getElementById("announcementCount").textContent = "9";
  document.getElementById("aiUsage").textContent = "321 queries";

  const log = [
    "Student A submitted Quiz 3",
    "Admin published new announcement",
    "Student B created a new note",
    "AI Assistant used by Student C"
  ];

  const logList = document.getElementById("activityLog");
  logList.innerHTML = "";
  log.forEach(entry => {
    const li = document.createElement("li");
    li.textContent = entry;
    logList.appendChild(li);
  });
});
