<?php
require __DIR__ . '/../includes/session.php';
requireAdmin(); // Only admins can access
require __DIR__ . '/../config/db.php';

$con = dbConnect();


if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Movie ID is required.");
}

$movieId = (int) $_GET['id'];


$checkSql = "SELECT title FROM movies WHERE id = ?";
$checkStmt = $con->prepare($checkSql);
$checkStmt->execute([$movieId]);
$movie = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    die("Movie not found.");
}

$deleteSql = "DELETE FROM movies WHERE id = ?";
$deleteStmt = $con->prepare($deleteSql);
$deleteStmt->execute([$movieId]);


$con->prepare("DELETE FROM reviews WHERE movie_id = ?")->execute([$movieId]);
$con->prepare("DELETE FROM movie_genres WHERE movie_id = ?")->execute([$movieId]);

header("Location: ../public/index.php");
exit;
?>