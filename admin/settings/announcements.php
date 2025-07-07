<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Announcements | EduLearn</title>
  <link rel="stylesheet" href="../admin/css/manage_announcements.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

  <main class="announcement-admin-container">
    <h1>Manage Announcements</h1>

    <section class="create-announcement">
      <h2>Create New Announcement</h2>
      <input type="text" id="title" placeholder="Title">
      <textarea id="description" placeholder="Description..."></textarea>
      <label>
        Start Date:
        <input type="datetime-local" id="startDate">
      </label>
      <label>
        End Date:
        <input type="datetime-local" id="endDate">
      </label>
      <label>
        <input type="checkbox" id="pin"> Pin this announcement
      </label>
      <button onclick="addAnnouncement()">Publish</button>
    </section>

    <section class="announcement-list">
      <h2>All Announcements</h2>
      <div id="announcementCards"></div>
    </section>
  </main>


  <script src="../admin/js/manage_announcements.js"></script>
</body>
</html>
