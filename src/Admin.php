<?php
/* 
* Class Admin 
* Handles adding and deletion of programs for admin users
*/
require 'Hackathon.php';

class Admin extends Hackathon {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function addProgram() {
        // get data
        $this->getdata();
        
        //validate data
        if (!isset($this->data['cnp']) || empty($this->data['cnp'])) {
            exit('Please provide CNP');
        }
        if (!isset($this->data['type']) || empty($this->data['type'])) {
            exit('Please provide program type');
        } elseif(!$this->checkType()){
            exit('Invalid program type');
        } 
        if (!isset($this->data['start_date']) || empty($this->data['start_date'])) {
            exit('Please provide a valid start date');
        } elseif (!$this->isTimestamp($this->data['start_date'])) {
            exit('Please provide a valid timestamp for start date');
        }
        if (!isset($this->data['end_date']) || empty($this->data['end_date'])) {
            exit('Please provide a valid end date');
        } elseif (!$this->isTimestamp($this->data['end_date'])) {
            exit('Please provide a valid timestamp for end date');
        } elseif ($this->data['start_date'] >= $this->data['end_date']) {
            exit('Start date cannot be after end date');
        }
        if (!isset($this->data['max_user']) || empty($this->data['max_user'])) {
            exit('Please provide a maximum user limit');
        }
        
        // check if user exists and has permission
        if ($this->userExists() && $this->isAdmin()) {
            // insert user to database
            $statement = 'INSERT INTO programs (type_id, max_user, start_date, end_date) VALUES (:type_id, :max_user, FROM_UNIXTIME(:start_date), FROM_UNIXTIME(:end_date));';

            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(array(
                    'type_id' => $this->data['type'],
                    'max_user' => $this->data['max_user'],
                    'start_date' => $this->data['start_date'],
                    'end_date' => $this->data['end_date']
                ));
                echo 'Succesfully added program';
            } catch (\PDOException $e) {
                exit($e->getMessage());
            } 
        }
    }
    
    private function isTimestamp($timestamp) {
        if (!ctype_digit($timestamp)) return false;
        $x = strlen($timestamp) >= 13 ? $timestamp / 1000 : $timestamp;
        if ($timestamp < strtotime('-30 years') || $timestamp > strtotime('+30 years')) {   
            return false;
        }
        return true;
    }
    
    private function checkType() {
        $statement = 'SELECT COUNT(*) FROM program_type WHERE id = ?;';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($this->data['type']));
            $result = $statement->fetchColumn();
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    
    private function isAdmin() {
        if ($this->getUserRole() == 1) {
            return TRUE;
        }
        return FALSE;
    }
}
