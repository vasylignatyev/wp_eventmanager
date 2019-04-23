<?php

class Database{

    static private $pdo = null;
    
    private function __construct() {
        exit('Init function is not allowed');
    }
      
    /**
     * database descriptor
     *
     * @return PDO
     */
    static protected function pdo() {
        if (!is_object(self::$pdo)) {
            //self::$pdo = new PDO("mysql:host=localhost;dbname=eventman","u_eventman;charset=utf8",'Sedwo162',array(
            self::$pdo = new PDO("mysql:host=localhost;dbname=eventman","u_eventman",'Sedwo162',array(
                PDO::ATTR_PERSISTENT => true
            ));
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->exec("set names utf8");
        }
        return self::$pdo;
    }
}
?>