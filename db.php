<?php
class Database { 
    private $hostname = "?";
    private $username = "?";
    private $password = "?";
    private $dbname = "?";

    public function getConnection(){
        return new mysqli($this->hostname, $this->username, $this->password, $this->dbname);
    }

    public function selectAllEnambled($table){
        $conn = $this->getConnection();
        $sql = "SELECT * 
        FROM $table";

        $res = $conn->query($sql);

        if($res->num_rows > 0){
            return $res->fetch_assoc();
        }
    }
}
?>