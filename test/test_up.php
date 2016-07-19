<?php
class Upstream
{
    function __construct()
    {
        $this->table = new Hoff(new MysqliDb('localhost', 'root', '', 'hoff'));
    }

    function test_table()
    {
        $this->table->column('test')->varchar(32)->primary()
                    ->create('test_table');
    }
}
?>