<?php
declare(strict_types=1);
require __DIR__ . '/config/database.php';

$mechanics = $pdo->query("
    SELECT mechanic_id, mechanic_name
    FROM mechanics
    WHERE active = 1
    ORDER BY mechanic_name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoCare Workshop | Appointment</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header">
    <div>
        <h1>AutoCare Workshop</h1>
        <p>Book an appointment with your preferred senior mechanic.</p>
    </div>
    <a class="admin-link" href="admin/login.php">Admin Panel</a>
</header>

<main class="container">
    <section class="card">
        <h2>Book Your Appointment</h2>
        <p class="help-text">
            Each mechanic can handle a maximum of 4 active appointments per day.
            A client can have only one appointment per day.
        </p>

        <div id="message" class="message hidden"></div>

        <form id="appointmentForm" action="appointment.php" method="POST" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label for="client_name">Full Name</label>
                    <input type="text" id="client_name" name="client_name" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" maxlength="20"
                           placeholder="e.g. 01712345678" required>
                </div>

                <div class="form-group full-width">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" maxlength="255" required></textarea>
                </div>

                <div class="form-group">
                    <label for="car_license">Car License / Registration Number</label>
                    <input type="text" id="car_license" name="car_license" maxlength="50" required>
                </div>

                <div class="form-group">
                    <label for="engine_number">Car Engine Number</label>
                    <input type="text" id="engine_number" name="engine_number"
                           maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="appointment_date">Appointment Date</label>
                    <input type="date" id="appointment_date" name="appointment_date"
                           min="<?= htmlspecialchars(date('Y-m-d')) ?>" required>
                </div>

                <div class="form-group">
                    <label for="mechanic_id">Preferred Mechanic</label>
                    <select id="mechanic_id" name="mechanic_id" required disabled>
                        <option value="">Select an appointment date first</option>
                        <?php foreach ($mechanics as $mechanic): ?>
                            <option value="<?= (int)$mechanic['mechanic_id'] ?>">
                                <?= htmlspecialchars($mechanic['mechanic_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div id="availability" class="availability-box">
                Select an appointment date to see mechanic availability.
            </div>

            <button type="submit" class="primary-button">Book Appointment</button>
        </form>
    </section>

    <section class="card help-card">
        <h2>How It Works</h2>
        <ol>
            <li>Enter your personal and car information.</li>
            <li>Select an appointment date.</li>
            <li>Choose a mechanic with available slots.</li>
            <li>Submit your appointment request.</li>
            <li>The system checks duplicate bookings and mechanic capacity.</li>
        </ol>
    </section>
</main>

<script src="js/script.js"></script>
</body>
</html>
