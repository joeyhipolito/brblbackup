<?php

class GameDao {
    private $increment = 0;
    
    public function getQuestion() {
        
        $file = 'logs/question_start.txt';
        file_put_contents($file, 0);
        
        $db = new Database();
        
        $file = 'logs/category.txt';
        $handle = fopen($file, 'r');
        $data = fread($handle, filesize($file));
        if($data == 'blow') {$type = 0;}
        else if ($data == 'gimme'){ $type = 1;}
        
        $unusedCat = $this->getUnusedCategories();
        
        // get a random question
        $query = 'SELECT id FROM question '
                . 'WHERE type = :type '
                . 'AND is_used = 0 '
                . 'AND category IN (' . $unusedCat .  ')';
        $bindVal = array(':type' => $type);
        $results = $db->fetchAll($query, $bindVal);

        if (!empty($results)) {
            $numbers = [];
            foreach ($results as $result) {
                foreach ($result as $value) {
                    array_push($numbers, $value);
                }
            }
            shuffle($numbers);
            $qid = $numbers[0];

            $query = 'SELECT id AS question_id, description, type, answer, category, sub_category, is_used, duration'
                    . ' FROM question '
                    . 'WHERE id = :qid AND type = :type '
                    . 'AND is_used = 0 '
                    . 'AND category IN (' . $unusedCat .  ')';
            $bindVal = array(':qid' => $qid, ':type' => $type);
            $question = $db->fetchAll($query, $bindVal);

            // remove usedCategory
            $this->removeCategory($question[0]['category']);
            // update is_used
            $query = 'UPDATE question SET is_used = 1 WHERE id = :qid';
            $params = array(':qid' => $question[0]['question_id']);

            $db->query($query, $params);

            // remaining
            $query = 'SELECT id, (SELECT COUNT(id) FROM question WHERE type = 1) AS blow_left,'
                    . '(SELECT COUNT(id) FROM question WHERE type = 0) AS gimme_left '
                    . 'FROM question';
            $count = $db->fetchAll($query);

            
            // question, choices
            $data = array('questions' => $question, 'choices' => $this->getChoices($qid));
            $file = 'logs/current_question.txt';
            $retval = file_put_contents($file, json_encode($data)) > 0 ? true : false;
            return array('written' => $retval,
                'item' => $data );
        } else {
            $data = array('questions' => null, 'choices' => null);
            return array('written' => false, 'item' => $data, 'remaining' => 0);
        }
    }
    
    public function getChoices($qid) {
        $db = new Database();
        $query = 'SELECT id AS choice_id, choice_name, question_id FROM choice WHERE question_id = :qid';
        $bindVal = array(':qid' => $qid);
        
        return $db->fetchAll($query, $bindVal);
    }
    
    public function getUnusedCategories() {
        $file = 'logs/unused_cat.txt';
        
        $unused = file($file, FILE_SKIP_EMPTY_LINES);
        
        for ($i = 0; $i < count($unused); $i++) {
            $unused[$i] = "'" . str_replace("\n", "", $unused[$i]) . "'";
        }
        
        return implode(',', $unused);
        
    }
    
    public function refreshCategories() {
        $db = new Database();
        $file = 'logs/unused_cat.txt';
        $handle = fopen($file, 'a');
        // get all distinct categories
        $query = "SELECT DISTINCT category FROM question";
        $objects = $db->fetchAll($query);
        if (filesize($file) <= 0) {
            foreach ($objects as $obj => $value) {
                foreach ($value as $category => $v) {
                    fwrite($handle, $v . "\n");
                }
            }
        }

    }
    
    public function removeCategory($category) {
        $file = 'logs/unused_cat.txt';
        $contents = str_replace($category . "\n", '', file_get_contents($file));
        file_put_contents($file, $contents, FILE_SKIP_EMPTY_LINES);
        if (filesize($file) <= 0) {
            $this->refreshCategories();
        }
    }
    
    public function getTeams() {
        $db = new Database();
        // get random team
        $nopick = $this->getNotPickedTeams();
        $query = 'SELECT id, username FROM user WHERE username IN('. $nopick .')';
        $results = $db->fetchAll($query);
        
        return $results;
        
    }
    
    public function taya($me) {
        $file = 'logs/current_team.txt';
        $team = file_get_contents($file);
        if ($me == $team) {
            return json_encode(array('taya' => true));
        } else {
            return json_encode(array('taya' => false));
        }
    }
    
    public function setTeam($team) {
        $file = 'logs/current_team.txt';
        $this->removeTeam($team);
        if (file_put_contents($file, $team) > 0) {
            return array('set' => true);
        } else {
            return array('set' => false);
        }
    }
    
