<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/session.php'; 
$con = dbConnect();

$sql = 'SELECT id, title, release_year, created_at FROM movies ORDER BY created_at DESC';
$stmt = $con->prepare($sql);
$stmt->execute();
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Movies</title>
</head>
<body>

<h1>Movies</h1>
<?php if (isAdmin()): ?>
    <p>
        <a href="../public/add_movie.php" style="padding: 8px 12px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">
            + Add Movie
        </a>
    </p>
<?php endif; ?>
<?php if (empty($movies)): ?>
    <p>No movies found.</p>
<?php else: ?>
    <?php foreach($movies as $movie): ?>
        <article>
            <h2>
                <a href="movie.php?id=<?= $movie['id'] ?>">
                    <?= htmlspecialchars($movie['title'], ENT_QUOTES, 'UTF-8') ?>
                </a>
                <?php if (isAdmin()): ?>
                    | <a href="../public/edit_movie.php?id=<?= $movie['id'] ?>">Edit</a>
                    | <a href="../public/delete_movie.php?id=<?= $movie['id'] ?>"
                         onclick="return confirm('Are you sure you want to delete this movie?');">
                        Delete
                    </a>
                <?php endif; ?>
            </h2>
            <p>
                Release Year: <?= $movie['release_year'] ?? 'N/A' ?> |
                Added on: <?= date("F j, Y", strtotime($movie['created_at'])) ?>
            </p>
        </article>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
