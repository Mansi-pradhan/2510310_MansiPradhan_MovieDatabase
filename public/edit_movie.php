<?php
require __DIR__ . '/../includes/session.php';
requireAdmin();
require __DIR__ . '/../config/db.php';

$con = dbConnect();
$message = '';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Movie ID is required.");
}

$movieId = (int) $_GET['id'];

$sql = "SELECT id, title, description, cast_names, release_year, duration FROM movies WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->execute([$movieId]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    die("Movie not found.");
}

$genreIdsStmt = $con->prepare("SELECT genre_id FROM movie_genres WHERE movie_id = ?");
$genreIdsStmt->execute([$movieId]);
$genreIds = $genreIdsStmt->fetchAll(PDO::FETCH_COLUMN);

$currentGenres = [];
if (!empty($genreIds)) {
    foreach ($genreIds as $genreId) {
        $gStmt = $con->prepare("SELECT name FROM genres WHERE id = ?");
        $gStmt->execute([$genreId]);
        $genreName = $gStmt->fetchColumn();
        if ($genreName) $currentGenres[] = $genreName;
    }
}
$genresString = implode(", ", $currentGenres);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $cast_names = trim($_POST['cast_names']);
    $release_year = $_POST['release_year'];
    $duration = $_POST['duration'];
    $genreInput = trim($_POST['genres']); 
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

        $deleteStmt = $con->prepare("DELETE FROM movie_genres WHERE movie_id = ?");
        $deleteStmt->execute([$movieId]);

        if ($genreInput !== '') {
            $genresArray = array_map('trim', explode(',', $genreInput));
            foreach ($genresArray as $genreName) {
                if ($genreName === '') continue;


                $checkStmt = $con->prepare("SELECT id FROM genres WHERE name = ?");
                $checkStmt->execute([$genreName]);
                $genreId = $checkStmt->fetchColumn();

                if (!$genreId) {
                    $insertGenre = $con->prepare("INSERT INTO genres (name) VALUES (?)");
                    $insertGenre->execute([$genreName]);
                    $genreId = $con->lastInsertId();
                }
            
                $insertMovieGenre = $con->prepare("INSERT INTO movie_genres (movie_id, genre_id) VALUES (?, ?)");
                $insertMovieGenre->execute([$movieId, $genreId]);
            }
        }

        $message = "Movie updated successfully!";
        $stmt->execute([$movieId]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);

        $genreIdsStmt->execute([$movieId]);
        $genreIds = $genreIdsStmt->fetchAll(PDO::FETCH_COLUMN);
        $currentGenres = [];
        if (!empty($genreIds)) {
            foreach ($genreIds as $genreId) {
                $gStmt = $con->prepare("SELECT name FROM genres WHERE id = ?");
                $gStmt->execute([$genreId]);
                $genreName = $gStmt->fetchColumn();
                if ($genreName) $currentGenres[] = $genreName;
            }
        }
        $genresString = implode(", ", $currentGenres);
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
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<form method="post">

    <label>Title:</label><br>
    <input type="text" name="title" value="<?= htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8') ?>" required><br><br>

    <label>Description:</label><br>
    <textarea name="description"><?= htmlspecialchars($movie['description'], ENT_QUOTES, 'UTF-8') ?></textarea><br><br>

    <label>Cast:</label><br>
    <textarea name="cast_names"><?= htmlspecialchars($movie['cast_names'], ENT_QUOTES, 'UTF-8') ?></textarea><br><br>

    <label>Release Year:</label><br>
    <input type="number" name="release_year" value="<?= htmlspecialchars($movie['release_year']); ?>"><br><br>

    <label>Duration (minutes):</label><br>
    <input type="number" name="duration" value="<?= htmlspecialchars($movie['duration']); ?>"><br><br>
<label>Genres (comma-separated):</label><br>
    <input type="text" name="genres" value="<?= htmlspecialchars($genresString); ?>" placeholder="Action, Sci-Fi, Adventure"><br><br>

    <button type="submit">Update Movie</button>

</form>

<p><a href="../public/index.php">Back to Movies</a></p>

</body>
</html>