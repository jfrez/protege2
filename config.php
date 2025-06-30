<?php
session_start(); 

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "127.0.0.1";
$user = "root";
$passwd = "holahola";
$database = "protege";


$conn = new mysqli($host, $user, $passwd, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$error = '';
if(isset($_SESSION['email'])){
$user_email = $_SESSION['email'];
$userid = $_SESSION['userid'];
}
?>
