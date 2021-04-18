<?php
require 'Hackathon.php';

/* 
* Class Client 
* Handles bookings for clients
*
*/
class Client extends Hackathon {
    
    public function __construct() {
        parent::__construct();
    }
    
    /*
    * Adds a user booking in database if all conditions pass
    */
    public function addBooking() {
        // get data
        $this->getdata();
        
        // validate data
        if (!isset($this->data['cnp']) || empty($this->data['cnp'])) {
            exit('Please provide a CNP');
        } elseif(!$this->userExists()) {
            exit('No user found with provided CNP');
        } elseif(!$this->isClient()) {
            exit('Incorrect user role');
        }
        if (!isset($this->data['program']) || empty($this->data['program'])) {
            exit('Please provide a program');
        } elseif (!$this->programExists($this->data['program'])) {
            exit('Program does not exist');
        } else {
            // check if program is not due
            $program = $this->getProgram($this->data['program']);
            if (strtotime($program->start_date) <= time()) {
                exit('Program is due');
            }
            // check if date range is available, seats not taken and not already booked
            if (!$this->isBooked() && $this->isAvailable($program) && $this->hasSeats($program)) {
                // insert booking to database
                $statement = 'INSERT INTO booking (user_id, program_id) VALUES (:user_id, :program_id);';
                try {
                    $statement = $this->db->prepare($statement);
                    $statement->execute(array(
                        'user_id' => $this->getUser($this->data['cnp']),
                        'program_id' => $this->data['program']
                    ));
                    echo 'Succesfully added booking';
                } catch (\PDOException $e) {
                    exit($e->getMessage());
                } 
            }
        }
    }
    
    /*
    * Checks if user is client
    */
    private function isClient() {
        if ($this->getUserRole() == 2) {
            return TRUE;
        }
        return FALSE;
    }
    
    /*
    * Check if user can book to program
    */
    private function isAvailable($program) {
        
        $user_id = $this->getUser($this->data['cnp']);
        
        $statement = 'SELECT program_id FROM booking WHERE user_id = ?;';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($user_id));
            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
            // check if other bookings have same date
            if (!empty($results)) {
                foreach ($results as $result) {
                    if ($result['program_id'] == $program->id) {
                        // if same program, do nothing
                        continue;
                    }
                    if (!$this->programExists($result['program_id'])) {
                        // check if program was deleted
                        continue;
                    }
                    // load other programs
                    $result = $this->getProgram($result['program_id']);
                    // validate date
                    if (!$this->checkDate($program->start_date, $program->end_date, $result->start_date, $result->end_date)) {
                        exit('You are already booked for this period');
                    }
                }
                return TRUE;
            }
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        return FALSE;
    }
    
    
    /*
    * Checks if a program has seats available
    */
    private function hasSeats($program) {
        // count all bookings from program
        $statement = 'SELECT COUNT(*) FROM booking WHERE program_id = ?;';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($program->id));
            $result = $statement->fetchColumn();
            if ($result >= $program->max_user) {
                exit('Program is fully booked');
            }
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        
        return TRUE;
    }
    
    /*
    * Checks if user is already booked to program
    */
    private function isBooked() {
        // count all bookings from program
        $statement = 'SELECT COUNT(*) FROM booking WHERE program_id = :program_id AND user_id = :user_id;';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'user_id' => $this->getUser($this->data['cnp']),
                'program_id' => $this->data['program']
            ));
            $result = $statement->fetchColumn();
            if ($result) {
                exit('You are already booked');
            }
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        
        return FALSE;
    }
    
    /*
    * Helper function to compare if date intervals overlap
    */
    private function checkDate($start_date, $end_date, $start_date_2, $end_date_2) {
        // Convert to timestamp
        $date['start'] = strtotime($start_date);
        $date['end'] = strtotime($end_date);
        $date2['start'] = strtotime($start_date_2);
        $date2['end'] = strtotime($end_date_2);
        
        // check if intervals overlap
        if(($date['start'] <= $date2['end']) && ($date['end'] >= $date2['start'])) {
            return FALSE;
        }
        
        return TRUE;
    }
}