<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/session.php'; 

$con = dbConnect();

$sql = 'SELECT id, title, release_year, created_at FROM movies ORDER BY created_at DESC';
$stmt = $con->prepare($sql);
$stmt->execute();
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);


$yearsStmt = $con->query("SELECT DISTINCT release_year FROM movies ORDER BY release_year DESC");
$years = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .btn { padding: 6px 12px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
        input, select { padding: 5px; margin-right: 10px; margin-bottom: 10px; }
        article { margin-bottom: 15px; }
        hr { border: 0; border-top: 1px solid #ccc; }
    </style>
</head>
<body>

<h1>Movies</h1>

<?php if (isAdmin()): ?>
    <p><a href="../public/add_movie.php" class="btn">+ Add Movie</a></p>
<?php endif; ?>

<div>
    <input type="text" id="searchInput" placeholder="Search by title or genre">
    <select id="yearFilter">
        <option value="">All Years</option>
        <?php foreach ($years as $year): ?>
            <option value="<?= $year ?>"><?= $year ?></option>
        <?php endforeach; ?>
    </select>

    <select id="ratingFilter">
        <option value="">All Ratings</option>
        <?php for ($i=1; $i<=5; $i++): ?>
            <option value="<?= $i ?>"><?= $i ?>+</option>
        <?php endfor; ?>
    </select>
</div>

<hr>

<div id="moviesContainer">
    <?php if (empty($movies)): ?>
        <p>No movies found.</p>
    <?php else: ?>
        <?php foreach ($movies as $movie): ?>
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
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const yearFilter = document.getElementById('yearFilter');
const ratingFilter = document.getElementById('ratingFilter');
const moviesContainer = document.getElementById('moviesContainer');
async function fetchMovies() {
    const search = searchInput.value.trim();
    const year = yearFilter.value;
    const rating = ratingFilter.value;

    if (!search && !year && !rating) return;

    try {
        const response = await fetch(`search_movies.php?search=${encodeURIComponent(search)}&year=${year}&rating=${rating}`);
        if (!response.ok) throw new Error('Network response was not ok');

        const html = await response.text();
        moviesContainer.innerHTML = html;
    } catch (error) {
        moviesContainer.innerHTML = "<p>Error loading movies.</p>";
        console.error('Fetch error:', error);
    }
}
searchInput.addEventListener('input', fetchMovies);
yearFilter.addEventListener('change', fetchMovies);
ratingFilter.addEventListener('change', fetchMovies);
</script>

</body>
</html>