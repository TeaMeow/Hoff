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
        $this->hoff->column('test')->varchar(32)->primary();
        
        echo var_dump($this->hoff->columns);
        
        $this->hoff->create('test_table');
    }
    
    function testTableType()
    {
        $this->hoff->column('test')->varchar(32);
        $this->hoff->MyISAM()->create('test_myisam_table');
        
        $this->hoff->column('test')->varchar(32)
                   ->InnoDB()->create('test_innodb_table');
    }
    
    function testPrimaryKey()
    {
        $this->hoff->column('test')->varchar(32)->primary()
                   ->column('test2')->varchar(32)
                   ->create('test_table2');
    }
    
    function testNamingPrimaryKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->primary('pk_test', ['test', 'test2'])
                   ->create('test_table3');
    }
    
    function testMultiPrimaryKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->primary(['test', 'test2'])
                   ->create('test_table3');
    }
    
    function testUniqueKey()
    {
        $this->hoff->column('test')->varchar(32)->unique()
                   ->column('test2')->varchar(32)
                   ->create('test_table4');
    }
    
    function testNamingUniqueKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->unique('uk_test', ['test', 'test2'])
                   ->unique('uk_test2', ['test3', 'test4'])
                   ->create('test_table5');
    }
    
    function testMultiUniqueKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->unique(['test', 'test2'])
                   ->unique(['test3', 'test4'])
                   ->create('test_table5');
    }
    
    function testIndexKey()
    {
        $this->hoff->column('test')->varchar(32)->index()
                   ->column('test2')->varchar(32)
                   ->create('test_table6');
    }
    
    function testNamingIndexKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->index('ik_test', ['test', 'test2'])
                   ->index('ik_test2', ['test3', 'test4'])
                   ->create('test_table7');
    }
    
    function testMultiIndexKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->index(['test', 'test2'])
                   ->index(['test3', 'test4'])
                   ->create('test_table7');
    }
}
?>