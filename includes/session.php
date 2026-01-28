<?php
session_start();

function isLoggeedIn(){
	return isset($_SESSION['user_id']);
}

function isAdmin(){
	return isset($_SESSION['role'])&& $_SESSION['role'] === 'admin';
}

function requireLogin(){
	if(!isLoggeedIn()){
		header("Location: ../public/login.php");
		exit;
	}
}
function requireAdmin(){
	requireLogin();
	if(!isAdmin()){
		die("Access denied");
	}
}
?>