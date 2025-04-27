<?php
require_once 'config.php';
// session_start();

// ⏱ Sesijas pārbaude (1 stunda)
$timeout = 3600;
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
$_SESSION['last_activity'] = time(); // Atjauno sesijas taimautu

$pdo = getDBConnection();
$id = $_GET['id'] ?? null;

// ✅ Datu izgūšana
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
$stmt->execute(['id' => $id]);
$client = $stmt->fetch();

if (!$client) {
    echo "Klients nav atrasts.";
    exit();
}

// 💾 Saglabāšana
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $note = $_POST['note'] ?? '';
    $verified = isset($_POST['verified']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE customers SET full_name = :name, phone = :phone, email = :email, notes = :note, verified = :verified WHERE id = :id");
    $stmt->execute([
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'note' => $note,
        'verified' => $verified,
        'id' => $id
    ]);

    header("Location: clients.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Rediģēt klientu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
            margin: 2em;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        h2 {
            color: #114b5f;
            margin-bottom: 1em;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 600;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            height: 100px;
        }

        .actions {
            margin-top: 20px;
        }

        .button,
        .back-link {
            background-color: #114b5f;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .back-link {
            background-color: #888;
            margin-right: 10px;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }

        .checkbox-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }

        .checkbox-row label {
            margin: 0;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Rediģēt klientu</h2>

    <!-- 🔙 Atgriezties uz klientu sarakstu -->
    <a href="clients.php" class="back-link">← Atpakaļ uz klientu sarakstu</a>

<!-- 🔙 Atgriezties uz rezervāciju sarakstu -->
    <a href="edit_booking.php" class="back-link">← Atpakaļ uz rezervāciju sarakstu</a>
    <!-- ✏️ Rediģēšanas forma -->
    <form method="POST">
        <label for="name">Vārds:</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($client['full_name']) ?>" required>

        <label for="phone">Telefons:</label>
        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($client['phone']) ?>" required>

        <label for="email">E-pasts:</label>
        <input type="email" name="email" id="email" value="<?= htmlspecialchars($client['email']) ?>" required>

        <label for="note">Piezīme:</label>
        <textarea name="note" id="note"><?= htmlspecialchars($client['notes'] ?? '') ?></textarea>

        <!-- ☑️ Verificēšana -->
        <div class="checkbox-row">
            <input type="checkbox" id="verified" name="verified" <?= $client['verified'] ? 'checked' : '' ?>>
            <label for="verified">Verificēts klients</label>
        </div>

        <div class="actions">
            <button type="submit" class="button">💾 Saglabāt izmaiņas</button>
        </div>
    </form>
</div>

</body>
</html>
