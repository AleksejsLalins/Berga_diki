<?php
session_start();

// ⏱️ Проверка сессии по тайм-ауту (1 stunda = 3600 sekundes)
$timeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
}
$_SESSION['last_activity'] = time(); // Обновить время активности

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'config.php';

    $username = $_POST['username'];
    $password = $_POST['password'];

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch();

    // ✅ Проверка пароля
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: edit.php"); // Pāradresācija uz admin paneli
        exit();
    } else {
        $error = 'Nepareizs lietotājvārds vai parole.';
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title>Admina Pieteikšanās</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: white;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        .login-container img {
            width: 100px;
            margin-bottom: 20px;
        }

        h2 {
            margin-bottom: 20px;
        }

        input[type="text"], input[type="password"] {
            padding: 10px;
            width: 90%;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            padding: 10px 20px;
            background: #0073e6;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        button:hover {
            background: #005bb5;
        }

        .error {
            color: red;
            margin-top: 10px;
            font-size: 15px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <!-- 🔧 LOGO -->
    <img src="../images/logo.png" alt="Logo">

    <h2>Pieslēgties Admin Panelim</h2>

    <form method="POST">
        <input type="text" name="username" placeholder="Lietotājvārds" required><br>
        <input type="password" name="password" placeholder="Parole" required><br>
        <button type="submit">Ieiet</button>
    </form>

    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</div>

</body>
</html>
