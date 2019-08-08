<?php

class database {

    const MYSQL_PORT = 3306;
    const MYSQL_CHARSET = "latin1";


    private $link;
    private $host;
    private $database;
    private $username;
    private $password;
    private $port;
    private $charset;


    // Opens a PDO connection to the database.
    public function __construct($host, $database, $username, $password, $port = self::MYSQL_PORT) {
        $this->port = $port;
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
        $this->charset = self::MYSQL_CHARSET;
        $this->open();
    }

    public function __destruct() {
        $this->close();
    }

    private function open() {
        $dsn = "mysql:host=$this->host;dbname=$this->database;port=$this->port;charset=$this->charset";
        $options = [];
        try {
            $this->link = new PDO($dsn, $this->username, $this->password, $options);
        } catch (\PDOException $ex) {
            error_log("Unable to connect to database $dsn: " . $ex->getMessage() . " " . $ex->getCode());
            return false;
        }
        return true;
    }

    // Closes database connection
    public function close() {
        $this->link = null;
    }

    // Insert with placeholders.
    // Returns the id number of the new record, 0 if it fails
    public function insert($sql, $params) {
        $this->link->prepare($sql)->execute($params);
        return $this->link->lastInsertId();
    }

    // Insert a brand new row into the table, with the first column being an integer primary key auto_incremement, thus NULL.
    // $table - string - database table to insert data into
    // $data - associative array with index being the column and value the data.
    // Returns the id number of the new record, 0 if it fails
    public function build_insert($table, $data) {
        $values_sql = "";
        $count = 0;
        foreach ($data as $key => $value) {
            $values_sql .= ($count ? ", " : "") . ":$key";
            $count++;
        }
        $sql = "INSERT INTO $table VALUES (NULL, $values_sql)";
        return $this->insert($sql, $data);
    }
    

    // For update and delete queries
    // returns true on success, false otherwise
    public function non_select_query($sql) {
        return $this->link->query($sql);
    }

    // Used for SELECT queries, optionally parameterized
    // $sql - SQL query
    // $params - associative array with the parameters matching the named placeholders in the $sql
    //      (if this is empty or not provided, parameterization is not used)
    // Returns an array of associative arrays.
    public function query($sql, $params = []) {
        if (empty($params)) {
            return $this->link->query($sql)->fetchAll();
        } else {
            return $this->link->prepare($sql)->execute($params)->fetchAll();
        }
    }
}

?>
