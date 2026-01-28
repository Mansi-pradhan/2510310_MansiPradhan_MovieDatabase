<?php
require __DIR__ . '/../includes/session.php';
requireAdmin();
require __DIR__ . '/../config/db.php';

$con = dbConnect();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Movie ID is required.");
}

$movieId = $_GET['id'];

$checkSql = "SELECT title FROM movies WHERE id = ?";
$checkStmt = $con->prepare($checkSql);
$checkStmt->execute([$movieId]);
$movie = $checkStmt->fetch();

if (!$movie) {
    die("Movie not found.");
}


$deleteSql = "DELETE FROM movies WHERE id = ?";
$deleteStmt = $con->prepare($deleteSql);
$deleteStmt->execute([$movieId]);


header("Location: ../public/index.php");
exit;