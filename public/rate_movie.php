<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/session.php';
requireLogin();

$con = dbConnect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $movie_id = isset($_POST['movie_id']) ? (int) $_POST['movie_id'] : 0;
    $user_id  = (int) $_SESSION['user_id'];
    $rating   = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
    $review   = isset($_POST['review']) ? trim($_POST['review']) : '';


    if ($rating < 1 || $rating > 5) {
        die("Invalid rating value.");
    }

    $review = substr($review, 0, 1000);

    $checkMovie = $con->prepare("SELECT id FROM movies WHERE id = ?");
    $checkMovie->execute([$movie_id]);
    if (!$checkMovie->fetch()) {
        die("Invalid movie ID.");
    }
    $checkStmt = $con->prepare("SELECT id FROM reviews WHERE movie_id = ? AND user_id = ?");
    $checkStmt->execute([$movie_id, $user_id]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        
        $updateStmt = $con->prepare("
            UPDATE reviews 
            SET rating = ?, review = ?, created_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $updateStmt->execute([$rating, $review, $existing['id']]);
    } else {
        
        $insertStmt = $con->prepare("
            INSERT INTO reviews (movie_id, user_id, rating, review, created_at) 
            VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)
        ");
        $insertStmt->execute([$movie_id, $user_id, $rating, $review]);
    }

    header("Location: movie.php?id=$movie_id");
    exit;
}
?>