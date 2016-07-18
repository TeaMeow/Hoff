<?php
require 'hoff.php';
require '../test/library/MysqliDb.php';

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
    }
}
?>