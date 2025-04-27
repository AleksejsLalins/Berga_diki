<?php
// =============================
// edit_booking.php – Admin Panel for Reservations
// =============================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';

$pdo = getDBConnection();

// === Filters from GET params ===
$name   = $_GET['name'] ?? '';
$phone  = $_GET['phone'] ?? '';
$date   = $_GET['date'] ?? '';
$sector = $_GET['sector'] ?? '';

$where = [];
$params = [];

// === Filter: name
if ($name) {
    $where[] = 'c.full_name LIKE :name';
    $params['name'] = "%$name%";
}

// === Filter: phone
if ($phone) {
    $where[] = 'c.phone LIKE :phone';
    $params['phone'] = "%$phone%";
}

// === Filter: date
if ($date) {
    $where[] = 'b.date = :date';
    $params['date'] = $date;
}

// === Filter: sector
if ($sector) {
    $where[] = 'b.sector = :sector';
    $params['sector'] = $sector;
}

// ✅ Show only confirmed bookings
$where[] = 'b.status = :status';
$params['status'] = 'confirmed';

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// === Main booking query with customer data ===
$sql = "
    SELECT b.*, c.full_name, c.phone, c.email, c.verified, c.id AS customer_id
    FROM booking b
    JOIN customers c ON b.customer_id = c.id
    $whereSQL " . ($whereSQL ? " AND" : "WHERE") . " b.date >= CURDATE()
    ORDER BY b.date ASC
";


$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <title>Rezervāciju pārvaldība</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/globals.css?v=<?= time() ?>">
  <link rel="stylesheet" href="../css/admin_calendar.css">
  <script src="../js/admin_booking.js?v=<?= time() ?>" defer></script>
</head>
<body>
<div class="container">
  <h2>Rezervāciju pārvaldība</h2>

  <!-- 🔙 Back to Admin Panel -->
  <a href="edit.php" class="back-link">&larr; Atpakaļ uz admin paneli</a>

  <!-- 🔍 Search form -->
  <form method="GET">
    <input type="text" name="name" placeholder="Vārds..." value="<?= htmlspecialchars($name) ?>">
    <input type="text" name="phone" placeholder="Telefons..." value="<?= htmlspecialchars($phone) ?>">
    <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
    <select name="sector">
      <option value="">-- Sektors --</option>
      <?php for ($i = 1; $i <= 11; $i++): ?>
        <option value="<?= $i ?>" <?= $sector == $i ? 'selected' : '' ?>><?= $i ?></option>
      <?php endfor; ?>
    </select>
    <button type="submit">Meklēt</button>
  </form>

  <!-- ➕ Add Booking Form -->
  <form method="POST" action="save_booking_admin.php">
    <h3>Pievienot jaunu rezervāciju</h3>
    <input type="text" name="name" placeholder="Vārds *" required>
    <input type="text" name="phone" placeholder="Telefons">
    <input type="email" name="email" placeholder="E-pasts">
    <label><input type="checkbox" name="verified" value="1"> Verificēt klientu</label><br>
    <select name="sector" required>
      <option value="">-- Sektors --</option>
      <?php for ($i = 1; $i <= 11; $i++): ?>
        <option value="<?= $i ?>"><?= $i ?></option>
      <?php endfor; ?>
    </select>
    <input type="date" name="date" required>
    <select name="time_slot" required>
      <option value="full">Pilna diena</option>
      <option value="half_am">Līdz 12:00</option>
      <option value="half_pm">Pēc 12:00</option>
      <option value="custom">Custom</option>
    </select>
    <input type="text" name="custom_time" placeholder="Custom laiks (piem. 08:00-11:00)">
    <button type="submit">Saglabāt</button>
  </form>

  <!-- 📆 Calendar Occupancy -->
  <div class="admin-calendar-area">
    <h3>Pārbaudīt sektoru aizņemtību</h3>
    <div class="sector-buttons">
      <?php for ($i = 1; $i <= 11; $i++): ?>
        <button class="sector-btn" data-sector="<?= $i ?>">Sektors <?= $i ?></button>
      <?php endfor; ?>
    </div>
    <table class="calendar-table">
      <thead>
        <tr><th>P</th><th>O</th><th>T</th><th>C</th><th>Pk</th><th>S</th><th>Sv</th></tr>
      </thead>
      <tbody id="calendar-body">
        <!-- JS fills in calendar -->
      </tbody>
    </table>
  </div>

  <!-- 📘 Calendar Legend -->
  <div class="calendar-legend">
    <span class="legend-item available">Brīvs</span>
    <span class="legend-item half-am">Līdz 12:00</span>
    <span class="legend-item half-pm">Pēc 12:00</span>
    <span class="legend-item booked">Pilna diena</span>
    <span class="legend-item custom-booked">Pielāgots laiks</span>
  </div>

  <!-- Spacer between legend and table -->
  <div style="height: 40px;"></div>

  <!-- 📋 Confirmed Bookings Table -->
  <table>
    <thead>
      <tr>
        <th>Vārds</th>
        <th>Telefons</th>
        <th>E-pasts</th>
        <th>Sektors</th>
        <th>Datums</th>
        <th>Tips</th>
        <th>Verificēts</th>
        <th>Atc.kods</th>
        <th></th> <!-- для крестика -->
      </tr>
    </thead>
    <tbody>
      <?php foreach ($reservations as $r): ?>
        <tr>
          <td><a href="edit_client.php?id=<?= $r['customer_id'] ?>"><?= htmlspecialchars($r['full_name']) ?></a></td>
          <td><?= htmlspecialchars($r['phone']) ?></td>
          <td><?= htmlspecialchars($r['email']) ?></td>
          <td><?= $r['sector'] ?></td>
          <td><?= $r['date'] ?></td>
          <td><?= $r['time_slot'] === 'custom' ? htmlspecialchars($r['custom_time']) : $r['time_slot'] ?></td>
          <td><?= $r['verified'] ? '✓' : '✗' ?></td>
          <td><?= htmlspecialchars($r['cancel_code'] ?? '') ?></td>
          <td>
  <a href="cancel_booking_admin.php?id=<?= $r['id'] ?>"
     onclick="return confirm('Vai tiešām atcelt šo rezervāciju?')"
     class="delete-button" title="Atcelt rezervāciju">✖</a>
</td>

        </tr>
        
      <?php endforeach; ?>
    </tbody>
  </table>
  <div style="margin-top: 40px;">
  <a href="archieve_booking.php" class="back-link">📦 Skatīt arhīvu</a>
</div>

</div>
</body>
</html>
