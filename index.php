<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "event_manager_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Add Event
if (isset($_POST['add'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $desc  = $conn->real_escape_string($_POST['description']);
    $date  = $_POST['event_date'];
    $time  = $_POST['event_time'];

    $conn->query("INSERT INTO events (title, description, event_date, event_time) VALUES ('$title', '$desc', '$date', '$time')");
    header("Location: index.php");
    exit();
}

// Update Event
if (isset($_POST['update'])) {
    $id    = (int)$_POST['id'];
    $title = $conn->real_escape_string($_POST['title']);
    $desc  = $conn->real_escape_string($_POST['description']);
    $date  = $_POST['event_date'];
    $time  = $_POST['event_time'];
    $status = $_POST['status'];

    $conn->query("UPDATE events SET title='$title', description='$desc', event_date='$date', event_time='$time', status='$status' WHERE id=$id");
    header("Location: index.php");
    exit();
}

// Delete Event
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM events WHERE id=$id");
    header("Location: index.php");
    exit();
}

// Fetch all events
$result = $conn->query("SELECT * FROM events ORDER BY event_date, event_time ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Manager - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: "Segoe UI", sans-serif; background:#f0f2f5; padding:20px; }
        h1 { color:#222; }
        .btn { padding:8px 14px; border:none; border-radius:4px; cursor:pointer; }
        .btn-primary { background:#007bff; color:#fff; }
        .btn-primary:hover { background:#0069d9; }
        .btn-edit { background:#17a2b8; color:#fff; }
        .btn-edit:hover { background:#138496; }
        .btn-delete { background:#dc3545; color:#fff; }
        .btn-delete:hover { background:#c82333; }
        table { width:100%; border-collapse: collapse; margin-top:20px; background:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
        th, td { border:1px solid #ddd; padding:10px; text-align:left; }
        th { background:#007bff; color:#fff; }
        .modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); }
        .modal-content { background:#fff; margin:8% auto; padding:20px; width:420px; border-radius:6px; }
        .close { float:right; cursor:pointer; font-size:20px; }
        input, textarea, select { width:100%; padding:8px; margin:6px 0; border:1px solid #ccc; border-radius:4px; }
    </style>
</head>
<body>

<h1><i class="fa-solid fa-calendar-check"></i> Event Manager - Admin</h1>
<button class="btn btn-primary" onclick="openModal('addModal')"><i class="fa-solid fa-plus"></i> Add Event</button>

<table>
    <tr>
        <th>ID</th>
        <th><i class="fa-solid fa-heading"></i> Title</th>
        <th><i class="fa-solid fa-align-left"></i> Description</th>
        <th><i class="fa-solid fa-calendar-day"></i> Date</th>
        <th><i class="fa-solid fa-clock"></i> Time</th>
        <th><i class="fa-solid fa-flag"></i> Status</th>
        <th><i class="fa-solid fa-cogs"></i> Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= $row['event_date'] ?></td>
            <td><?= $row['event_time'] ?></td>
            <td><?= ucfirst($row['status']) ?></td>
            <td>
                <button class="btn btn-edit" onclick="openEditModal(<?= $row['id'] ?>,'<?= htmlspecialchars($row['title']) ?>','<?= htmlspecialchars($row['description']) ?>','<?= $row['event_date'] ?>','<?= $row['event_time'] ?>','<?= $row['status'] ?>')"><i class="fa-solid fa-pen"></i></button>
                <a href="?delete=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this event?')"><i class="fa-solid fa-trash"></i></a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addModal')">&times;</span>
        <h2><i class="fa-solid fa-plus-circle"></i> Add Event</h2>
        <form method="POST">
            <input type="text" name="title" placeholder="Event Title" required>
            <textarea name="description" placeholder="Description"></textarea>
            <input type="date" name="event_date" required>
            <input type="time" name="event_time" required>
            <button type="submit" name="add" class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editModal')">&times;</span>
        <h2><i class="fa-solid fa-pen-to-square"></i> Edit Event</h2>
        <form method="POST">
            <input type="hidden" name="id" id="editId">
            <input type="text" name="title" id="editTitle" required>
            <textarea name="description" id="editDesc"></textarea>
            <input type="date" name="event_date" id="editDate" required>
            <input type="time" name="event_time" id="editTime" required>
            <select name="status" id="editStatus">
                <option value="upcoming">Upcoming</option>
                <option value="cancelled">Cancelled</option>
                <option value="completed">Completed</option>
            </select>
            <button type="submit" name="update" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>

<script>
function openModal(id) { document.getElementById(id).style.display = "block"; }
function closeModal(id) { document.getElementById(id).style.display = "none"; }
function openEditModal(id,title,desc,date,time,status) {
    document.getElementById('editId').value = id;
    document.getElementById('editTitle').value = title;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editDate').value = date;
    document.getElementById('editTime').value = time;
    document.getElementById('editStatus').value = status;
    openModal('editModal');
}
</script>

</body>
</html>
