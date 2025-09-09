<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "event_manager_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle file upload
$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    if (in_array($_FILES['image']['type'], $allowedTypes) && $_FILES['image']['size'] <= $maxFileSize) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $imagePath = $uploadDir . time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
    }
}

// Add Event
if (isset($_POST['add'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $desc  = $conn->real_escape_string($_POST['description']);
    $date  = $_POST['event_date'];
    $time  = $_POST['event_time'];
    $status = $_POST['status'];
    
    // Use the uploaded image path or null if no image was uploaded
    $imageValue = $imagePath ? "'$imagePath'" : "NULL";

    $conn->query("INSERT INTO events (title, description, event_date, event_time, image, status) VALUES ('$title', '$desc', '$date', '$time', $imageValue, '$status')");
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
    
    // If a new image was uploaded, use it; otherwise keep the existing one
    $imageUpdate = "";
    if ($imagePath) {
        $imageUpdate = ", image='$imagePath'";
    }

    $conn->query("UPDATE events SET title='$title', description='$desc', event_date='$date', event_time='$time', status='$status' $imageUpdate WHERE id=$id");
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
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body { 
            font-family: "Segoe UI", sans-serif; 
            background: #f0f2f5; 
            padding: 20px; 
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        h1 { 
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn { 
            padding: 10px 16px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-primary { 
            background: var(--primary); 
            color: #fff; 
        }
        
        .btn-primary:hover { 
            background: var(--secondary); 
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-edit { 
            background: var(--info); 
            color: #fff; 
            padding: 6px 10px;
            font-size: 0.85rem;
        }
        
        .btn-edit:hover { 
            background: #3a76d5; 
        }
        
        .btn-delete { 
            background: var(--danger); 
            color: #fff; 
            padding: 6px 10px;
            font-size: 0.85rem;
        }
        
        .btn-delete:hover { 
            background: #e3126f; 
        }
        
        .btn-view {
            background: var(--success);
            color: #fff;
            padding: 6px 10px;
            font-size: 0.85rem;
        }
        
        .btn-view:hover {
            background: #3bb4e0;
        }
        
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            background: #fff; 
        }
        
        th, td { 
            border-bottom: 1px solid #e0e0e0; 
            padding: 15px; 
            text-align: left; 
        }
        
        th { 
            background: var(--primary); 
            color: #fff; 
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        tr:hover {
            background-color: #f1f5f9;
        }
        
        .event-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .no-image {
            width: 80px;
            height: 60px;
            background: #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            color: #888;
            font-size: 12px;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-upcoming {
            background: #e9f7ef;
            color: #28a745;
        }
        
        .status-completed {
            background: #e8f0fe;
            color: #4361ee;
        }
        
        .status-cancelled {
            background: #fde8e8;
            color: #e43f5a;
        }
        
        .modal { 
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .modal-content { 
            background: #fff; 
            margin: 5% auto; 
            padding: 25px; 
            width: 90%;
            max-width: 600px;
            border-radius: 10px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .close { 
            float: right; 
            cursor: pointer; 
            font-size: 24px; 
            font-weight: bold;
            color: #888;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        input, textarea, select { 
            width: 100%; 
            padding: 12px; 
            margin: 6px 0; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            font-family: inherit;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .current-image {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .current-image img {
            max-width: 150px;
            border-radius: 4px;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 10% auto;
                padding: 20px;
            }
            
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fa-solid fa-calendar-check"></i> Event Manager - Admin</h1>
            <button class="btn btn-primary" onclick="openModal('addModal')"><i class="fa-solid fa-plus"></i> Add Event</button>
        </header>

        <div class="card">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th><i class="fa-solid fa-heading"></i> Title</th>
                            <th><i class="fa-solid fa-align-left"></i> Description</th>
                            <th><i class="fa-solid fa-calendar-day"></i> Date</th>
                            <th><i class="fa-solid fa-clock"></i> Time</th>
                            <th><i class="fa-solid fa-flag"></i> Status</th>
                            <th><i class="fa-solid fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="<?= $row['image'] ?>" alt="Event Image" class="event-image">
                                    <?php else: ?>
                                        <div class="no-image">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars(substr($row['description'], 0, 50)) . (strlen($row['description']) > 50 ? '...' : '') ?></td>
                                <td><?= date('M j, Y', strtotime($row['event_date'])) ?></td>
                                <td><?= date('g:i A', strtotime($row['event_time'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $row['status'] ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-view" onclick="viewEvent(<?= $row['id'] ?>)"><i class="fa-solid fa-eye"></i></button>
                                        <button class="btn btn-edit" onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['title'])) ?>', '<?= htmlspecialchars(addslashes($row['description'])) ?>', '<?= $row['event_date'] ?>', '<?= $row['event_time'] ?>', '<?= $row['status'] ?>', '<?= $row['image'] ?>')"><i class="fa-solid fa-pen"></i></button>
                                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this event?')"><i class="fa-solid fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Modal -->
        <div id="addModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addModal')">&times;</span>
                <h2><i class="fa-solid fa-plus-circle"></i> Add Event</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" placeholder="Event Title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" placeholder="Event Description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_date">Date</label>
                        <input type="date" name="event_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_time">Time</label>
                        <input type="time" name="event_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" required>
                            <option value="upcoming">Upcoming</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="add" class="btn btn-primary">Save Event</button>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('editModal')">&times;</span>
                <h2><i class="fa-solid fa-pen-to-square"></i> Edit Event</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="editId">
                    
                    <div class="form-group">
                        <label for="editTitle">Title</label>
                        <input type="text" name="title" id="editTitle" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editDesc">Description</label>
                        <textarea name="description" id="editDesc"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editDate">Date</label>
                        <input type="date" name="event_date" id="editDate" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editTime">Time</label>
                        <input type="time" name="event_time" id="editTime" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="editImage">Current Image</label>
                        <div class="current-image" id="currentImageContainer">
                            <img id="currentImage" src="" alt="Current image" style="max-width: 150px;">
                            <span id="noImageText">No image uploaded</span>
                        </div>
                        <label for="editImageNew">Upload New Image (optional)</label>
                        <input type="file" name="image" id="editImageNew" accept="image/*">
                    </div>
                    
                    <div class="form-group">
                        <label for="editStatus">Status</label>
                        <select name="status" id="editStatus">
                            <option value="upcoming">Upcoming</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="update" class="btn btn-primary">Update Event</button>
                </form>
            </div>
        </div>

        <!-- View Modal -->
        <div id="viewModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('viewModal')">&times;</span>
                <h2><i class="fa-solid fa-calendar-days"></i> Event Details</h2>
                <div id="viewContent"></div>
            </div>
        </div>
    </div>

    <script>
    function openModal(id) { 
        document.getElementById(id).style.display = "block"; 
    }
    
    function closeModal(id) { 
        document.getElementById(id).style.display = "none"; 
    }
    
    function openEditModal(id, title, desc, date, time, status, image) {
        document.getElementById('editId').value = id;
        document.getElementById('editTitle').value = title;
        document.getElementById('editDesc').value = desc;
        document.getElementById('editDate').value = date;
        document.getElementById('editTime').value = time;
        document.getElementById('editStatus').value = status;
        
        // Handle image display
        const imgElement = document.getElementById('currentImage');
        const noImageText = document.getElementById('noImageText');
        
        if (image) {
            imgElement.src = image;
            imgElement.style.display = 'block';
            noImageText.style.display = 'none';
        } else {
            imgElement.style.display = 'none';
            noImageText.style.display = 'block';
        }
        
        openModal('editModal');
    }
    
    function viewEvent(id) {
        // In a real application, you would fetch the event details via AJAX
        // For this example, we'll just show a message
        document.getElementById('viewContent').innerHTML = `
            <p>Loading event details for ID: ${id}...</p>
            <p>In a complete implementation, this would show the full event details.</p>
        `;
        openModal('viewModal');
    }
    
    // Close modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = "none";
        }
    }
    </script>
</body>
</html>