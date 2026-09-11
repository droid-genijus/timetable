<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tvarkaraštis</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <p class="topbar-info">Šiandien yra <span id="current-day"></span></p>
        <div class="topbar-user">
            <span><?= htmlspecialchars(Auth::username() ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            <a href="logout.php" class="button">Atsijungti</a>
        </div>
    </div>

    <p id="schedule-status" class="schedule-status" hidden></p>

    <div class="timetable-wrapper">
    <table>
        <tr>
            <td></td>
            <td>Angliškai</td>
            <td>Monday</td>
            <td>Tuesday</td>
            <td>Wednesday</td>
            <td>Thursday</td>
            <td>Friday</td>
        </tr>
        <tr>
            <td>Nr.</td>
            <td>Laikas</td>
            <td>Pirmadienis</td>
            <td>Antradienis</td>
            <td>Trečiadienis</td>
            <td>Ketvirtadienis</td>
            <td>Penktadienis</td>
        </tr>
        <tbody id="schedule-body"></tbody>
    </table>
    </div>

    <div id="mobile-schedule" class="mobile-schedule"></div>

    <script src="script.js"></script>
</body>
</html>
