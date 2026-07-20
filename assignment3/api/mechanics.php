<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$date = $_GET['date'] ?? '';

$dateObject = DateTime::createFromFormat('Y-m-d', $date);
if (!$dateObject || $dateObject->format('Y-m-d') !== $date) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid date.']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        m.mechanic_id,
        m.mechanic_name,
        COUNT(a.appointment_id) AS booked_slots,
        GREATEST(0, 4 - COUNT(a.appointment_id)) AS free_slots
    FROM mechanics m
    LEFT JOIN appointments a
        ON a.mechanic_id = m.mechanic_id
        AND a.appointment_date = ?
        AND a.status = 'Booked'
    WHERE m.active = 1
    GROUP BY m.mechanic_id, m.mechanic_name
    ORDER BY m.mechanic_name
");
$stmt->execute([$date]);

echo json_encode([
    'success' => true,
    'mechanics' => $stmt->fetchAll()
]);
