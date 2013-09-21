<?php

class PlayerController {
    
    public function index() {
        echo json_encode(array('message' => 'player controller'));
    }
    
    public function login() {
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        
        if(filter_input(INPUT_POST, 'submit')) {
            $dao = new LoginDao($username, $password);
            echo json_encode($dao->getResults());
        }
        // return format {loggedIn : true, uid : int}
    }
    
    public function getStart() {
        $file = 'logs/gamestart.txt';
        $handle = fopen($file, 'r');
        $data = fread($handle, filesize($file));
        if($data == 'started' || $data == '1') {
            echo json_encode(array('status' => 'started', 'stat_id' => 1));
        } else if ($data == 'not yet started' || $data == '0') {
            echo json_encode(array('status' => 'not yet started', 'stat_id' => 0));
        } else {
            echo json_encode(array('status' => null, 'stat_id' => -1));
        }
    }
    
    public function sendAnswer() {
        $submit = filter_input(INPUT_POST, 'submit');
        $answer = filter_input(INPUT_POST, 'answer');
        $question = filter_input(INPUT_POST, 'question');
        
        if ($submit) {
            $dao = new GameDao();
            $re = $dao->evaluatePlayer($player = 'CCSS', $question, $answer);
            echo json_encode(array('success' => $re));
        }
    }
    
    
    public function getTime() {
        $file = 'logs/timetoend.txt';
        $to_end = file_get_contents($file);
        echo json_encode(array('time' => $to_end - time()));
        
    }
    
    public function setCat() {
        $category = filter_input(INPUT_POST, 'category_id');
        $file = 'logs/category.txt';
        $handle = fopen($file, 'w') or die('cannot open file');
        $stat = 0;
        if ($category == '1') {
            $data = 'blow';
            $stat = 1;
        } else if  ($category == '2') {
            $data = 'gimme';
            $stat = 1;
        } else {
            $stat = 2;
        }
        fwrite($handle, $data);
        echo json_encode(array('category_set' => $retval = $stat > 0 ? true : false));
    }
    
    public function getTaya($me) {
        $dao = new GameDao();
        echo $dao->taya($me);
    }
    
    public function getQuestion() {
        
        // return random question based on category_id
    }
    
    public function getScore($uid) {
        // return specific user score
    }
    
}
