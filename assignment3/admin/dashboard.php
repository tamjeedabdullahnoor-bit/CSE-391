<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../config/auth.php';

require_admin();

$filterDate = trim($_GET['date'] ?? '');

$sql = "
    SELECT
        a.appointment_id,
        a.client_name,
        a.phone,
        a.car_license,
        a.engine_number,
        a.address,
        a.appointment_date,
        a.status,
        m.mechanic_name
    FROM appointments a
    JOIN mechanics m ON m.mechanic_id = a.mechanic_id
";
$params = [];

if ($filterDate !== '') {
    $sql .= " WHERE a.appointment_date = ?";
    $params[] = $filterDate;
}

$sql .= " ORDER BY a.appointment_date ASC, a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="site-header">
    <div>
        <h1>Admin Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></p>
    </div>
    <div class="header-actions">
        <a class="admin-link" href="../index.php">User Panel</a>
        <a class="admin-link" href="logout.php">Logout</a>
		<a class="admin-link" href="change_password.php">Change Password</a>
    </div>
</header>

<main class="container wide">
    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="card">
        <div class="dashboard-top">
            <div>
                <h2>Appointment List</h2>
                <p><?= count($appointments) ?> appointment(s) found.</p>
            </div>

            <form class="filter-form" method="GET">
                <label for="date">Filter Date:</label>
                <input type="date" id="date" name="date"
                       value="<?= htmlspecialchars($filterDate) ?>">
                <button class="secondary-button" type="submit">Filter</button>
                <a class="secondary-button" href="dashboard.php">Clear</a>
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client Name</th>
                        <th>Phone</th>
                        <th>Car Registration</th>
                        <th>Appointment Date</th>
                        <th>Mechanic</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$appointments): ?>
                    <tr>
                        <td colspan="8" class="empty">No appointments found.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td>#<?= (int)$appointment['appointment_id'] ?></td>
                        <td><?= htmlspecialchars($appointment['client_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['phone']) ?></td>
                        <td><?= htmlspecialchars($appointment['car_license']) ?></td>
                        <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                        <td><?= htmlspecialchars($appointment['mechanic_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['status']) ?></td>
                        <td>
                            <a class="small-button"
                               href="edit_appointment.php?id=<?= (int)$appointment['appointment_id'] ?>">
                               Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
