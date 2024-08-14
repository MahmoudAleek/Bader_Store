<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Enable CORS for specific origin (replace * with your domain if needed)
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);

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


require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DataBase.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/User.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ServiceHandler.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

error_reporting(E_ALL);
ini_set('display_errors', 1);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Retrieve the Authorization header
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



    $db = new DataBase();

    // Debugging: Output raw POST data
    $data = json_decode(file_get_contents('php://input'), true);


    // Check if the JSON data was successfully decoded
    if ($data === null) {
        http_response_code(400); // Bad Request
        exit("Invalid JSON data.");
    }

    $action = $data['action'];

    if($action == 'getCountries'){
		// decode JWT
		$secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';

        try {
		    $jwt_decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
		    // print_r($jwt_decoded);
            // exit;

			// Compare the timestamp with the current time
			// if ($jwt_decoded->exp <  time()) {
            //     // Send a response
            //     $response = array(
            //         'success' => false,
            //         'message' => 'Reset password link has expired.',
            //     );
            //     echo json_encode($response);
            //     exit;
            // }


		} catch (Exception $e) {
		    echo 'Error decoding JWT: ' . $e->getMessage();
		}

        $db = new DataBase(); 
        $sql_select = "SELECT * FROM tbl_country";
        
        $params = [];
        // echo $db->printQuery($sql_insert,$params);
        // exit;
        $countriesJSON = $db->select($sql_select,$params);


        // Send a response
        $response = array(
            'data' => $countriesJSON,
            'status' => 'success',
            'message' => 'this is get cases post request ',
        );
        echo json_encode($response);
        exit;
    }

    if($action == 'getInstitutions'){
        $db = new DataBase(); 
        $sql_select = "SELECT * FROM tbl_institutions";
        
        $params = [];
        // echo $db->printQuery($sql_insert,$params);
        // exit;
        $institutionsJSON = $db->select($sql_select,$params);


        // Send a response
        $response = array(
            'data' => $institutionsJSON,
            'status' => 'success',
            'message' => 'this is get cases post request ',
        );
        echo json_encode($response);
        exit;
    }


    exit;
}




// If it's not a POST request, return an error
http_response_code(405); // Method Not Allowed
exit("Only GET requests are allowed.");
?>
