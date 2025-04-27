<?php
require_once 'config.php';
// session_start();

// ⏱ Sēdes pārbaude (1 stunda)
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
$_SESSION['last_activity'] = time(); // Atjaunojam taimautu

$pdo = getDBConnection();

// ✅ Jauna klienta saglabāšana (POST pieprasījums)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['phone'], $_POST['email'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $note = trim($_POST['note'] ?? '');

    if ($name && $phone && $email) {
        $stmt = $pdo->prepare("INSERT INTO customers (full_name, phone, email, notes, created_at) VALUES (:name, :phone, :email, :note, NOW())");
        $stmt->execute([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'note' => $note
        ]);
        header("Location: clients.php");
        exit();
    }
}

// 🔍 Meklēšana
$name_search = $_GET['name'] ?? '';
$phone_search = $_GET['phone'] ?? '';

$search_sql = "SELECT * FROM customers WHERE 1";
$params = [];

if ($name_search) {
    $search_sql .= " AND full_name LIKE :name";
    $params['name'] = "%$name_search%";
}
if ($phone_search) {
    $search_sql .= " AND phone LIKE :phone";
    $params['phone'] = "%$phone_search%";
}

$stmt = $pdo->prepare($search_sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Klientu pārvaldība</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 2em;
            background-color: #f5f5f5;
            color: #333;
        }

        h2 {
            color: #114b5f;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        form.add-form, form.search-form {
            background-color: #fff;
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }

        form.add-form input,
        form.search-form input {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        form.add-form textarea {
            padding: 8px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 14px;
        }

        form.add-form button,
        form.search-form button,
        .back-link {
            background-color: #114b5f;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link {
            background-color: #888;
            margin-bottom: 20px;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }

        th {
            background-color: #e9ecef;
            text-align: left;
        }

        .actions {
            text-align: center;
            white-space: nowrap;
        }

        .actions a {
            margin: 0 4px;
            padding: 6px 10px;
            background-color: #114b5f;
            color: #fff;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
        }

        .note-col {
            width: 30%;
        }

        .created-col {
            white-space: nowrap;
            width: 120px;
        }

        .verified-col {
            width: 100px;
            text-align: center;
        }

        .actions-col {
            width: 130px;
        }

        .delete-button {
            background-color: #d9534f;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .delete-button:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Klientu pārvaldība</h2>

    <!-- 🔙 Atpakaļ uz administrēšanu -->
    <a href="edit.php" class="back-link">← Atpakaļ uz admin paneli</a>

    <!-- 🔍 Meklēšana -->
    <form method="GET" class="search-form">
        <input type="text" name="name" placeholder="Vārds..." value="<?= htmlspecialchars($name_search) ?>">
        <button type="submit">Meklēt</button>

        <input type="text" name="phone" placeholder="Telefons..." value="<?= htmlspecialchars($phone_search) ?>">
        <button type="submit">Meklēt</button>
    </form>

    <!-- ➕ Jauna klienta pievienošana -->
    <form method="POST" class="add-form">
        <h3>Pievienot jaunu klientu</h3>
        <input type="text" name="name" placeholder="Vārds" required>
        <input type="text" name="phone" placeholder="Telefons" required>
        <input type="email" name="email" placeholder="E-pasts" required>
        <textarea name="note" placeholder="Piezīme (nav obligāti)"></textarea>
        <button type="submit">Saglabāt klientu</button>
    </form>

    <!-- 📋 Klientu saraksts -->
    <table>
        <thead>
        <tr>
            <th>Vārds</th>
            <th>Telefons</th>
            <th>E-pasts</th>
            <th class="created-col">Pievienots</th>
            <th class="note-col">Piezīme</th>
            <th class="verified-col">Verificēts</th>
            <th class="actions-col">Darbības</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($customers as $customer): ?>
            <tr>
                <td><?= htmlspecialchars($customer['full_name']) ?></td>
                <td><?= htmlspecialchars($customer['phone']) ?></td>
                <td><?= htmlspecialchars($customer['email']) ?></td>
                <td class="created-col"><?= htmlspecialchars($customer['created_at']) ?></td>
                <td class="note-col"><?= nl2br(htmlspecialchars($customer['notes'] ?? '')) ?></td>
                <td class="verified-col"><?= $customer['verified'] ? '✓' : '✗' ?></td>
                <td class="actions">
                    <a href="edit_client.php?id=<?= $customer['id'] ?>">Rediģēt</a>
                    <a href="delete_client.php?id=<?= $customer['id'] ?>" onclick="return confirm('Vai tiešām dzēst šo klientu?')" class="delete-button">Dzēst</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
