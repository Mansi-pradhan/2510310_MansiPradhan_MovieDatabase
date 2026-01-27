<?php
require __DIR__ . '/../config/db.php';
$con=dbConnect();

$sql='SELECT id, title, release_year, created_at FROM movies ORDER BY created_at DESC';
$stmt=$con->prepare($sql);
$stmt->execute();
$movies=$stmt->fetchALL(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>


<h1>Movies</h1>
<?php
if (empty($movies)):
?>
<?php else:?>
	<?php foreach($movies as $movie):?>
		<article>
			<h2>
				   <a href="movie.php?id=<?php echo $movie['id']; ?>">
				   	<?php echo htmlspecialchars($movie["title"],ENT_QUOTES, 'UTF-8');?>
				   </h2>
				   <p>
				   	Release Year:
				   	<?php echo $movie['release_year']??'N/A'?>
				   	|
				   	Added on: <?php echo date("F j, Y", strtotime($movie['created_at']));?>
				   </p>
				</article>
				<hr>
				<?php endforeach; ?>
				<?php endif; ?>
</body>
</html>