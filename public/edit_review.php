<?php
require __DIR__ . '/../includes/session.php';
requireLogin();
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/header.php';

$con = dbConnect();

if (!isset($_GET['movie_id']) || empty($_GET['movie_id'])) {
    die("Movie ID is required.");
}

$movieId = (int) $_GET['movie_id'];
$userId = $_SESSION['user_id'];

$stmt = $con->prepare("SELECT rating, review FROM reviews WHERE movie_id = ? AND user_id = ?");
$stmt->execute([$movieId, $userId]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$review) {
    die("Review not found.");
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int) $_POST['rating'];
    $reviewText = trim($_POST['review']);

    if ($rating < 1 || $rating > 5) {
        $message = "Invalid rating value.";
    } else {
        $updateStmt = $con->prepare("UPDATE reviews SET rating = ?, review = ?, created_at = CURRENT_TIMESTAMP WHERE movie_id = ? AND user_id = ?");
        $updateStmt->execute([$rating, $reviewText, $movieId, $userId]);
        header("Location: movie.php?id=$movieId");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Your Review</title>
</head>
<body>

<h1>Edit Your Review</h1>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post">
    <label>Rating (1–5):</label><br>
    <select name="rating" required>
        <option value="">--Select--</option>
        <?php for ($i = 1; $i <= 5; $i++): ?>
            <option value="<?= $i ?>" <?= ($review['rating'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
        <?php endfor; ?>
    </select><br><br>

    <label>Review:</label><br>
    <textarea name="review"><?= htmlspecialchars($review['review']) ?></textarea><br><br>

    <button type="submit">Update Review</button>
</form>

<p><a href="movie.php?id=<?= $movieId ?>">Back to Movie</a></p>

</body>
</html>