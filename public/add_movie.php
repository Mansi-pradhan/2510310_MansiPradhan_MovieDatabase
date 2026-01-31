<?php
require __DIR__ . '/../includes/session.php';
requireAdmin();
require __DIR__ . '/../config/db.php';

$con = dbConnect();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $cast_names = trim($_POST['cast_names']);
    $release_year = $_POST['release_year'];
    $duration = $_POST['duration'];
    $genreInput = trim($_POST['genres']); 
$posterFileName = null;

if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $fileType = mime_content_type($_FILES['poster']['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        die("Invalid image type. Only JPG, PNG, WEBP allowed.");
    }

    if ($_FILES['poster']['size'] > 2 * 1024 * 1024) {
        die("Poster image must be less than 2MB.");
    }

    $ext = pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION);
    $posterFileName = uniqid('poster_', true) . '.' . $ext;

    $uploadPath = __DIR__ . '/../public/uploads/posters/' . $posterFileName;

    if (!move_uploaded_file($_FILES['poster']['tmp_name'], $uploadPath)) {
        die("Failed to upload poster.");
    }
}
    if ($title !== '') {

       
        $sql = "INSERT INTO movies 
(title, description, cast_names, release_year, duration, poster)
VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $con->prepare($sql);
        $stmt->execute([
            $title,
            $description,
            $cast_names,
            $release_year ?: null,
            $duration ?: null,
            $posterFileName
        ]);
       
        $movieId = $con->lastInsertId();

        
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

        $message = "Movie added successfully!";
    } else {
        $message = "Title is required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Movie</title>
</head>
<body>

<h1>Add Movie</h1>

<?php if ($message): ?>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <label>Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Description:</label><br>
    <textarea name="description"></textarea><br><br>

    <label>Cast:</label><br>
    <textarea name="cast_names" placeholder="Actor 1, Actor 2, Actor 3"></textarea><br><br>

    <label>Release Year:</label><br>
    <input type="number" name="release_year"><br><br>

    <label>Duration (minutes):</label><br>
    <input type="number" name="duration"><br><br>

    <label>Genres (comma-separated):</label><br>
    <input type="text" name="genres" placeholder="Action, Sci-Fi, Adventure"><br><br>
 <label>Poster:</label><br>
<input type="file" name="poster" accept="image/*"><br><br>
    <button type="submit">Add Movie</button>
</form>

<p><a href="../public/index.php">Back to Movies</a></p>

</body>
</html>
