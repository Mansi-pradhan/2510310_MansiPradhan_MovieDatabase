<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/session.php';

$con = dbConnect();


if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Movie ID is required.");
}
$movieId = (int) $_GET['id'];

$sql = "SELECT * FROM movies WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->execute([$movieId]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    die("Movie not found.");
}

$genreIdsStmt = $con->prepare("SELECT genre_id FROM movie_genres WHERE movie_id = ?");
$genreIdsStmt->execute([$movieId]);
$genreIds = $genreIdsStmt->fetchAll(PDO::FETCH_COLUMN);
$genres = [];
if (!empty($genreIds)) {
    foreach ($genreIds as $genreId) {
        $gStmt = $con->prepare("SELECT name FROM genres WHERE id = ?");
        $gStmt->execute([$genreId]);
        $genreName = $gStmt->fetchColumn();
        if ($genreName) {
            $genres[] = $genreName;
        }
    }
}

$ratingStmt = $con->prepare("SELECT rating FROM reviews WHERE movie_id = ?");
$ratingStmt->execute([$movieId]);
$allRatings = $ratingStmt->fetchAll(PDO::FETCH_COLUMN);

$totalReviews = count($allRatings);
$avgRating = $totalReviews > 0 ? round(array_sum($allRatings) / $totalReviews, 1) : "No ratings yet";

$reviewStmt = $con->prepare("SELECT user_id, rating, review, created_at FROM reviews WHERE movie_id = ? ORDER BY created_at DESC");
$reviewStmt->execute([$movieId]);
$reviewsRaw = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
$reviewStmt = $con->prepare("SELECT user_id, rating, review, created_at FROM reviews WHERE movie_id = ? ORDER BY created_at DESC");
$reviewStmt->execute([$movieId]);
$reviewsRaw = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);

$reviews = [];
foreach ($reviewsRaw as $r) {
    
    $userStmt = $con->prepare("SELECT name FROM users WHERE id = ?");
    $userStmt->execute([$r['user_id']]);
    $userName = $userStmt->fetchColumn();

    $reviews[] = [
        'name' => $userName,
        'rating' => $r['rating'],
        'review' => $r['review'],
        'created_at' => $r['created_at']
    ];
}
if (isset($_SESSION['user_id'])) {
    $stmt = $con->prepare("SELECT rating, review FROM reviews WHERE movie_id = ? AND user_id = ?");
    $stmt->execute([$movieId, $_SESSION['user_id']]);
    $userReview = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($movie['title']) ?></title>
</head>
<body>
    <h1><?= htmlspecialchars($movie['title']) ?></h1>
    <p><strong>Release Year:</strong> <?= htmlspecialchars($movie['release_year']) ?></p>
    <p><strong>Duration:</strong> <?= htmlspecialchars($movie['duration']) ?> minutes</p>
    <p><strong>Cast:</strong> <?= htmlspecialchars($movie['cast_names'] ?? 'N/A') ?></p>
    <p><strong>Genres:</strong> <?= !empty($genres) ? htmlspecialchars(implode(", ", $genres)) : 'N/A' ?></p>
    <p><?= nl2br(htmlspecialchars($movie['description'] ?? '')) ?></p>

    <hr>
    <h3>Rating</h3>
    <p><strong>Average Rating:</strong> <?= $avgRating ?> / 5</p>
    <p><strong>Total Reviews:</strong> <?= $totalReviews ?></p>
    <hr>
    <?php if (isset($_SESSION['user_id'])): ?>
        <h3>Your Rating</h3>
        <form method="post" action="rate_movie.php">
            <label>Rating (1–5):</label><br>
            <select name="rating" required>
                <option value="">--Select--</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= (isset($userReview['rating']) && $userReview['rating'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select><br><br>

            <label>Review (optional):</label><br>
            <textarea name="review"><?= htmlspecialchars($userReview['review'] ?? '') ?></textarea><br><br>

            <input type="hidden" name="movie_id" value="<?= $movieId ?>">
            <button type="submit">Submit</button>
        </form>
    <?php else: ?>
        <p><a href="../public/login.php">Login</a> to rate or review this movie.</p>
    <?php endif; ?>
    <hr>
    <h3>User Reviews</h3>
    <?php if (empty($reviews)): ?>
        <p>No Reviews yet.</p>
    <?php else: ?>
        <?php foreach ($reviews as $r): ?>
            <div>
                <strong><?= htmlspecialchars($r['name']) ?></strong> — <?= $r['rating'] ?>/5
                <p><?= nl2br(htmlspecialchars($r['review'])) ?></p>
                <small><?= $r['created_at'] ?></small>
                <hr>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p><a href="index.php">Back to Movies</a></p>
</body>
</html>