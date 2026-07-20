<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';

require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: dashboard.php?error=Invalid appointment.');
    exit;
}

$mechanics = $pdo->query("
    SELECT mechanic_id, mechanic_name
    FROM mechanics
    WHERE active = 1
    ORDER BY mechanic_name
")->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['appointment_date'] ?? '');
    $mechanicId = filter_input(INPUT_POST, 'mechanic_id', FILTER_VALIDATE_INT);

    $dateObject = DateTime::createFromFormat('Y-m-d', $date);

    if (!$dateObject || $dateObject->format('Y-m-d') !== $date || !$mechanicId) {
        $error = 'Please provide a valid date and mechanic.';
    } elseif ($date < date('Y-m-d')) {
        $error = 'Appointment date cannot be in the past.';
    } else {
        try {
            $pdo->beginTransaction();

            $currentStmt = $pdo->prepare("
                SELECT phone, appointment_date, mechanic_id, status
                FROM appointments
                WHERE appointment_id = ?
                FOR UPDATE
            ");
            $currentStmt->execute([$id]);
            $current = $currentStmt->fetch();

            if (!$current) {
                throw new RuntimeException('Appointment not found.');
            }

            if ($current['status'] === 'Cancelled') {
                throw new RuntimeException('Cancelled appointments cannot be edited.');
            }

            $duplicate = $pdo->prepare("
                SELECT appointment_id
                FROM appointments
                WHERE phone = ?
                  AND appointment_date = ?
                  AND status = 'Booked'
                  AND appointment_id <> ?
                LIMIT 1
            ");
            $duplicate->execute([$current['phone'], $date, $id]);

            if ($duplicate->fetch()) {
                throw new RuntimeException(
                    'This client already has another appointment on the selected date.'
                );
            }

            $count = $pdo->prepare("
                SELECT COUNT(*)
                FROM appointments
                WHERE mechanic_id = ?
                  AND appointment_date = ?
                  AND status = 'Booked'
                  AND appointment_id <> ?
            ");
            $count->execute([$mechanicId, $date, $id]);

            if ((int)$count->fetchColumn() >= 4) {
                throw new RuntimeException(
                    'The selected mechanic is already fully booked on this date.'
                );
            }

            $update = $pdo->prepare("
                UPDATE appointments
                SET appointment_date = ?, mechanic_id = ?
                WHERE appointment_id = ?
            ");
            $update->execute([$date, $mechanicId, $id]);

            $pdo->commit();

            header('Location: dashboard.php?success=' .
                urlencode('Appointment updated successfully.'));
            exit;

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("
    SELECT a.*, m.mechanic_name
    FROM appointments a
    JOIN mechanics m ON m.mechanic_id = a.mechanic_id
    WHERE a.appointment_id = ?
");
$stmt->execute([$id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    header('Location: dashboard.php?error=Appointment not found.');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<main class="container narrow">
    <section class="card">
        <h1>Edit Appointment #<?= (int)$appointment['appointment_id'] ?></h1>

        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="details-box">
            <p><strong>Client:</strong> <?= htmlspecialchars($appointment['client_name']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($appointment['phone']) ?></p>
            <p><strong>Car:</strong> <?= htmlspecialchars($appointment['car_license']) ?></p>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="appointment_date">Appointment Date</label>
                <input type="date" id="appointment_date" name="appointment_date"
                       min="<?= htmlspecialchars(date('Y-m-d')) ?>"
                       value="<?= htmlspecialchars($appointment['appointment_date']) ?>"
                       required>
            </div>

            <div class="form-group">
                <label for="mechanic_id">Mechanic</label>
                <select id="mechanic_id" name="mechanic_id" required>
                    <?php foreach ($mechanics as $mechanic): ?>
                        <option value="<?= (int)$mechanic['mechanic_id'] ?>"
                            <?= (int)$mechanic['mechanic_id'] === (int)$appointment['mechanic_id']
                                ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mechanic['mechanic_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="primary-button" type="submit">Save Changes</button>
            <a class="secondary-button" href="dashboard.php">Cancel</a>
        </form>
    </section>
</main>
</body>
</html>
