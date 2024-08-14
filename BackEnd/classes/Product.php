<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/Bader_store/classes/DataBase.php';

class Product {
    private $table_name = "products";

    public function __construct() {
    }

    public function getAllProducts() {
        $db = new DataBase();
        $select_query = "SELECT * FROM $this->table_name";

        // $products1 = $db->printQuery($select_query,[]);
        $products = $db->select($select_query,[]);

        return $products;
    }
}

?>