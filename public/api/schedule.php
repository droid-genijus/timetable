<?php

declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

Auth::requireLogin();

$stmt = Database::connection()->prepare(
    'SELECT day_of_week, start_time, end_time, subject, room, teacher
     FROM schedule_entries
     WHERE user_id = :user_id
     ORDER BY day_of_week, start_time'
);
$stmt->execute(['user_id' => Auth::userId()]);

echo json_encode(['entries' => $stmt->fetchAll()]);
