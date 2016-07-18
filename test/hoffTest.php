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
    
    function testBasic()
    {
        $this->hoff->column('test')->varchar(32)->primary()
                   ->create('test_table');
                   
        $this->assertEquals('CREATE TABLE test_table (test varchar(32) NOT NULL PRIMARY KEY) ENGINE=INNODB ', $this->hoff->lastQuery);
    }
    
    function testDataTypes()
    {
        $this->hoff->column('test')->tinyint(1)
                   ->column('test2')->smallint(1)
                   ->column('test3')->mediumint(1)
                   ->column('test4')->int(1)
                   ->column('test5')->bigint(1)
                   ->column('test6')->char(1)
                   ->column('test7')->varchar(1)
                   ->column('test8')->binary(1)
                   ->column('test9')->varbinary(1)
                   ->column('test10')->bit(1)
                   ->column('test11')->tinytext()
                   ->column('test12')->text()
                   ->column('test13')->mediumtext()
                   ->column('test14')->longtext()
                   ->column('test15')->tinyblob()
                   ->column('test16')->blob()
                   ->column('test17')->mediumblob()
                   ->column('test18')->longblob()
                   ->column('test19')->date()
                   ->column('test20')->datetime()
                   ->column('test21')->time()
                   ->column('test22')->timestamp()
                   ->column('test23')->year()
                   ->column('test24')->double([2, 1])
                   ->column('test25')->decimal([2, 1])
                   ->column('test26')->float([2, 1])
                   ->column('test27')->float([1])
                   ->column('test28')->enum([1, 2, 3, 'A', 'B', 'C'])
                   ->column('test29')->set([1, 2, 3, 'A', 'B', 'C'])
                   ->create('test_table1');
                   
        //$this->assertEquals('CREATE TABLE test_table (test varchar(32) NOT NULL PRIMARY KEY) ENGINE=INNODB ', $this->hoff->lastQuery);
    }
            
    function testTableType()
    {
        $this->hoff->column('test')->varchar(32);
        $this->hoff->MyISAM()->create('test_myisam_table');
        
        $this->assertEquals('CREATE TABLE test_myisam_table (test varchar(32) NOT NULL) ENGINE=MyISAM ', $this->hoff->lastQuery);
        
        $this->hoff->column('test')->varchar(32)
                   ->InnoDB()->create('test_innodb_table');
        
        $this->assertEquals('CREATE TABLE test_innodb_table (test varchar(32) NOT NULL) ENGINE=InnoDB ', $this->hoff->lastQuery);
    }
    
    function testPrimaryKey()
    {
        $this->hoff->column('test')->varchar(32)->primary()
                   ->column('test2')->varchar(32)
                   ->create('test_table2');
                   
        $this->assertEquals('CREATE TABLE test_table2 (test varchar(32) NOT NULL PRIMARY KEY , test2 varchar(32) NOT NULL) ENGINE=INNODB ', $this->hoff->lastQuery);
    }
    
    function testNamingPrimaryKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->primary('pk_test', ['test', 'test2'])
                   ->create('test_table3');
                   
        $this->assertEquals('CREATE TABLE test_table3 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL, PRIMARY KEY `pk_test` (`test`,`test2`)) ENGINE=INNODB ', $this->hoff->lastQuery);
    }
    
    function testMultiPrimaryKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->primary(['test', 'test2'])
                   ->create('test_table4');
                   
        $this->assertEquals('', $this->hoff->lastQuery);
    }
    
    function testUniqueKey()
    {
        $this->hoff->column('test')->varchar(32)->unique()
                   ->column('test2')->varchar(32)
                   ->create('test_table5');
                   
        $this->assertEquals('CREATE TABLE test_table5 (test varchar(32) NOT NULL UNIQUE , test2 varchar(32) NOT NULL) ENGINE=INNODB ', $this->hoff->lastQuery);
    }
    
    function testNamingUniqueKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->unique('uk_test', ['test', 'test2'])
                   ->unique('uk_test2', ['test3', 'test4'])
                   ->create('test_table6');
                   
        $this->assertEquals('CREATE TABLE test_table6 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL , test3 varchar(32) NOT NULL , test4 varchar(32) NOT NULL, UNIQUE KEY `uk_test` (`test`,`test2`), UNIQUE KEY `uk_test2` (`test3`,`test4`)) ENGINE=INNODB ', $this->hoff->lastQuery);
    }
    
    function testMultiUniqueKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->unique(['test', 'test2'])
                   ->unique(['test3', 'test4'])
                   ->create('test_table7');
                   
        $this->assertEquals('', $this->hoff->lastQuery);
    }
    
    function testIndexKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->index(['test'])->create('test_table8');
                   
        $this->assertEquals('', $this->hoff->lastQuery);
    }
    
    function testNamingIndexKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->index('ik_test', ['test', 'test2'])
                   ->index('ik_test2', ['test3', 'test4'])
                   ->create('test_table9');
                   
        $this->assertEquals('CREATE TABLE test_table9 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL , test3 varchar(32) NOT NULL , test4 varchar(32) NOT NULL, INDEX `ik_test` (`test`,`test2`), INDEX `ik_test2` (`test3`,`test4`)) ENGINE=INNODB ', $this->hoff->lastQuery);
    }
    
    function testMultiIndexKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->index(['test', 'test2'])
                   ->index(['test3', 'test4'])
                   ->create('test_table10');
        
        $this->assertEquals('', $this->hoff->lastQuery);
    }
}
?>