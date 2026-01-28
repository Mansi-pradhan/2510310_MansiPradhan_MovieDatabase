<?php
require __DIR__ . '/../config/db.php';

$con = dbConnect();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if ($name === '' || $email === '' || $password === '') {
        $message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {

        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $con->prepare($sql);
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $message = "Email is already registered.";
            } else {

            // Hash the password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user (role = user)
            $insertSql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $insertStmt = $con->prepare($insertSql);
            $insertStmt->execute([$name, $email, $passwordHash, 'user']);

            $message = "Registration successful! You can now <a href='login.php'>login</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>User Signup</title>
</head>
<body>

<h1>Signup</h1>

<?php if ($message): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>

<form method="post">
    <label>Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <label>Confirm Password:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <button type="submit">Signup</button>
</form>

<p>Already have an account? <a href="login.php">Login here</a></p>

</body>
</html>