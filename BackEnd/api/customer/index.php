<?php

// Set the content type to JSON
header('Content-Type: application/json');

// Enable CORS for specific origin (replace * with your domain if needed)
// header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);

// Prevent MIME-sniffing
header('X-Content-Type-Options: nosniff');

// Enable XSS protection
header('X-XSS-Protection: 1; mode=block');

// Prevent content from being framed
header('X-Frame-Options: DENY');

// Enforce HTTPS
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Set the referrer policy
header('Referrer-Policy: no-referrer');

// Set the feature policy
header("Feature-Policy: camera 'self'; microphone 'none'");

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');

// // // Your PHP API logic goes here
// // // For example, you can echo JSON response
// // echo json_encode(['message' => 'This is a secure API endpoint ' . $_SERVER['HTTP_ORIGIN']]);


error_reporting(E_ALL);
ini_set('display_errors', 1);


// Include the JWT library
require_once $_SERVER['DOCUMENT_ROOT'].'/Bader_store/config/constants.php';
// require_once $_SERVER['DOCUMENT_ROOT'].'/Bader_store/vendor/autoload.php';
// require_once $_SERVER['DOCUMENT_ROOT'].'/Bader_store/classes/DataBase.php';
// require_once $_SERVER['DOCUMENT_ROOT'].'/Bader_store/classes/ServiceHandler.php';
// require_once $_SERVER['DOCUMENT_ROOT'].'/Bader_store/classes/customer/Customer.php';
// require_once $_SERVER['DOCUMENT_ROOT'].'/Bader_store/classes/User.php';
require_once BASE_PATH .'/classes/Product.php';


use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // // Retrieve the Authorization header
    $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    // Extract the token from the Authorization header
    if (preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches)) {
        $token = $matches[1]; // Extract the token
        // Now you have the token, you can use it as needed
        // echo "Token received: $token";
    } else {
        // No token found in the Authorization header
        header('Location: ' . WEBSITE_ROOT);
        exit;
    }




    // Debugging: Output raw POST data
    $data = json_decode(file_get_contents('php://input'), true);


    // Check if the JSON data was successfully decoded
    if ($data === null) {
        http_response_code(400); // Bad Request
        exit("Invalid JSON data.");
    }

    $action = $data['action'];

    if($action == 'updateUserData'){
        // decode JWT
        // $secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';
        // $userInfo = JWT::decode($token, new Key($secret_key, 'HS256'));
        $userInfo = new User($token,true);

        $db = new DataBase(); 
        $sql_select = "SELECT *, FROM_UNIXTIME(dob, '%Y-%m-%d') AS dob_date FROM tbl_cases WHERE customer_id = ? ";
        
        $params = [$userInfo->getId()];

        // echo $db->printQuery($sql_insert,$params);
        // exit;
        $casesJSON = $db->select($sql_select,$params);
        $db = new DataBase(); 

        foreach($casesJSON as  $caseKey => $caseValue){
            $sql_select_case_status = "SELECT * FROM tbl_case_status WHERE id = ? LIMIT 1 ";
            $case_status = $db->select($sql_select_case_status,[$caseValue['status_id']]);
            // var_dump( $case_status);

            $casesJSON[$caseKey]['status'] = $case_status[0]['status'];
        }


        // Send a response
        $response = array(
            'data' => $casesJSON,
            'status' => 'success',
            'message' => 'this is get cases post request ',
        );
        echo json_encode($response);
        exit;
    }

    exit;
}



if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // $db = new Database();
    echo BASE_PATH;
    exit;
    // Get the action from the query string
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    if($action == 'getProducts'){
        $product = new Product();

        echo json_encode(['data' => $product->getAllProducts(), 'action' => $action]);
        exit;
    }

    exit;
}




// If it's not a POST request, return an error
http_response_code(405); // Method Not Allowed
exit("Only POST requests are allowed.");
?>
