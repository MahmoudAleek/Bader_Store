<?php
// Set the headers to allow cross-origin requests
// header("Access-Control-Allow-Origin: http://localhost:5173");
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");



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

// // Your PHP API logic goes here
// // For example, you can echo JSON response
// echo json_encode(['message' => 'This is a secure API endpoint ' . $_SERVER['HTTP_ORIGIN']]);



error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if it's a preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Respond with a 200 OK status
    http_response_code(200);
    exit();
}

// Include the JWT library
require_once $_SERVER['DOCUMENT_ROOT'].'/Bader_store/config/constants.php';
require_once BASE_PATH.'/vendor/autoload.php';
require_once BASE_PATH.'/classes/DataBase.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Debugging: Check if the library is loaded correctly
if (!class_exists('Firebase\JWT\JWT')) {
    exit('Error: JWT class not found.');
}



// decode JWT
// try {
//     $jwt_decoded = JWT::decode($jwt_token, new Key($secret_key, 'HS256'));
//     print_r($jwt_decoded);
// } catch (Exception $e) {
//     echo 'Error decoding JWT: ' . $e->getMessage();
// }


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debugging: Output raw POST data
    $data = json_decode(file_get_contents('php://input'), true);
    // error_log('Raw POST data: ' . $rawData);

    // Check if the JSON data was successfully decoded
    if ($data === null) {
        http_response_code(400); // Bad Request
        exit("Invalid JSON data.". $data);
    }


    // Access the username and password
    $username = $data['email'];
    $password = $data['password'];

    // echo md5('password123');
    // exit;
    if($username && $password && filter_var($username, FILTER_VALIDATE_EMAIL)){
        $db = new DataBase();

        $select_query = "SELECT id,name, email, role FROM users WHERE email = ? AND password = ?  LIMIT 1";
        $user = $db->select($select_query,[$username,md5($password)]);


        if(!$user){
            // Send a response
            $response = array(
                'success' => false,
                'message' => 'Incorrect username/password.',
            );

            echo json_encode($response);
            exit();
        }

        // checking the credentials 
        if(count($user) > 0){
            $secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';

            // User information
            $fullName = $user[0]['name'];
            $user_id = $user[0]['id'];
            $user_email = $user[0]['email'];
            $user_role = $user[0]['role'];

            // Payload (claims)
            $payload = array(
                "user_id" => $user_id,
                "email" => $user_email,
                "role" => $user_role,
                // "exp" => time() + 3600,
            );

            // Generate JWT token
            try {
                $jwt = JWT::encode($payload, $secret_key, 'HS256');
                // session_start();
                $_SESSION['userInfo'] = $payload;
            } catch (\Exception $e) {
                echo 'Error encoding JWT: ' . $e->getMessage();
            }

            
            // Send a response
            $response = array(
                'user' => ['userId'=>  $user_id , 'username'=> $fullName ],
                'status' => 'success',
                'message' => 'logged in successfully, token generated',
                'token' => $jwt,
            );

            echo json_encode($response);
            exit();
        }
    }

    // Send a response
    $response = array(
        'status' => 'faild',
        'message' => 'username or password is wrong',
    );
    echo json_encode($response);
    exit();
}




// If it's not a POST request, return an error
http_response_code(405); // Method Not Allowed
exit("Only POST requests are allowed.");
?>
