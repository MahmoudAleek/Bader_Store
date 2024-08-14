<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

session_start();
include($_SERVER['DOCUMENT_ROOT'].'/config/constants.php');


if(!$_SESSION['userInfo']){
    header('Location: ' . WEBSITE_ROOT);
}

switch($_SESSION['userInfo']['role']){
    case 'admin':
        header('Location: ' . WEBSITE_ROOT . ADMIN_ROOT);
    break;

    case 'miniAdmin':
        header('Location: ' . WEBSITE_ROOT . MINI_ADMIN_ROOT);
    break;

    case 'customer':
        header('Location: ' . WEBSITE_ROOT . CUSTOMER_ROOT);
    break;

    case 'agent':
        header('Location: ' . WEBSITE_ROOT . AGENT_ROOT);
    break;
}

?>

