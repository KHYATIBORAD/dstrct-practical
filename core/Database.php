<?php
class Database
{
    private $host = 'localhost';
    private $user = 'root';
    private $pass = 'root';
    private $dbname = 'dstrct';

    protected $connection;

    public function __construct()
    {
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }
}
