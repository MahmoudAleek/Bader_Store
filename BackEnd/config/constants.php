<?php
    session_start();

define('WEBSITE_ROOT','https://cp.educhecks.com/');

define('ADMIN_ROOT','admin/');
define('CUSTOMER_ROOT','customer/');



define('SECRET_KEY','In3Sg/jhwLg2BsRzQ961/A==');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/Bader_store');


if(isset($_SESSION['userInfo']) && $_SESSION['userInfo']){
    switch($_SESSION['userInfo']['role']){
        case 'admin': define('USER_ROOT','https://cp.educhecks.com/admin');
            break;

        case 'customer': define('USER_ROOT','https://cp.educhecks.com/customer'); 
            break;
    }
}



?>