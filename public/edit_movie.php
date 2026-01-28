<?php
require __DIR__ . '/../config/db.php';

$con = dbConnect();
$message = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Movie ID is required.");
}

$movieId = $_GET['id'];

$sql = "SELECT id, title, description, cast_names, release_year, duration 
        FROM movies WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->execute([$movieId]);
$movie = $stmt->fetch();

if (!$movie) {
    die("Movie not found.");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $cast_names = trim($_POST['cast_names']);
    $release_year = $_POST['release_year'];
    $duration = $_POST['duration'];

    if ($title !== '') {
        $updateSql = "UPDATE movies 
                      SET title = ?, description = ?, cast_names = ?, release_year = ?, duration = ?
                      WHERE id = ?";
        $updateStmt = $con->prepare($updateSql);
        $updateStmt->execute([
            $title,
            $description,
            $cast_names,
            $release_year ?: null,
            $duration ?: null,
            $movieId
        ]);
$message = "Movie updated successfully!";

        $stmt = $con->prepare("SELECT id, title, description, cast_names, release_year, duration FROM movies WHERE id = ?");
        $stmt->execute([$movieId]);
        $movie = $stmt->fetch();
    } else {
        $message = "Title is required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Movie</title>
</head>
<body>

<h1>Edit Movie</h1>

<?php if ($message): ?>
    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="post">

    <label>Title:</label><br>
    <input type="text" name="title" value="<?php echo htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8'); ?>" required><br><br>

    <label>Description:</label><br>
    <textarea name="description"><?php echo htmlspecialchars($movie['description'], ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>

    <label>Cast:</label><br>
    <textarea name="cast_names"><?php echo htmlspecialchars($movie['cast_names'], ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>

    <label>Release Year:</label><br>
    <input type="number" name="release_year" value="<?php echo htmlspecialchars($movie['release_year']); ?>"><br><br>

    <label>Duration (minutes):</label><br>
    <input type="number" name="duration" value="<?php echo htmlspecialchars($movie['duration']); ?>"><br><br>

    <button type="submit">Update Movie</button>

</form>

<p><a href="../public/index.php">Back to Movies</a></p>

</body>
</html>
