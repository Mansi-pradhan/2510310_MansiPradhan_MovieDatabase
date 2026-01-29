<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/session.php';
requireLogin();

$con = dbConnect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = (int) $_POST['movie_id'];
    $user_id = (int) $_SESSION['user_id'];
    $rating = (int) $_POST['rating'];
    $review = trim($_POST['review']);

    // Validate rating
    if ($rating < 1 || $rating > 5) die("Invalid rating value.");

    // Validate movie exists
    $checkMovie = $con->prepare("SELECT id FROM movies WHERE id = ?");
    $checkMovie->execute([$movie_id]);
    if (!$checkMovie->fetch()) die("Invalid movie ID.");
    // Check if user has already rated
    $checkStmt = $con->prepare("SELECT id FROM reviews WHERE movie_id = ? AND user_id = ?");
    $checkStmt->execute([$movie_id, $user_id]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        $updateStmt = $con->prepare("UPDATE reviews SET rating = ?, review = ?, created_at = CURRENT_TIMESTAMP WHERE id = ?");
        $updateStmt->execute([$rating, $review, $existing['id']]);
    } else {
        $insertStmt = $con->prepare("INSERT INTO reviews (movie_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
        $insertStmt->execute([$movie_id, $user_id, $rating, $review]);
    }

    header("Location: movie.php?id=$movie_id");
    exit;
}
?>