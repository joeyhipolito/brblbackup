<?php

class GmController {
    
    public function start() {
        
        // clear list of logged in users
        $file = 'logs/users.log.txt';
        $handle = fopen($file, 'w') or die('cannot open file');
        fclose($handle);
        
        if($submit = filter_input(INPUT_POST, 'submit')) {
            
            $file = 'logs/gamestart.txt';
            $handle = fopen($file, 'w') or die('cannot open file');
            $stat = 0;
            if ($start == 'true') {
                $data = 'started';
                $stat = 1;
            } else if  ($start == 'false') {
                $data = 'not yet started';
                $stat = 0;
            }
            fwrite($handle, $data);
            echo json_encode(array('start' => $retval = $stat > 0 ? 1 : 0));
        }
    }
    
    public function getLogged() {
        $file = 'logs/users.log.txt';
        $lines = array_map('trim', file($file));
        echo json_encode($lines);
    }
    
    public function getQuestion() {
        $dao = new GameDao();
        echo json_encode($dao->getQuestion());
    }

    public function sendQuestion() {
        // get time per question
        $file = 'logs/current_question.txt';
        $handle = fopen($file, 'r');
        $data = fread($handle, filesize($file));
        $dataArray = [];
        foreach (json_decode($data) as $object) {
          foreach ($object as $obj) {
              foreach ($obj as $key => $value) {
                  $dataArray[$key] = $value;
              }
           } 
        }
        $duration = $dataArray['duration'];
        $file2 = 'logs/timetoend.txt';
        file_put_contents($file2, time() + $duration);
        
        // start the round
        $file = 'logs/question_start.txt';
        file_put_contents($file, 1);
        
        $end_time = file_get_contents($file2);
        $round_start = file_get_contents($file);
        

        echo json_encode(array(
            'round_start' => intval($round_start),
            'end_time' => $end_time,
            'time_now' => time()
        ));
    }
    
    public function getCategory() {
        $file = 'logs/category.txt';
        $handle = fopen($file, 'r');
        $data = fread($handle, filesize($file));
        if($data == 'blow' || $data == '1') {
            echo json_encode(array('category' => 'mind blowing', 'cat_id' => 1));
        } else if ($data == 'gimme' || $data == '2') {
            echo json_encode(array('category' => 'earth shaking', 'cat_id' => 2));
        } else {
            echo json_encode(array('category' => null, 'cat_id' => -1));
        }
    }
    
    public function reset() {
        $password = filter_input(INPUT_POST, 'password');
        if ($password) {
            $dao = new GameDao();
            echo json_encode($dao->gameReset($password));
        }
    }
    
    public function refresh() {
        $password = filter_input(INPUT_POST, 'password');
        if ($password) {
            $dao = new GameDao();
            echo json_encode($dao->resetPlayerList($password));
        }
    }
    
    public function getTeam() {
        $dao = new GameDao();
        echo json_encode($dao->getTeams());
    }
    
    public function setTeam() {
        $team = filter_input(INPUT_POST, 'team');
        $dao = new GameDao();
        echo json_encode($dao->setTeam($team));
    }
    
    public function getScores() {
        // get from the scoreboard based on question id
    }
    
    public function whoSent($qid = null) {
        // receives question id, fetch answer and evaluate
        // return who sent and remarks
        $dao = new GameDao();
        if (is_null($qid)) {
            echo $dao->whoSent();
        } else {
            echo $dao->whoSent($qid);
        }
    }
    
    public function remaining() {
        // return number of remaining questions
    }
    
}

