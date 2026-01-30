<?php
require __DIR__ . '/../includes/session.php';
requireLogin();
require __DIR__ . '/../config/db.php';

$con = dbConnect();

if (!isset($_GET['movie_id']) || empty($_GET['movie_id'])) {
    die("Movie ID is required.");
}

$movieId = (int) $_GET['movie_id'];
$userId = $_SESSION['user_id'];

// Delete the review only if it belongs to this user
$deleteStmt = $con->prepare("DELETE FROM reviews WHERE movie_id = ? AND user_id = ?");
$deleteStmt->execute([$movieId, $userId]);

header("Location: movie.php?id=$movieId");
exit;
?>