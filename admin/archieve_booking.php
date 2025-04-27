<?php 
// =============================
// archieve_booking.php – Arhivētas rezervācijas (pagājušās)
// =============================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
require_once 'db.php';

$pdo = getDBConnection();

// === Saņemam GET filtrus no vaicājuma ===
$name   = $_GET['name'] ?? '';
$phone  = $_GET['phone'] ?? '';
$date   = $_GET['date'] ?? '';
$sector = $_GET['sector'] ?? '';

// === Dinamisks WHERE filtrs ===
$where = [];
$params = [];

if ($name) {
    $where[] = 'c.full_name LIKE :name';
    $params['name'] = "%$name%";
}

if ($phone) {
    $where[] = 'c.phone LIKE :phone';
    $params['phone'] = "%$phone%";
}

if ($date) {
    $where[] = 'b.date = :date';
    $params['date'] = $date;
}

if ($sector) {
    $where[] = 'b.sector = :sector';
    $params['sector'] = $sector;
}

// ✅ Tikai apstiprinātas rezervācijas
$where[] = 'b.status = :status';
$params['status'] = 'confirmed';

// ✅ Tikai pagājušas rezervācijas (mazākas par šodienu)
$where[] = 'b.date < CURDATE()';

// === SQL pieprasījums ar filtriem ===
$whereSQL = 'WHERE ' . implode(' AND ', $where);
$sql = "
    SELECT b.*, c.full_name, c.phone, c.email, c.verified, c.id AS customer_id
    FROM booking b
    JOIN customers c ON b.customer_id = c.id
    $whereSQL
    ORDER BY b.date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <title>Arhivētās rezervācijas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Stils un JS -->
  <link rel="stylesheet" href="../css/admin_calendar.css">
  <script src="../js/admin_booking.js?v=<?= time() ?>" defer></script>
</head>
<body>
<div class="container">
  <h2>📦 Arhivētās rezervācijas</h2>

  <!-- 🔙 Atpakaļ uz aktīvajām -->
  <a href="edit_booking.php" class="back-link">&larr; Atpakaļ uz aktīvajām rezervācijām</a>

  <!-- 🔍 Meklēšanas forma -->
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

  <!-- 📋 Tabula ar vēsturiskām rezervācijām -->
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
        <th></th> <!-- Kolonna dzēšanai -->
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
</div>
</body>
</html>
