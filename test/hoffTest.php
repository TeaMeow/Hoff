<?php
require 'hoff.php';
require 'library/MysqliDb.php';

class HoffTest extends \PHPUnit_Framework_TestCase
{
    function __construct()
    {
        $this->db   = new MysqliDb('localhost', 'root', '', 'hoff');
        $this->hoff = new Hoff($this->db);
    }
    
    function testBuild()
    {
        $this->hoff->column('test')->create('test_table');
        
        echo var_dump($this->hoff->columns);
    }
}
?>