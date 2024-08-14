<?php

class ServiceHandler {
    
    public function __construct() {
        // Constructor
    }

    // Method to handle text: capitalize the first letter of each word
    public function capitalizeWords($text) {
        return ucwords(strtolower($text));
    }

    // Method to check if a value is a valid email address
    public function isValidEmail($email) {
        // Regular expression pattern for validating email addresses
        $pattern = '/^\S+@\S+\.\S+$/';
        
        // Check if the email matches the pattern
        return preg_match($pattern, $email) ? true : false;
    }

    function isPasswordMatch($password, $confirmedPassword) {
        // Check if the password and confirmed password are identical
        if ($password === $confirmedPassword) {
            return true; // Passwords match
        } else {
            return false; // Passwords do not match
        }
    }

    function makeStringTimestamp ($date_str) {
        $date_parts = explode("/", $date_str);
        $month = $date_parts[0];
        $day = $date_parts[1];
        $year = $date_parts[2];

        return mktime(0, 0, 0, $month, $day, $year);
        // $date_numeric = date("Ymd", $timestamp);

        // echo $date_numeric; // Output: 20240502
    }

    function timeStampToDate($timestamp){
        return date('d/m/Y H:i:s',$timestamp);
    }

    function checkTimeDifferenceInMinutes($timestamp1){
        // Example timestamps in Unix epoch format (seconds since January 1, 1970)
        $timestamp2 = time(); // Current time as Unix timestamp

        // Calculate the difference in seconds
        $secondsDifference = abs($timestamp2 - $timestamp1);

        return $secondsDifference;

    }


    function compareTextsCaseInsensitive($text1, $text2) {
        // Use strcasecmp for case-insensitive comparison
        $result = strcasecmp($text1, $text2);
        
        if($text1 === $text2) {
            return true;
        }

        return false;
    }

}
?>
