<?php
class Database {
    // Config BD PwC
    private $pwc_server = "tcp:10.0.1.34,1433";
    private $db_pwc = "Campus";
    private $username_pwc = "N30t3lR3f3r3nc1@";
    private $pass_pwc = "password";
    
    // Config BD NOM2001
    private $nom_server = "servidor2";
    private $db_nom = "bd_horarios2";
    private $username_nom = "usuario";
    private $pass_nom = "password";
    
      
    public function connPwC() {
        $connectionInfo = array(
            "Database" => $this->db_pwc,
            "UID" => $this->username_pwc,
            "PWD" => $this->pass_pwc,
            "CharacterSet" => "UTF-8",
            "LoginTimeout" => 300, 
            "ConnectRetryCount" => 5, 
            "MultipleActiveResultSets" => 1

        );
        
        $conn = sqlsrv_connect($this->pwc_server, $connectionInfo);
        
        if (!$conn) {
            return null;
        }
        return $conn;
    }
    
    public function connNOM01() {
        $connectionInfo = array(
            "Database" => $this->db_nom,
            "UID" => $this->username_nom,
            "PWD" => $this->pass_nom,
            "CharacterSet" => "UTF-8",
            "LoginTimeout" => 300, 
            "ConnectRetryCount" => 5, 
            "MultipleActiveResultSets" => 1
        );
        
        $conn = sqlsrv_connect($this->nom_server, $connectionInfo);
        
        if (!$conn) {
            return null;
        }
        return $conn;
    }
    
}
?>