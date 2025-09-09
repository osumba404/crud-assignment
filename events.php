<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "event_manager_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Current month/year
$month = date('m');
$year  = date('Y');

// Fetch events for this month
$sql = "SELECT * FROM events WHERE MONTH(event_date)='$month' AND YEAR(event_date)='$year' AND status='upcoming'";
$result = $conn->query($sql);

// Store events by date
$events = [];
while ($row = $result->fetch_assoc()) {
    $day = date('j', strtotime($row['event_date']));
    $events[$day][] = $row;
}

// Calendar variables
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$startDayOfWeek = date('N', $firstDayOfMonth);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Calendar</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background:#f9f9f9; margin:0; padding:0; }
        header { background:#007bff; color:#fff; padding:20px; text-align:center; }
        .calendar { display:grid; grid-template-columns: repeat(7, 1fr); gap:1px; background:#ccc; margin:20px; }
        .day { background:#fff; min-height:120px; padding:5px; position:relative; }
        .day-header { background:#007bff; color:#fff; padding:10px; text-align:center; font-weight:bold; }
        .date { font-size:14px; font-weight:bold; margin-bottom:4px; }
        .event { background:#e9f5ff; border-left:4px solid #007bff; padding:5px; margin:4px 0; border-radius:4px; font-size:13px; }
        .event img { max-width:100%; height:60px; object-fit:cover; border-radius:4px; margin-top:5px; }
        .empty { background:#f1f1f1; }
    </style>
</head>
<body>

<header>
    <h1><i class="fa-solid fa-calendar-days"></i> Events in <?= date('F Y') ?></h1>
</header>

<div class="calendar">
    <!-- Weekday headers -->
    <div class="day-header">Mon</div>
    <div class="day-header">Tue</div>
    <div class="day-header">Wed</div>
    <div class="day-header">Thu</div>
    <div class="day-header">Fri</div>
    <div class="day-header">Sat</div>
    <div class="day-header">Sun</div>

    <!-- Blank days before start -->
    <?php for ($i=1; $i<$startDayOfWeek; $i++): ?>
        <div class="day empty"></div>
    <?php endfor; ?>

    <!-- Days of month -->
    <?php for ($day=1; $day<=$daysInMonth; $day++): ?>
        <div class="day">
            <div class="date"><?= $day ?></div>
            <?php if (isset($events[$day])): ?>
                <?php foreach ($events[$day] as $event): ?>
                    <div class="event">
                        <strong><?= htmlspecialchars($event['title']) ?></strong><br>
                        <i class="fa-solid fa-clock"></i> <?= $event['event_time'] ?><br>
                        <?php if (!empty($event['image'])): ?>
                            <img src="<?= $event['image'] ?>" alt="Event Image">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
</div>

</body>
</html>
