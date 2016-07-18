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
                   
        $this->assertEquals('CREATE TABLE test_table (test varchar(32) NOT NULL PRIMARY KEY) ENGINE=INNODB', $this->hoff->lastQuery);
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
                   ->column('test28')->enum(['1', '2', '3', 'A', 'B', 'C'])
                   ->column('test29')->set(['1', '2', '3', 'A', 'B', 'C'])
                   ->create('test_table1');
                   
        $this->assertEquals("CREATE TABLE test_table1 (test tinyint(1) NOT NULL , test2 smallint(1) NOT NULL , test3 mediumint(1) NOT NULL , test4 int(1) NOT NULL , test5 bigint(1) NOT NULL , test6 char(1) NOT NULL , test7 varchar(1) NOT NULL , test8 binary(1) NOT NULL , test9 varbinary(1) NOT NULL , test10 bit(1) NOT NULL , test11 tinytext NOT NULL , test12 text NOT NULL , test13 mediumtext NOT NULL , test14 longtext NOT NULL , test15 tinyblob NOT NULL , test16 blob NOT NULL , test17 mediumblob NOT NULL , test18 longblob NOT NULL , test19 date NOT NULL , test20 datetime NOT NULL , test21 time NOT NULL , test22 timestamp NOT NULL , test23 year NOT NULL , test24 double(2, 1) NOT NULL , test25 decimal(2, 1) NOT NULL , test26 float(2, 1) NOT NULL , test27 float(1) NOT NULL , test28 enum('1', '2', '3', 'A', 'B', 'C') NOT NULL , test29 set('1', '2', '3', 'A', 'B', 'C') NOT NULL) ENGINE=INNODB", $this->hoff->lastQuery);
    }
            
    function testTableType()
    {
        $this->hoff->column('test')->varchar(32);
        $this->hoff->MyISAM()->create('test_myisam_table');
        
        $this->assertEquals('CREATE TABLE test_myisam_table (test varchar(32) NOT NULL) ENGINE=MyISAM', $this->hoff->lastQuery);
        
        $this->hoff->column('test')->varchar(32)
                   ->InnoDB()->create('test_innodb_table');
        
        $this->assertEquals('CREATE TABLE test_innodb_table (test varchar(32) NOT NULL) ENGINE=InnoDB', $this->hoff->lastQuery);
    }
    
    function testDefault()
    {
        $this->hoff->column('test')->varchar(32)->nullable()->default(null)
                   ->create('test_default_null_table');
                   
        $this->assertEquals('CREATE TABLE test_default_null_table (test varchar(32) DEFAULT NULL) ENGINE=INNODB', $this->hoff->lastQuery);
        
        $this->hoff->column('test')->varchar(32)->default('string')
                   ->create('test_default_string_table');
                   
        $this->assertEquals("CREATE TABLE test_default_string_table (test varchar(32) NOT NULL DEFAULT 'string') ENGINE=INNODB", $this->hoff->lastQuery);
        
        $this->hoff->column('test')->varchar(32)->default(12)
                   ->create('test_default_int_table');
                   
        $this->assertEquals("CREATE TABLE test_default_int_table (test varchar(32) NOT NULL DEFAULT 12) ENGINE=INNODB", $this->hoff->lastQuery);
    }
    
    function testNullable()
    {
        $this->hoff->column('test')->varchar(32)->nullable()
                   ->create('test_nullable_table');
                   
        $this->assertEquals('CREATE TABLE test_nullable_table (test varchar(32) DEFAULT NULL) ENGINE=INNODB', $this->hoff->lastQuery);
    }
    
    function testUnsigned()
    {
        $this->hoff->column('test')->int(10)->unsigned()
                   ->create('test_unsigned_table');
                   
        $this->assertEquals('CREATE TABLE test_unsigned_table (test int(10) UNSIGNED NOT NULL) ENGINE=INNODB', $this->hoff->lastQuery);
    }
    
    function testAutoIncrement()
    {
        $this->hoff->column('test')->int(10)->autoIncrement()
                   ->create('test_auto_increment_table');
                   
        $this->assertEquals('CREATE TABLE test_auto_increment_table (test int(10) NOT NULL AUTO_INCREMENT) ENGINE=INNODB', $this->hoff->lastQuery);
    }
    
    function testComment()
    {
        $this->hoff->column('test')->int(10)->comment('月月，搭拉安！')
                   ->create('test_column_comment_table');
                   
        $this->assertEquals("CREATE TABLE test_column_comment_table (test int(10) NOT NULL COMMENT '月月，搭拉安！') ENGINE=INNODB", $this->hoff->lastQuery);
    }
    
    function testTableComment()
    {
        $this->hoff->column('test')->int(10)
                   ->create('test_comment_table', '月月，搭拉安！');
                   
        $this->assertEquals("CREATE TABLE test_comment_table (test int(10) NOT NULL) ENGINE=INNODB, COMMENT='月月，搭拉安！'", $this->hoff->lastQuery);
    }
    
    function testPrimaryKey()
    {
        $this->hoff->column('test')->varchar(32)->primary()
                   ->column('test2')->varchar(32)
                   ->create('test_table2');
                   
        $this->assertEquals('CREATE TABLE test_table2 (test varchar(32) NOT NULL PRIMARY KEY , test2 varchar(32) NOT NULL) ENGINE=INNODB', $this->hoff->lastQuery);
    }
    
    function testNamingPrimaryKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->primary('pk_test', ['test', 'test2'])
                   ->create('test_table3');
                   
        $this->assertEquals('CREATE TABLE test_table3 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL, PRIMARY KEY `pk_test` (`test`,`test2`)) ENGINE=INNODB', $this->hoff->lastQuery);
    }
    
    function testMultiPrimaryKey()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->primary(['test', 'test2'])
                   ->create('test_table4');
                   
        $this->assertEquals('CREATE TABLE test_table4 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL, PRIMARY KEY (`test`,`test2`)) ENGINE=INNODB', $this->hoff->lastQuery);
    }
    
    function testUniqueKey()
    {
        $this->hoff->column('test')->varchar(32)->unique()
                   ->column('test2')->varchar(32)
                   ->create('test_table5');
                   
        $this->assertEquals('CREATE TABLE test_table5 (test varchar(32) NOT NULL UNIQUE , test2 varchar(32) NOT NULL) ENGINE=INNODB', $this->hoff->lastQuery);
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
                   
        $this->assertEquals('CREATE TABLE test_table6 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL , test3 varchar(32) NOT NULL , test4 varchar(32) NOT NULL, UNIQUE KEY `uk_test` (`test`,`test2`), UNIQUE KEY `uk_test2` (`test3`,`test4`)) ENGINE=INNODB', $this->hoff->lastQuery);
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
                   
        $this->assertEquals('CREATE TABLE test_table7 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL , test3 varchar(32) NOT NULL , test4 varchar(32) NOT NULL, UNIQUE KEY (`test`,`test2`), UNIQUE KEY (`test3`,`test4`)) ENGINE=INNODB', $this->hoff->lastQuery);
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
                   
        $this->assertEquals('CREATE TABLE test_table9 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL , test3 varchar(32) NOT NULL , test4 varchar(32) NOT NULL, INDEX `ik_test` (`test`,`test2`), INDEX `ik_test2` (`test3`,`test4`)) ENGINE=INNODB', $this->hoff->lastQuery);
    }
    
    function testMixedKeys()
    {
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->primary(['test', 'test2'])
                   ->unique(['test3', 'test4'])
                   ->create('test_table10');
                   
        $this->assertEquals('CREATE TABLE test_table10 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL , test3 varchar(32) NOT NULL , test4 varchar(32) NOT NULL, PRIMARY KEY (`test`,`test2`), UNIQUE KEY (`test3`,`test4`)) ENGINE=INNODB', $this->hoff->lastQuery);
                   
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->index('ik_test', ['test', 'test2'])
                   ->unique(['test3', 'test4'])
                   ->create('test_table11');
                   
        $this->assertEquals('CREATE TABLE test_table11 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL , test3 varchar(32) NOT NULL , test4 varchar(32) NOT NULL, UNIQUE KEY (`test3`,`test4`), INDEX `ik_test` (`test`,`test2`)) ENGINE=INNODB', $this->hoff->lastQuery);
        
        $this->hoff->column('test')->varchar(32)
                   ->column('test2')->varchar(32)
                   ->column('test3')->varchar(32)
                   ->column('test4')->varchar(32)
                   ->primary(['test', 'test2'])
                   ->index('ik_test', ['test3', 'test4'])
                   ->create('test_table12');
        
        $this->assertEquals('CREATE TABLE test_table12 (test varchar(32) NOT NULL , test2 varchar(32) NOT NULL , test3 varchar(32) NOT NULL , test4 varchar(32) NOT NULL, PRIMARY KEY (`test`,`test2`), INDEX `ik_test` (`test3`,`test4`)) ENGINE=INNODB', $this->hoff->lastQuery);
    }
}
?>