    public function refreshTeams() {
        $db = new Database();
        $file = 'logs/teams.txt';
        return file_put_contents($file, file('logs/users.log.txt'));
//        $handle = fopen($file, 'a');
//        $query = "SELECT DISTINCT username FROM user";
//        $objects = $db->fetchAll($query);
//        if (filesize($file) <= 0) {
//            foreach ($objects as $obj => $value) {
//                foreach ($value as $category => $v) {
//                    fwrite($handle, $v . "\n");
//                }
//            }
//        }
    }
    
    public function getNotPickedTeams() {
        $file = 'logs/teams.txt';
        
        $unused = file($file, FILE_SKIP_EMPTY_LINES);
        
        for ($i = 0; $i < count($unused); $i++) {
            $unused[$i] = "'" . str_replace("\n", "", $unused[$i]) . "'";
        }
        
        return implode(',', $unused);
    }
    
    public function removeTeam($team) {
        $file = 'logs/teams.txt';
        $contents = str_replace($team . "\n", '', file_get_contents($file));
        file_put_contents($file, $contents, FILE_SKIP_EMPTY_LINES);
        
        if (filesize($file) <= 0) {
            $this->refreshTeams();
        }
    }
    
    public function resetPlayerList($password) {
        if ($password == 'luldaryll') {
            if ($this->refreshTeams() > 0) {
                return array('refreshed' => true);
            }
        }
    }
    
    public function gameReset($password) {
        if ($password == 'luldaryll') {
            $this->refreshTeams();
            $this->refreshCategories();

            $db = new Database();
            $conn = $db->getConnection();
            $conn->beginTransaction();
              $query = 'UPDATE question SET is_used = 0';
              $stmt = $conn->prepare($query);
              $stmt->execute();
              // next

              $query = 'UPDATE user SET login_status = 0, score = 0';
              $stmt = $conn->prepare($query);
              $stmt->execute();

              // next

    //          $query = 'DELETE FROM scoreboard';
    //          $stmt = $conn->prepare($query);
            return array( 'success' => $conn->commit() );
        }
    }
    
    public function evaluatePlayer($player ,$question, $choice) {
        $db = new Database();
        $result = $this->evaluateAnswer($question, $choice);
        $squery = "SELECT choice_name FROM choice WHERE id = :cid";
        $params = array(':cid' => $choice);
        $answer = $db->fetch($squery, $params)['choice_name'];

        $qquery = "SELECT duration FROM question WHERE id = :qid";
        $params = array(':qid' => $question);
        $this->increment = $db->fetch($qquery, $params)['duration'];
        
        $con = $db->getConnection();
        $con->beginTransaction();
            $query = "INSERT INTO scoreboard VALUES(null, :player, :answer, :remarks, :qid)";
            $stmt = $con->prepare($query);
            $stmt->execute(array(
                ':player' => $player,
                ':answer' => $answer,
                ':remarks' => $result,
                ':qid' => $question
            ));
          
            // update user score
            $query = "UPDATE user SET score = score + :inc WHERE username = :player";
            $stmt = $con->prepare($query);
            $stmt->execute(array(
               ':player' => $player,
               ':inc' => $this->increment
            ));
            
        return $con->commit();
    }
    
    public function evaluateAnswer($question, $answer) {
        $db = new Database();
        $query = "SELECT * FROM question WHERE question.id = :qid "
                . "AND answer = (SELECT c2.choice_name FROM choice AS c2 WHERE c2.id = :cid)";
//        $query = 'SELECT q1.id FROM question AS q1 JOIN choice AS c1'
//                . 'ON q1.id = c1.question_id WHERE q1.id = :id '
//                . 'AND q1.answer = (SELECT c2.choice_name FROM choice c2 WHERE'
//                . ' c2.id = :ans)';
        $params = array(':qid' => $question, ':cid' => $answer);
        if ($db->fetchAll($query, $params)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function whoSent($qid = null) {
        $db = new Database();
        if (is_null($qid)) {
            $query = 'SELECT scoreboard.id, question.id AS qid, username, question.answer,'
                    . ' remarks, description, type, category FROM scoreboard'
                    . ' LEFT JOIN question '
                    . 'ON scoreboard.question_id = question.id ';
            $results = $db->fetchAll($query);
            return json_encode($results);
        } else if($qid > 0) {
            $query = 'SELECT scoreboard.id, question.id AS qid, username, question.answer,'
                    . ' remarks, description, type, category FROM scoreboard'
                    . ' LEFT JOIN question '
                    . 'ON scoreboard.question_id = question.id '
                    . 'WHERE scoreboard.question_id = :qid';
            $params = array(':qid' => $qid);
            $results = $db->fetchAll($query, $params);
            return json_encode($results);
        }
    }
}

