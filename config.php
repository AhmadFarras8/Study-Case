<?php
// config.php — database connection (default XAMPP settings)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_coffee";
$port = 3306; // change to 3307 if your MySQL runs on another port

$conn = mysqli_connect($host, $user, $pass, $db, $port);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>
