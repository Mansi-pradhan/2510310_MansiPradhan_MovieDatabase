<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie DB</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="../assets/script.js" defer></script>
</head>
<body>
<header style="padding: 10px; background-color: #333; color: white;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h1 style="margin: 0;"><a href="index.php" style="color: white; text-decoration: none;">Movie DB</a></h1>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span> |
                <a href="../public/logout.php" style="color: white;">Logout</a>
                <?php if (isAdmin()): ?>
                    | <a href="../public/add_movie.php" style="color: white;">Add Movie</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="../public/login.php" style="color: white;">Login</a> |
                <a href="../public/register.php" style="color: white;">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main style="padding: 20px;">