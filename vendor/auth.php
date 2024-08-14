<?php

// echo  "Asdasd";
require_once  'autoload.php';

use Firebase\JWT\JWT;


// Generate a random binary string (bin32 token) to use as the secret key
function generate_bin32_token() {
    // Number of bytes for the binary string
    $bytes = 16; // 16 bytes = 128 bits

    // Generate random bytes using random_bytes()
    $random_bytes = random_bytes($bytes);

    // Encode the random bytes using base64 encoding
    $base64_encoded = base64_encode($random_bytes);

    // Return the first 32 characters (128 bits) as a binary string
    return substr($base64_encoded, 0, 32);
}

// Your secret key, it's recommended to store it in a secure location
$secret_key = generate_bin32_token();

// Simulated username and password (replace this with your actual authentication logic)
$valid_username = "user";
$valid_password = "password";

// Retrieve username and password from the request (assuming it's a POST request)
$username = $_POST['username'];
$password = $_POST['password'];


echo "<br> token : ".generate_bin32_token();
echo "<br> username : $username";
echo "<br> password : $password";

// Check if the provided username and password are valid
if ($username === $valid_username && $password === $valid_password) {
    // User is authenticated, generate JWT token

    // User information
    $user_id = 123;
    $user_email = "user@example.com";
    $user_roles = ["admin", "editor"];

    // Payload (claims)
    $payload = array(
        "user_id" => $user_id,
        "email" => $user_email,
        "roles" => $user_roles
    );

    // Generate JWT token
    try {
        $jwt = JWT::encode($payload, $secret_key);
    } catch (\Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
    
    // var_dump($jwt);
    // Return the JWT token
    echo "JWT Token: " . $jwt;
} else {
    // Authentication failed
    http_response_code(401); // Unauthorized
    echo "Invalid username or password";
}

 

?>