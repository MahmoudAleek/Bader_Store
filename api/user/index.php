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
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


error_reporting(E_ALL);
ini_set('display_errors', 1);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new DataBase();

    // Debugging: Output raw POST data
    $data = json_decode(file_get_contents('php://input'), true);


    // Check if the JSON data was successfully decoded
    if ($data === null) {
        http_response_code(400); // Bad Request
        exit("Invalid JSON data.");
    }

    $action = $data['action'];

    if($action == 'forgotPassword'){

        // STEP 1 : check if email is valid or correct 
        $sh = new ServiceHandler();
        if(!$sh->isValidEmail($data['email'])){
            // Send a response
            $response = array(
                'isvalid' => $sh->isValidEmail($data['email']),
                'email' => $data['email'],
                'success' => false,
                'message' => 'Email not valid/exist in out emails list',
            );
            echo json_encode($response);
            exit;
        } 


        $sql_select1 = "SELECT * FROM tbl_reset_password_tokens WHERE email = ? ORDER BY date_insert DESC LIMIT 1 ";
        $userInfo = $db->select($sql_select1,[$data['email']]);


        // refuse send a new link if user already request reset link in less than 5 min
        $timeDifference = $sh->checkTimeDifferenceInMinutes($userInfo[0]['date_insert']);
        if($timeDifference < 300){
            // Send a response
            $response = array(
                'isvalid' => $sh->isValidEmail($data['email']),
                'email' => $data['email'],
                'success' => false,
                'errorType' => 'cooldown',
                'serverTimestamp' => time(),
                'cooldownPeriod' => 300 - $timeDifference, 
                'message' => 'Cooldown peroid not finished yet',
            );
            echo json_encode($response);
            exit;
        } 
        
        $sql_select2 = "SELECT * FROM tbl_users WHERE email = ? LIMIT 1 ";
        $userInfo = $db->select($sql_select2,[$data['email']]);


        // STEP 2 : check if the email is exist in our db 
        if(count($userInfo) < 1){
            // Send a response
            $response = array(
                'isvalid' => $sh->isValidEmail($data['email']),
                'email' => $data['email'],
                'success' => false,
                'message' => 'Email not valid/exist in out emails list',
            );
            echo json_encode($response);
            exit;
        } 



        // STEP 3 : create reset token & save it in DB
        $user = new User($userInfo[0]['user_id']);
        $resetPasswordToken = $user->generateResetPasswordToken();


        $resetPasswordLink = 'https://cp.educhecks.com/resetPassword.php?token='.$resetPasswordToken;
        // Send reset password link in gmail
        $to = "mahmoudaleek99@gmail.com";
        $subject = "Test Email";
        $message = "<h3>Use this link in order to reset your password</h3> 
                        <a href='$resetPasswordLink' class='btn btn-outline-primary'> Reset Password </a>";
        $headers = "From: sender@example.com";




        if($user->SendResetPasswordMail($to, $subject, $message, $headers)) {
            // Send a response
            $response = array(
                'resetLink' => 'https://cp.educhecks.com/resetPassword.php?token='.$resetPasswordToken,
                'email' => $data['email'],
                'success' => true,
                'message' => 'send reset link API',
            );
            // echo 'cp.educhecks.com/resetPasswordPage.php?token='.$resetPasswordToken;
            echo json_encode($response);
            exit;
        } else {
            echo "Failed to send email.";
            exit;
        }
    }

    if($action == 'resetPassword'){
        $token = $data['token'] ? $data['token'] : null;

        if(!$token){
            // Send a response
            $response = array(
                'success' => false,
                'message' => 'Invalid token',
            );
            echo json_encode($response);
            exit;
        }
        
		$db = new DataBase();
		$sql_select = "SELECT * FROM tbl_reset_password_tokens WHERE token = ? ORDER BY id DESC LIMIT 1 ";
		$db->select($sql_select,[$token]);


     
		// decode JWT
		$secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';

		try {
		    $jwt_decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
		    // print_r($jwt_decoded);

			// Compare the timestamp with the current time
			if ($jwt_decoded->exp <  time()) {
                // Send a response
                $response = array(
                    'success' => false,
                    'message' => 'Reset password link has expired.',
                );
                echo json_encode($response);
                exit;
            }

            $sh = new ServiceHandler();
            $isPasswordMatch = $sh->isPasswordMatch($data['cust-password'],$data['conf-cust-password']);
            
            if(!$isPasswordMatch){
                // Send a response
                $response = array(
                    'success' => false,
                    'message' => 'password and confirmed password does not match.',
                );
                echo json_encode($response);
                exit;
            }

            $validPassword = md5($data['cust-password']);

            $sql_update = "UPDATE tbl_users SET password = ? WHERE user_id = ? ";
            $db->execute($sql_update,[$validPassword,$jwt_decoded->user_id]);



		} catch (Exception $e) {
		    echo 'Error decoding JWT: ' . $e->getMessage();
		}


        // Send a response
        $response = array(
            'success' => true,
            'message' => 'Password updated successfully.',
        );
        echo json_encode($response);
        exit;
    }
    
    if($action == 'getCountries'){
        $token = $data['token'] ? $data['token'] : null;

        if(!$token){
            // Send a response
            $response = array(
                'success' => false,
                'message' => 'Invalid token',
            );
            echo json_encode($response);
            exit;
        }
        
		$db = new DataBase();
		$sql_select = "SELECT * FROM tbl_country";
		$db->select($sql_select,[$token]);


     
		// decode JWT
		$secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';

		try {
		    $jwt_decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
		    // print_r($jwt_decoded);

			// Compare the timestamp with the current time
			if ($jwt_decoded->exp <  time()) {
                // Send a response
                $response = array(
                    'success' => false,
                    'message' => 'Reset password link has expired.',
                );
                echo json_encode($response);
                exit;
            }

            $sh = new ServiceHandler();
            $isPasswordMatch = $sh->isPasswordMatch($data['cust-password'],$data['conf-cust-password']);
            
            if(!$isPasswordMatch){
                // Send a response
                $response = array(
                    'success' => false,
                    'message' => 'password and confirmed password does not match.',
                );
                echo json_encode($response);
                exit;
            }

            $validPassword = md5($data['cust-password']);

            $sql_update = "UPDATE tbl_users SET password = ? WHERE id = ? ";
            $db->execute($sql_update,[$validPassword,$jwt_decoded->user_id]);



		} catch (Exception $e) {
		    echo 'Error decoding JWT: ' . $e->getMessage();
		}


        // Send a response
        $response = array(
            'success' => true,
            'message' => 'Password updated successfully.',
        );
        echo json_encode($response);
        exit;
    }

    if($action == 'getInstitutions'){
		$db = new DataBase();
		$sql_select = "SELECT * FROM tbl_country";
		$db->select($sql_select,[$token]);
     
		// decode JWT
		$secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';

		try {
		    $jwt_decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
		    // print_r($jwt_decoded);

			// Compare the timestamp with the current time
			if ($jwt_decoded->exp <  time()) {
                // Send a response
                $response = array(
                    'success' => false,
                    'message' => 'Reset password link has expired.',
                );
                echo json_encode($response);
                exit;
            }


		} catch (Exception $e) {
		    echo 'Error decoding JWT: ' . $e->getMessage();
		}


        // Send a response
        $response = array(
            'success' => true,
            'message' => 'Password updated successfully.',
        );
        echo json_encode($response);
        exit;
    }

    exit;
}




// If it's not a POST request, return an error
http_response_code(405); // Method Not Allowed
exit("Only POST requests are allowed.");
?>
