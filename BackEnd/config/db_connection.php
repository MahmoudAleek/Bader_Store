<?php

require_once 'Database.php';


session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);


// Create a new instance of the Database class
$db = new Database();

// $servername = "localhost";
// $dbname = "ICP_EDU_DB";
// $username = "admin";
// $password = "zoolai7sie3Alei7p";

// $link = mysql_connect($servername, $username, $password);
// if (!$link) {
//     die('Could not connect: ' . mysql_error());
// }
// echo 'Connected successfully';
// mysql_close($link);

// return;
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully";

// Now you can execute queries using this $conn object
?>
