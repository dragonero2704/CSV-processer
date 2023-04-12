<?php
define("DBCONFIG",json_decode(file_get_contents("./configs/db.json")));

class Database { 
    //inserire credenziali database
    private $hostname = DBCONFIG['hostname'];
    private $dbname = DBCONFIG['dbname'];
    private $username = DBCONFIG['username'];
    private $password = DBCONFIG['password'];
    private $connection = null;
    public $connerror = array();
    public $error = array();

    function __construct($hostname = null, $username = null, $password = null, $dbname = null)
    {
        if(isset($hostname)) $this->hostname = $hostname;
        if(isset($username)) $this->username = $username;
        if(isset($password)) $this->password = $password;
        if(isset($dbname)) $this->dbname = $dbname;
        $this->connection = new mysqli($this->hostname, $this->username, $this->password, $this->dbname);
        if(!empty($this->connection->connect_errno)){
            $this->connerror['code'] = $this->connection->connect_errno;
            $this->connerror['message'] = $this->connection->connect_error;
        }
    }

    public function isConnected(){
        return empty($this->connerror);
    }

    function __destruct()
    {
        if(empty($this->connerror)){
            $this->connection->close();
        }
        
    }

    public function getNewConnection(){
        return new mysqli($this->hostname, $this->username, $this->password, $this->dbname);
    }

    public function getConnection(){
        return $this->connection;
    }

    public function selectAllEnabled($table, $fieldName = "enabled"){
        // $conn = $this->getConnection();
        $sql = "SELECT * 
        FROM $table
        WHERE $fieldName = 1";

        $res = $this->connection->query($sql);
        // $conn->close();

        if($res->num_rows > 0){
            return $res->fetch_assoc();
        }else{
            return false;
        }
    }
    /*
    *$data deve essere nella forma colonna=>valore
    */
    public function insertInto($table, $data){
        $this->error = array();
        /*if(!is_array($data)){
            return false;
        }*/

        if($this->recordExists($table, $data) > 0){
            return false;
        }

        $sql = "
        INSERT INTO $table (";

        $values = "VALUES (";

        foreach($data as $key => $value){
            
            $value = str_replace('€', '', $value);
            $value = addslashes($value);
            
            if(!empty($value)){
                $sql = $sql.$key.', ';
                if(is_numeric($value)){
                    $values = $values.$value.', ';
                }else{
                    $values = $values.'"'.$value.'", ';
                }
            }
        }

        if($values === "VALUES ("){
            echo "Valore nullo";
            return false;
        }
        //rimozione dell'ultima virgola
        $sql = substr($sql, 0, strlen($sql)-2);
        $values = substr($values, 0, strlen($values)-2);

        $sql = $sql.') '.$values.')';
        
        echo "query insertInto: $sql";
        
        $ris = $this->connection->query($sql);
        if(!empty($this->connection->errno)){
            $this->error['code'] = $this->connection->errno;
            $this->error['message'] = $this->connection->error;
            return $ris;
        }
        return $ris;

    }

    public function recordExists($table, $data){
        $this->error = array();
        $sql = "
        SELECT *
        FROM $table 
        WHERE ";

        $conditions = array();

        foreach($data as $key => $value){
            $value = addslashes($value);
            //echo "<p class='seeme'>$value</p>";
            if(!empty($value)){
                if(is_numeric($value)){
                    array_push($conditions, "$key = $value");
                }else{
                    array_push($conditions, "$key = '$value'");
                }
            }
            
        }
        echo "<br><br>";
        //var_dump($conditions);
        echo "<br><br>";

        $conditions = join(" AND ", $conditions);
        $sql = $sql.$conditions;
        echo "<p class='seeme'>recordExists query: $sql</p>";
        $ris = $this->connection->query($sql);
        var_dump($ris);
        if(!empty($this->connection->errno)){
            $this->error['code'] = $this->connection->errno;
            $this->error['message'] = $this->connection->error;
            return $ris;
        }

        return $ris->num_rows;
    }

    public function query($query){
        $this->error = array();
        try {
            //code...
            $ris = $this->connection->query($query);
        } catch (\Throwable $th) {
            //throw $th;
            echo $th;
        }
        
        if(!empty($this->connection->errno)){
            $this->error['code'] = $this->connection->errno;
            $this->error['message'] = $this->connection->error;
            return $ris;
        }
        return $ris;
    }
}
?>