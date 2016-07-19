<?php
class Downstream
{
    function __construct()
    {
        $this->table = new Hoff(new MysqliDb('localhost', 'root', '', 'hoff'));
    }

    function test_table()
    {
        $this->table->drop('test_table');
    }
}
?>