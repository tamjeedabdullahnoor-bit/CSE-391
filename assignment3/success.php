<?php
declare(strict_types=1);

require __DIR__ . '/config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        a.*,
        m.mechanic_name
    FROM appointments a
    JOIN mechanics m ON m.mechanic_id = a.mechanic_id
    WHERE a.appointment_id = ?
");
$stmt->execute([$id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmed</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<main class="container">
    <section class="card success-card">
        <div class="success-icon">✓</div>
        <h1>Appointment Confirmed</h1>
        <p>Your appointment has been successfully booked.</p>

        <div class="confirmation">
            <p><strong>Appointment ID:</strong> #<?= (int)$appointment['appointment_id'] ?></p>
            <p><strong>Name:</strong> <?= htmlspecialchars($appointment['client_name']) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($appointment['appointment_date']) ?></p>
            <p><strong>Mechanic:</strong> <?= htmlspecialchars($appointment['mechanic_name']) ?></p>
        </div>

        <a class="primary-button inline-button" href="index.php">Book Another Appointment</a>
    </section>
</main>
</body>
</html>
