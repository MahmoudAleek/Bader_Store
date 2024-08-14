<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'].'/classes/RouterChecker.php';

// Get the current user's role from the session
$userRole = isset($_SESSION['userInfo']['role']) ? $_SESSION['userInfo']['role'] : null;


// echo "--------------------------------------------------------------------------------------".$_SESSION['userInfo']['role'];
// return;
// exit;

// Check if user is logged in and has a valid role
if (!$userRole) {
    // Redirect the user to the login page or display an access denied message
    header("Location: /");
    exit;
}

// Check if the user has access to the current directory
$path = $_SERVER['PHP_SELF'];
$pathParts = explode('/', $path);
$currentDirectory = $pathParts[1]; // The directory immediately after .com/

if (!RouteChecker::canAccessDirectory($userRole, $currentDirectory)) {
    // Redirect the user to an access denied page or display an access denied message
    header("Location: /");
    exit;
}





?>