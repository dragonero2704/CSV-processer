<?php
class Database { 
    //inserire credenziali database
    private $hostname = "192.168.80.110:3306";
    private $dbname = "crmbrand039_db_test";
    private $username = "crmbrand039test";
    private $password = "5v!IbzkgBp6Su6";
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
        $this->connection->close();
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
        if(!is_array($data)){
            return false;
        }

        if($this->recordExists($table, $data)){
            return false;
        }

        $sql = "
        INSERT INTO $table (";

        $values = "VALUES (";

        foreach($data as $key => $value){
            $sql = $sql.$key.', ';
            $value = str_replace($value, '€', '');
            if(is_int($value) || is_float($value)){
                $values = $values.$value.', ';
            }else{
                $values = $values.'"'.$value.'",';
            }
            
        }
        //rimozione dell'ultima virgola
        $sql = substr($sql, 0, strlen($sql)-1);
        $values = substr($values, 0, strlen($values)-1);

        $sql = $sql.')\n'.$values.')';

        return $this->connection->query($sql);

    }

    public function recordExists($table, $data){
        $sql = "
        SELECT *
        FROM $table 
        WHERE ";

        $conditions = array();

        foreach($data as $key => $value){
            $conditions = array_push($conditions, "$key = $value");
        }

        $conditions = join(" AND ", $conditions);
        $sql = $sql.$conditions;

        return $this->connection->query($sql);
    }

    public function query($query){
        $this->error = array();
        $ris = $this->connection->query($query);
        if(!empty($this->connection->errno)){
            $this->connection->errno = $this->error['code'];
            $this->connection->error = $this->error['message'];
            return $ris;
        }
        return $ris;
    }
}
?>