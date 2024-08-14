<?php
class RouteChecker {
    // Define user roles
    const ROLE_ADMIN = 'admin';
    const ROLE_MINI_ADMIN = 'miniAdmin';
    const ROLE_CUSTOMER = 'customer';
    const ROLE_AGENT = 'agent';
    
    
    // Define directories accessible by each role
    private static $directories = [
        self::ROLE_ADMIN => ['admin'],
        self::ROLE_MINI_ADMIN => ['miniAdmin'],
        self::ROLE_CUSTOMER => ['customer'],
        self::ROLE_AGENT => ['agent'],
    ];

    // Function to check if a user with a certain role can access a specific directory
    public static function canAccessDirectory($role, $directory) {
        if (isset(self::$directories[$role]) && in_array($directory, self::$directories[$role])) {
            return true;
        }
        return false;
    }
}

?>
