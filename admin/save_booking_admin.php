<?php
// save_booking_admin.php - Saglabā rezervāciju no admin paneļa
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $sector       = (int) ($_POST['sector'] ?? 0);
    $date         = $_POST['date'] ?? '';
    $time_slot    = $_POST['time_slot'] ?? '';
    $custom_time  = trim($_POST['custom_time'] ?? '');
    $verified     = isset($_POST['verified']) ? 1 : 0;

    if (!$name || !$sector || !$date || !$time_slot) {
        die('Trūkst obligāto lauku.');
    }

    // 1. Pārbaudām vai šāds klients jau eksistē
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE full_name = :name AND phone = :phone LIMIT 1");
    $stmt->execute([
        'name'  => $name,
        'phone' => $phone
    ]);
    $existing = $stmt->fetch();

    if ($existing) {
        $customer_id = $existing['id'];
    } else {
        // 2. Ja nav, ievietojam jaunu klientu
        $stmt = $pdo->prepare("INSERT INTO customers (full_name, phone, email, verified, created_at) VALUES (:name, :phone, :email, :verified, NOW())");
        $stmt->execute([
            'name'     => $name,
            'phone'    => $phone,
            'email'    => $email,
            'verified' => $verified
        ]);
        $customer_id = $pdo->lastInsertId();
    }

    // 3. Ievietojam rezervāciju
    $stmt = $pdo->prepare("INSERT INTO booking (customer_id, sector, date, time_slot, custom_time, status, created_at) VALUES (:customer_id, :sector, :date, :time_slot, :custom_time, 'confirmed', NOW())");
    $stmt->execute([
        'customer_id' => $customer_id,
        'sector'      => $sector,
        'date'        => $date,
        'time_slot'   => $time_slot,
        'custom_time' => $time_slot === 'custom' ? $custom_time : null
    ]);

    header("Location: edit_booking.php");
    exit();
} else {
    die('Nederīgs pieprasījums');
}
