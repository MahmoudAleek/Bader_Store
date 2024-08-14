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

// Your PHP API logic goes here
// For example, you can echo JSON response
// echo json_encode(['message' => 'This is a secure API endpoint ' . $_SERVER['HTTP_ORIGIN']]);



error_reporting(E_ALL);
ini_set('display_errors', 1);


// Include the JWT library
require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DataBase.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ServiceHandler.php';

use \Firebase\JWT\JWT;
use GUMP;

// Debugging: Check if the library is loaded correctly
if (!class_exists('Firebase\JWT\JWT')) {
    exit('Error: JWT class not found.');
}



function validateData($jsonData) {
    $data = $jsonData;

    $gump = new GUMP();

    $rules = [
        'cust_name' => 'required',
        'cust_email' => 'required|valid_email',
        'cust_password' => 'required',
        'cust_conf_password' => 'required',
        'ornisation_name' => 'required',
        'address_line_1' => 'required|max_len,3000',
        'address_line_2' => 'required|max_len,3000',
        'post_code' => 'alpha_numeric',
        'country' => 'required',
        'phone_number' => 'alpha_numeric',
        'vat_number' => 'required',
        'uic_number' => 'required'
    ];

    $gump->validation_rules($rules);

    $validated = $gump->run($data);

    if($validated === false) {
        return $gump->get_readable_errors();
    } else {
        return true;
    }
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debugging: Output raw POST data
    $data = json_decode(file_get_contents('php://input'), true);
    // error_log('Raw POST data: ' . $rawData);

    // Check if the JSON data was successfully decoded
    if ($data === null) {
        http_response_code(400); // Bad Request
        exit("Invalid JSON data.");
    }


    // Access the username and password
    $customerName = $data['cust_name'];
    $customerEmail = $data['cust_email'];
    $password = $data['cust_password'];
    $confPassword = $data['cust_conf_password'];
    $orgnisationName = $data['ornisation_name'];
    $addressLine1 = $data['address_line_1'];
    $addressLine2 = $data['address_line_2'];
    $postCode = $data['post_code'] == '' ? $data['post_code'] : NULL;
    $country = $data['country'];
    $phoneNumber = $data['phone_number'];
    $VATNumber = $data['vat_number'];
    $UICNumber = $data['uic_number'];



    // TEST MODE VALUES 
    // $customerName = "John Doe";
    // $customerEmail = "john.doe@example.com6";
    // $password = "P@ssw0rd123";
    // $confPassword = "P@ssw0rd123";
    // $orgnisationName = "Doe Industries";
    // $addressLine1 = "1234 Elm Street";
    // $addressLine2 = "Suite 567";
    // $postCode = "12345";
    // $country = "3";
    // $phoneNumber = "785454545";
    // $VATNumber = "323265";
    // $UICNumber = "98765";



    // print_r($data);
    // exit;

    if($customerName && $password && filter_var($customerEmail, FILTER_VALIDATE_EMAIL)){
        $role = 'customer';

        
        // Validate Register Form Information 
        $sh = new ServiceHandler();
        if(!$sh->isPasswordMatch($password,$confPassword)){
            echo "this pass not match ";
            return;
        }


        // $isDataValidated = validateData($data);

        // if($isDataValidated){
        //     echo "validate";
        //     exit;
        // }else{
        //     echo "not validate";
        //     exit;
        // }

        // if(!$isDataValidated){
        //     print_r($isDataValidated)
        // }

        $db = new DataBase();
        $sql_insert_user = "INSERT INTO tbl_users (`user_id`, `name`, `email`, `password`, `role`) VALUES (NULL, ?, ?, ?, ?)";
        $user_id = $db->execute($sql_insert_user,[$customerName, $customerEmail, md5($password),$role]);
        

        if($user_id){
            $sql_insert_customer_info = "INSERT INTO `tbl_customers` ( `user_id`, `organization`, `address_line_1`,
             `address_line_2`, `post_code`, `country`, `phone_number`, `vat_number`, `uic_number`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);";

            $params = [$user_id,$orgnisationName,$addressLine1,$addressLine2,$postCode,$country,$phoneNumber,$VATNumber,$UICNumber];
            
            $result = $db->execute($sql_insert_customer_info,$params);

        }

        // $db->execute();
        // Execute the statement
        if ($user_id) {
            $secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';

            // Payload (claims)
            $payload = array(
                "user_id" => $user_id,
                "email" => $customerEmail,
                "role" => $role,
                // "exp" => time() + 3600,
            );

                        
            // Generate JWT token
            try {
                $jwt = JWT::encode($payload, $secret_key, 'HS256');
                session_start();
                $_SESSION['userInfo'] = $payload;
            } catch (\Exception $e) {
                echo 'Error encoding JWT: ' . $e->getMessage();
            }

            // Send a response
            $response = array(
                // 'user' => ['userId'=>  $user_id , 'username'=> $fullName ],
                'status' => 'success',
                'message' => 'your account created successfully, token generated',
                'token' => $jwt,
            );

            echo json_encode($response);
            exit;
        }

        // Close statement and connection

        exit;


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
