<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "event_manager_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$result = $conn->query("SELECT * FROM events WHERE status='upcoming' ORDER BY event_date, event_time ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upcoming Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background:#fff; margin:0; padding:0; }
        header { background:#007bff; color:#fff; padding:20px; text-align:center; }
        .container { display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:20px; padding:20px; }
        .event-card { background:#f9f9f9; padding:20px; border-radius:8px; box-shadow:0 3px 8px rgba(0,0,0,0.1); transition:transform 0.2s; }
        .event-card:hover { transform: translateY(-4px); }
        .event-card h2 { margin-top:0; font-size:20px; color:#007bff; }
        .event-info { margin:10px 0; color:#444; }
        .event-info i { margin-right:6px; color:#007bff; }
    </style>
</head>
<body>

<header>
    <h1><i class="fa-solid fa-calendar-days"></i> Upcoming Events</h1>
</header>

<div class="container">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="event-card">
                <h2><i class="fa-solid fa-star"></i> <?= htmlspecialchars($row['title']) ?></h2>
                <p class="event-info"><i class="fa-solid fa-calendar-day"></i> <?= $row['event_date'] ?></p>
                <p class="event-info"><i class="fa-solid fa-clock"></i> <?= $row['event_time'] ?></p>
                <p><?= htmlspecialchars($row['description']) ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="padding:20px;">No upcoming events found.</p>
    <?php endif; ?>
</div>

</body>
</html>
