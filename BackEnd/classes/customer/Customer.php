<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/constants.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DataBase.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/University.php';


use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Customer {
    private $id;
    private $username;
    private $email;
    private $password;
    private $role;

    // Constructor
    public function __construct($idOrToken,$isToken = false) {
        if($isToken){
            // decode JWT
            $secret_key = 'In3Sg/jhwLg2BsRzQ961/A==';
            $userToken = JWT::decode($idOrToken, new Key($secret_key, 'HS256'));

            $userData = $this->getUserDataFromDatabase((int)$userToken->user_id);
            // Assign user data to class properties
            $this->id = $userData['user_id'];
            $this->email = $userData['email'];
            $this->name = $userData['name'];
            $this->password = $userData['password'];
            $this->role = $userData['role'];
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
        }


    }


    // Method to retrieve user data from the database
    private function getUserDataFromDatabase($id) {
        $db = new DataBase();

        // Check if the provided ID is numeric
        if (!is_numeric($id)) {
            throw new InvalidArgumentException('User ID must be a number.');
        }


        $select_query = "SELECT user_id, name, email, password,role FROM tbl_users WHERE user_id = ? LIMIT 1";
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
            echo "PHPMailer loaded successfully!";
        } catch (Exception $e) {
            echo "PHPMailer could not be loaded. Error: {$e->getMessage()}";
        }

        try {
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'support@intelimasters.com';            // SMTP username
            $mail->Password   = 'FQ@#62oiP0(Wc#df.W~B@V@Q7';            // SMTP password
            $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('support@intelimasters.com', 'Mailer');
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

    private function checkIfHaveWallet(){
        $sql_select = "SELECT * FROM tbl_wallet WHERE user_id = ?";
        $params = [$this->id];

        $db = new DataBase();
        $walletInfo = $db->select($sql_select,$params);

        if($walletInfo != NULL && count($walletInfo) > 0){
            return true;
        }else{
            return false;
        }

    }

    public function addToWallet($amount){
        // Check if the amount is numeric
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Amount must be a number.');
        }

        // Convert the amount to a float (or integer if preferred)
        $amount = floatval($amount);

        $columns = ['user_id','balance','created_at','updated_at'];
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        $db = new DataBase();
        if($this->checkIfHaveWallet()){
            $sql_insert = "UPDATE tbl_wallet SET balance = balance + ? , Updated_at = ? WHERE user_id = ?";
            return $db->execute($sql_insert,[$amount,time(),$this->id]);

        }else{
            $sql_insert = "INSERT INTO tbl_wallet (" . implode(', ', $columns) . ") VALUES ($placeholders)";
            return $db->execute($sql_insert,[$this->id,$amount,time(),time()]);
        }

    }

    public function getAllTransaction(){
        
        \Stripe\Stripe::setApiKey('sk_test_51PAZfJB8nb4dgmp46322gpYmo1c5TQ3w9M922B81aLpjMdkQVTDlPbknZJN4iEhgAs9sXprqdf4Ga16BUBI0QY1Q00uxWMniUO');

        $customers = \Stripe\Customer::all(['email' => $this->email]);

        if (count($customers->data) > 0) {
            $customer = $customers->data[0]; // Assuming the email is unique and taking the first result
            $customerId = $customer->id;

            // Fetch transactions for the customer
            $charges = \Stripe\Charge::all(['customer' => $customerId]);

            return  $charges->data;
        } else {
            throw new Error('No customer found with email ' . $email);
        }


    }

    public function getWalletBalance(){
        $db = new DataBase();
        $custWallet = $db->select("SELECT * FROM tbl_wallet WHERE user_id = ? LIMIT 1",[$this->id]);

        if(empty($custWallet)){
            return 0;
        }

        return $custWallet[0]['balance'];
    }

    public function isCustomerBalanceSufficient($unversityId){
        $uni = new University($unversityId);
        $custBalance = $this->getWalletBalance();

        return $uni->getPrice() <= $custBalance;
    }

    public function deductCasePriceFromBalance($instit_id,$case_id){
        $uni = new University($instit_id);
        $db = new Database();

        if ($uni->getPrice() <= 0) {
            throw new Error('Unable to find university');
        }

        // If customer doesn't has sufficient funds update case to is not paid and status to pending payment
        if(!$this->isCustomerBalanceSufficient($instit_id)){
            // status 5 : pending payment
            $update_query = "UPDATE tbl_wallet SET is_paid = 0, status = 5  WHERE case_id = ?";
            $walletResult = $db->execute($update_query, [$case_id]);

            return true;
        }
    
        // Database connection
        $conn = $db->conn; // Direct access to the connection
    
        // Start transaction
        $conn->begin_transaction();
    
        try {
            // STEP 1 : Deduct from customer wallet
            $update_query = "UPDATE tbl_wallet SET balance = balance - ? WHERE user_id = (SELECT customer_id FROM tbl_cases WHERE case_id = ?)";
            $walletResult = $db->execute($update_query, [$uni->getPrice(), $case_id]);
            // echo $db->printQuery($update_query, [$uni->getPrice(), $case_id]);
            // return;
            if ($walletResult <= 0) {
                throw new Exception("Unable to update wallet balance or insufficient balance.");
            }
    
            // STEP 2 : Update case to paid case
            $update_query = "UPDATE tbl_cases SET is_paid = 1 WHERE case_id = ?";
            $caseResult = $db->execute($update_query, [$case_id]);
    
            if ($caseResult <= 0) {
                throw new Exception("Unable to update case status.");
            }
    
            // Commit transaction
            $conn->commit();

            return true;
        } catch (Exception $e) {
            // Rollback transaction in case of error
            $conn->rollback();
            return true;
        } finally {
            // Close connection
            // $conn->close();
        }
    }

    public function getCaseInfo($case_id){
        $db= new DataBase();
        $select_query_Case = "SELECT * FROM tbl_cases WHERE case_id = ? AND customer_id = ?";

        $caseInfo = $db->select($select_query_Case,[$case_id,$this->getId()]);
        $caseInfo = $caseInfo[0];
        return $caseInfo;
        if($caseInfo > 0){
            $newCaseInfo = [
                ['First name', 'Joan Powell', '<i class="fa fa-check text-success" aria-label="fa fa-check"></i>', '$450,870'],
                ['Last name', 'Joan Powell', '<i class="fa fa-check text-success" aria-label="fa fa-check"></i>', '$450,870'],
                ['Date of birth name', 'Joan Powell', '<i class="fa fa-check text-success" aria-label="fa fa-check"></i>', '$450,870'],
                ['Awarded year', 'Joan Powell', '<i class="fa fa-check text-success" aria-label="fa fa-check"></i>', '$450,870'],
                ['Enrollment start date', 'Joan Powell', '<i class="fa fa-check text-success" aria-label="fa fa-check"></i>', '$450,870'],
                ['Enrollment end date', 'Joan Powell', '<i class="fa fa-check text-success" aria-label="fa fa-check"></i>', '$450,870'],
                ['Major field of study', 'Joan Powell', '<i class="fa fa-check text-success" aria-label="fa fa-check"></i>', '$450,870'],
                ['Qualification', 'Joan Powell', '<i class="fa fa-check text-success" aria-label="fa fa-check"></i>', '$450,870'],
            ];
        }
        

    }

}

?>
