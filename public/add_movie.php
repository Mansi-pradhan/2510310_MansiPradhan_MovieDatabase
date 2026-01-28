<?php
require __DIR__ . '/../config/db.php';

$con = dbConnect();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $cast_names = trim($_POST['cast_names']);
    $release_year = $_POST['release_year'];
    $duration = $_POST['duration'];

    if ($title !== '') {

        $sql = "INSERT INTO movies 
                (title, description, cast_names, release_year, duration)
                VALUES (?, ?, ?, ?, ?)";
	$stmt = $con->prepare($sql);
        $stmt->execute([
            $title,
            $description,
            $cast_names,
            $release_year ?: null,
            $duration ?: null
        ]);

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
    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<form method="post">

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

    <button type="submit">Add Movie</button>

</form>

<p><a href="../public/index.php">Back to Movies</a></p>

</body>
</html>