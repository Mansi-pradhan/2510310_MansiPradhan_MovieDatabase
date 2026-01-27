<?php
function dbConnect(){
	$server="mysql:host=localhost;dbname=movie_db;charset=utf8mb4";
	$user='root';
	$password='';

	try{
		  $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
		$con =new PDO($server, $user, $password, $options);
		return $con;

	}catch (PDOExecption $e){
		die("Database connection Error:" . $e->getMessage());
	}
}
?>