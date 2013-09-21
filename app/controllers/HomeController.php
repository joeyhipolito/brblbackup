<?php

class HomeController {
    public function __construct() {
        
    }
    
    public function index() {
        echo json_encode(array('message' => 'home controller'));
    }
}
