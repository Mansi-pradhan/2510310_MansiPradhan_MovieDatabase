<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/session.php';

requireLogin(); 
$con = dbConnect();

$search = trim($_GET['search'] ?? '');
$year = $_GET['year'] ?? '';
$rating = $_GET['rating'] ?? '';


$sql = "SELECT id, title, release_year, created_at FROM movies WHERE 1";
$params = [];


if ($search) {
    $sql .= " AND title LIKE ?";
    $params[] = "%$search%";
}


if ($year) {
    $sql .= " AND release_year = ?";
    $params[] = $year;
}
$stmt = $con->prepare($sql);
$stmt->execute($params);
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($search) {
    $filtered = [];
    foreach ($movies as $movie) {
        $genreIdsStmt = $con->prepare("SELECT genre_id FROM movie_genres WHERE movie_id = ?");
        $genreIdsStmt->execute([$movie['id']]);
        $genreIds = $genreIdsStmt->fetchAll(PDO::FETCH_COLUMN);

        $match = false;
        foreach ($genreIds as $gid) {
            $gStmt = $con->prepare("SELECT name FROM genres WHERE id = ?");
            $gStmt->execute([$gid]);
            $genreName = $gStmt->fetchColumn();
            if ($genreName && stripos($genreName, $search) !== false) {
                $match = true;
                break;
            }
        }
      
        if (stripos($movie['title'], $search) !== false || $match) {
            $filtered[] = $movie;
        }
    }
    $movies = $filtered;
}

if ($rating) {
    $filtered = [];
    foreach ($movies as $movie) {
        $ratingStmt = $con->prepare("SELECT rating FROM reviews WHERE movie_id = ?");
        $ratingStmt->execute([$movie['id']]);
        $allRatings = $ratingStmt->fetchAll(PDO::FETCH_COLUMN);

        $avgRating = count($allRatings) > 0 ? array_sum($allRatings)/count($allRatings) : 0;
        if ($avgRating >= $rating) {
            $filtered[] = $movie;
        }
    }
    $movies = $filtered;
}

if (empty($movies)) {
    echo "<p>No movies found.</p>";
} else {
    foreach ($movies as $movie): ?>
        <article>
            <h2>
                <a href="movie.php?id=<?= $movie['id'] ?>">
                    <?= htmlspecialchars($movie['title'], ENT_QUOTES) ?>
                </a>
                <?php if (isAdmin()): ?>
                    | <a href="../public/edit_movie.php?id=<?= $movie['id'] ?>">Edit</a>
                    | <a href="../public/delete_movie.php?id=<?= $movie['id'] ?>"
                         onclick="return confirm('Are you sure you want to delete this movie?');">Delete</a>
                <?php endif; ?>
            </h2>
            <p>
                Release Year: <?= $movie['release_year'] ?? 'N/A' ?> |
                Added on: <?= date("F j, Y", strtotime($movie['created_at'])) ?>
            </p>
        </article>
        <hr>
    <?php endforeach;
}
?>