<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DataBase.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/constants.php';


use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class User {
    private $id;
    private $username;
    private $email;
    private $password;
    private $role;
    private $isAccountActive;

    // Constructor
    public function __construct($idOrToken,$isToken = false) {
        if($isToken){
            // decode JWT
            $secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';
            $userToken = JWT::decode($idOrToken, new Key($secret_key, 'HS256'));

            $userData = $this->getUserDataFromDatabase($userToken->user_id);

            // Assign user data to class properties
            $this->id = $userData['user_id'];
            $this->email = $userData['email'];
            $this->name = $userData['name'];
            $this->password = $userData['password'];
            $this->role = $userData['role'];
            $this->isAccountActive = $userData['is_active'];
        }else{

            // Retrieve user data from the database based on the provided ID
            // Replace this with your actual database retrieval logic
            $userData = $this->getUserDataFromDatabase($idOrToken);

            // Assign user data to class properties
            $this->id = $userData['user_id'];
            $this->email = $userData['email'];
            $this->name = $userData['name'];
            $this->password = $userData['password'];
            $this->role = $userData['role'];
            $this->isAccountActive = $userData['is_active'];
        }


    }


    // Method to retrieve user data from the database
    private function getUserDataFromDatabase($id) {
        $db = new DataBase();

        // Check if the provided ID is numeric
        if (!is_numeric($id)) {
            throw new InvalidArgumentException('User ID must be a number.');
        }


        $select_query = "SELECT id, name, email, password, role, is_active FROM users WHERE user_id = ? LIMIT 1";
        $user = $db->select($select_query,[$id]);

        return $user[0];
    }

    private function getUserInfoFromToken($token) {
       
    }



    // Getter methods
    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRole() {
        return $this->role;
    }

    public function getIsAccountActive() {
        return $this->isAccountActive;
    }


    // // Setter methods (if needed)
    // public function setUsername($username) {
    //     $this->username = $username;
    // }

    // public function setEmail($email) {
    //     $this->email = $email;
    // }

    // public function setRole($role) {
    //     $this->role = $role;
    // }

    // Method to check if user has a specific role
    public function hasRole($targetRole) {
        return $this->role === $targetRole;
    }

    // Method to edit user data
    public function updateUserInfo($id, $name, $email, $password, $role) {
        $this->username = $username;
        $this->email = $email;
    }


     // Method to generate a reset password token link with expiration date using JWT
     public function generateResetPasswordToken() {
        $secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';

        // Payload (claims)
        $payload = array(
            "user_id" => $this->id,
            "email" => $this->email,
            "role" => $this->role,
            "exp" => time() + 1800, // 30 min 
        );

        // Generate JWT token
        try {
            $jwt = JWT::encode($payload, $secret_key, 'HS256');
            
            return $this->saveResetPasswordGeneratedToken($jwt) ? $jwt : false;
            
        } catch (\Exception $e) {
            echo 'Error encoding JWT: ' . $e->getMessage();
        }
    }


    private function saveResetPasswordGeneratedToken($token){
        $db = new DataBase();

        $sql_insert = "INSERT INTO tbl_reset_password_tokens (user_id,email,token,date_insert) VALUES(?,?,?,?)";
        return $db->execute($sql_insert,[$this->id,$this->email,$token,time()]);
    }


    public function SendResetPasswordMail($to, $subject, $message) {
        try {
            $mail = new PHPMailer(true);
            // echo "PHPMailer loaded successfully!";
        } catch (Exception $e) {
            throw new Exception("PHPMailer could not be loaded. Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }        

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'bader_store@gmail.com';                // SMTP username
            $mail->Password   = 'password';                             // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to
    
            //Recipients
            $mail->setFrom('bader_store@gmail.com', 'Mailer');
            $mail->addAddress($to);                                     // Add a recipient
    
            // Content
            $mail->isHTML(true);                                        // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);
    
            $mail->send();
            return true;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }

}

?>
