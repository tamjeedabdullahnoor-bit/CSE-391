<?php declare(strict_types=1); require __DIR__ . '/../config/database.php'; require __DIR__ . '/../config/auth.php'; require_admin(); $message = ''; $messageType = ''; if ($_SERVER['REQUEST_METHOD'] === 'POST') { $currentPassword = $_POST['current_password'] ?? ''; $newPassword = $_POST['new_password'] ?? ''; $confirmPassword = $_POST['confirm_password'] ?? ''; if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') { $message = 'Please fill in all fields.'; $messageType = 'error'; } elseif (strlen($newPassword) < 8) { $message = 'New password must be at least 8 characters long.'; $messageType = 'error'; } elseif ($newPassword !== $confirmPassword) { $message = 'New passwords do not match.'; $messageType = 'error'; } else { $stmt = $pdo->prepare(" SELECT password_hash FROM admins WHERE admin_id = ? "); $stmt->execute([$_SESSION['admin_id']]); $admin = $stmt->fetch(); if (!$admin || !password_verify($currentPassword, $admin['password_hash'])) { $message = 'Current password is incorrect.'; $messageType = 'error'; } else { $newHash = password_hash($newPassword, PASSWORD_DEFAULT); $update = $pdo->prepare(" UPDATE admins SET password_hash = ? WHERE admin_id = ? "); $update->execute([ $newHash, $_SESSION['admin_id'] ]); $message = 'Password changed successfully.'; $messageType = 'success'; } } } ?>

<!DOCTYPE html>

<html lang="en"> <head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Change Password</title> <link rel="stylesheet" href="../css/style.css"> </head>

<body>

<main class="container narrow">

<section class="card">

    <h1>Change Admin Password</h1>

    <?php if ($message): ?>

        <div class="message <?= htmlspecialchars($messageType) ?>">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <div class="form-group">
            <label for="current_password">
                Current Password
            </label>

            <input
                type="password"
                id="current_password"
                name="current_password"
                required
            >
        </div>

        <div class="form-group">
            <label for="new_password">
                New Password
            </label>

            <input
                type="password"
                id="new_password"
                name="new_password"
                minlength="8"
                required
            >
        </div>

        <div class="form-group">
            <label for="confirm_password">
                Confirm New Password
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                required
            >
        </div>

        <button
            class="primary-button"
            type="submit"
        >
            Change Password
        </button>

        <a
            class="secondary-button"
            href="dashboard.php"
        >
            Cancel
        </a>

    </form>

</section>

</main>

</body> </html>