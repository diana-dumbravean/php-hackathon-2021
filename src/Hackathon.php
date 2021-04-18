<?php

/* 
* Class Hackathon
* Main Class 
* Connects to DB and handles user registrations
*/
class Hackathon {
    
    public function __construct() {
        $this->getConnection();
    }
    
    /*
    * Get database connection
    */
    public function getConnection() {
        define('HOST', 'localhost');
        define('PORT', '3306');
        define('DB', 'hackathon');
        define('USER', 'root');
        define('PASS', '');
        try {
            $this->db = new \PDO(
                'mysql:host=' . HOST . ';port=' . PORT . ';charset=utf8mb4;dbname=' .  DB,
                USER,
                PASS
            );
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    
    /*
    * Register new user in database if all conditions pass
    */
    public function registerUser() {
        // get data
        $this->getdata();
        
        // validate data
        if (!isset($this->data['cnp']) || empty($this->data['cnp'])) {
            exit('Please provide a CNP');
        } elseif(!$this->validateCNP($this->data['cnp'])) {
            exit('CNP is not valid');
        } 
        if (!isset($this->data['role']) || empty($this->data['role'])) {
            exit('Please provide a valid role');
        } elseif (!in_array($this->data['role'], [1, 2])) {
            exit('Role can be admin(1) or client(2)');
        }
        if (!isset($this->data['name']) || empty($this->data['name'])) {
            exit('Please provide a name');
        }
        
        // check if user exists
        if ($this->userExists()) {
            exit('User already exists');
        }
        
        // insert user to database
        $statement = 'INSERT INTO user (cnp, role, name) VALUES (:cnp, :role, :name);';

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'cnp' => $this->data['cnp'],
                'role' => $this->data['role'],
                'name' => $this->data['name']
            ));
            echo 'Succesfully added user';
        } catch (\PDOException $e) {
            exit($e->getMessage());
        } 
    }
    
    public function getData() {
        $this->data = (array) json_decode(file_get_contents('php://input'), TRUE);
    }
    
    /*
    * Validate romnian CNP
    */
    private function validateCNP($cnp) {
        // CNP must have 13 characters
        if(strlen($cnp) != 13) {
            return false;
        }
        $cnp = str_split($cnp);
        $hashTable = array( 2 , 7 , 9 , 1 , 4 , 6 , 3 , 5 , 8 , 2 , 7 , 9 );
        $hashResult = 0;
        // All characters must be numeric
        for($i=0 ; $i<13 ; $i++) {
            if(!is_numeric($cnp[$i])) {
                return false;
            }
            $cnp[$i] = (int)$cnp[$i];
            if($i < 12) {
                $hashResult += (int)$cnp[$i] * (int)$hashTable[$i];
            }
        }
        unset($hashTable, $i);
        $hashResult = $hashResult % 11;
        if($hashResult == 10) {
            $hashResult = 1;
        }
        // Check Year
        $year = ($cnp[1] * 10) + $cnp[2];
        switch( $cnp[0] ) {
            case 1  : case 2 : { $year += 1900; } break; // cetateni romani nascuti intre 1 ian 1900 si 31 dec 1999
            case 3  : case 4 : { $year += 1800; } break; // cetateni romani nascuti intre 1 ian 1800 si 31 dec 1899
            case 5  : case 6 : { $year += 2000; } break; // cetateni romani nascuti intre 1 ian 2000 si 31 dec 2099
            case 7  : case 8 : case 9 : {                // rezidenti si Cetateni Straini
                $year += 2000;
                if($year > (int)date('Y')-14) {
                    $year -= 100;
                }
            } break;
            default : {
                return false;
            } break;
        }
        return ($year > 1800 && $year < 2099 && $cnp[12] == $hashResult);
    }
    
    /*
    * Checks if user already exists
    */
    public function userExists() {
        $statement = 'SELECT COUNT(*) FROM user WHERE CNP = ?;';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($this->data['cnp']));
            $result = $statement->fetchColumn();
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    
    /*
    * Get user via CNP
    */
    public function getUser() {
        $statement = 'SELECT id FROM user WHERE CNP = ?;';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($this->data['cnp']));
            $result = $statement->fetchColumn();
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    
    /*
    * Get program
    */
    public function getProgram($id) {
        $statement = 'SELECT * FROM programs WHERE id = ?;';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetchObject();
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    
    /*
    * Checks if room exists
    */
    public function roomExists() {
        $statement = 'SELECT COUNT(*) FROM rooms WHERE id = ?;';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($this->data['room']));
            $result = $statement->fetchColumn();
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    
    /*
    * Checks if program exists
    */
    public function programExists($id) {
        $statement = 'SELECT COUNT(*) FROM programs WHERE id = ?;';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetchColumn();
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    
    /*
    * Get user role ,Admin (1) , Client (2)  via CNP
    */
    public function getUserRole() {
        $statement = 'SELECT role FROM user WHERE CNP = ?;';
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($this->data['cnp']));
            $result = $statement->fetchColumn();
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}

?>