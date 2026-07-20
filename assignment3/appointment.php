<?php
declare(strict_types=1);

require __DIR__ . '/config/database.php';

function redirect_with_message(string $type, string $message): never {
    header('Location: index.php?' . http_build_query([
        'type' => $type,
        'message' => $message
    ]));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$name = trim($_POST['client_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$carLicense = trim($_POST['car_license'] ?? '');
$engineNumber = trim($_POST['engine_number'] ?? '');
$date = trim($_POST['appointment_date'] ?? '');
$mechanicId = filter_input(INPUT_POST, 'mechanic_id', FILTER_VALIDATE_INT);

if ($name === '' || $address === '' || $phone === '' ||
    $carLicense === '' || $engineNumber === '' || $date === '' || !$mechanicId) {
    redirect_with_message('error', 'Please complete every required field.');
}

if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
    redirect_with_message('error', 'Phone number must contain 10 to 15 digits.');
}

if (!preg_match('/^[A-Za-z0-9\-\/]+$/', $engineNumber)) {
    redirect_with_message('error', 'Engine number contains invalid characters.');
}

$dateObject = DateTime::createFromFormat('Y-m-d', $date);
if (!$dateObject || $dateObject->format('Y-m-d') !== $date ||
    $date < date('Y-m-d')) {
    redirect_with_message('error', 'Please choose a valid current or future date.');
}

try {
    $pdo->beginTransaction();

    $duplicate = $pdo->prepare("
        SELECT appointment_id
        FROM appointments
        WHERE phone = ?
          AND appointment_date = ?
          AND status = 'Booked'
        LIMIT 1
    ");
    $duplicate->execute([$phone, $date]);

    if ($duplicate->fetch()) {
        $pdo->rollBack();
        redirect_with_message('error',
            'You already have an appointment booked for this date.');
    }

    $mechanic = $pdo->prepare("
        SELECT mechanic_id
        FROM mechanics
        WHERE mechanic_id = ?
          AND active = 1
        FOR UPDATE
    ");
    $mechanic->execute([$mechanicId]);

    if (!$mechanic->fetch()) {
        $pdo->rollBack();
        redirect_with_message('error', 'The selected mechanic is not available.');
    }

    $count = $pdo->prepare("
        SELECT COUNT(*)
        FROM appointments
        WHERE mechanic_id = ?
          AND appointment_date = ?
          AND status = 'Booked'
    ");
    $count->execute([$mechanicId, $date]);
    $booked = (int)$count->fetchColumn();

    if ($booked >= 4) {
        $pdo->rollBack();
        redirect_with_message('error',
            'The selected mechanic is fully booked for this date.');
    }

    $insert = $pdo->prepare("
        INSERT INTO appointments
        (client_name, address, phone, car_license, engine_number,
         appointment_date, mechanic_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $insert->execute([
        $name,
        $address,
        $phone,
        $carLicense,
        $engineNumber,
        $date,
        $mechanicId
    ]);

    $appointmentId = (int)$pdo->lastInsertId();
    $pdo->commit();

    header('Location: success.php?id=' . $appointmentId);
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirect_with_message('error', 'An unexpected error occurred. Please try again.');
}
