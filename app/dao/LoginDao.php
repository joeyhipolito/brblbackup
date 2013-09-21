<?php

class LoginDao {
    
    private $results;
    
    public function __construct($user, $pass) {
        $query = 'SELECT id FROM user WHERE username = :u AND password = :p';
        $bindVal = array(':u'=> $user, ':p' => $pass);
        $db = new Database();
        $uid = $db->fetch($query, $bindVal);
        if ($db->num_rows($query, $bindVal) > 0) {
            $this->setSession($uid['id'], $user);
            $this->setLogged();
            $this->results = array('loggedIn' => true, 'uid' => intval($uid['id']));
        }
    }
    
    
    private function setSession($uid, $user) {
        $_SESSION['user'] = $user;
        $_SESSION['uid'] = $uid;
    }
    
    private function setLogged() {
        $file = 'logs/users.log.txt';
        if(strpos(file_get_contents($file), $_SESSION['user']) === false) {
            $handle = fopen($file, 'a') or die('cannot open file');
            $data = $_SESSION['user'] . "\n";
            fwrite($handle, $data);
        }
        
    }
    
    public function getResults() {
        return $this->results;
    }
}
