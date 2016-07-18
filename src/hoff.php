<?php

class Hoff
{
    private $types = [];
    
    private $indexTypes = [];
    
    private $db = null;
    
    private $columns = [];
    
    function __construct($db)
    {
        $this->db    = $db;
        
        $this->types = ['tinyint' , 'smallint' , 'mediumint', 'int'       , 'bigint'  , 
                         'float'   , 'double'   , 'decimal'  , 'bit'       , 'char'    ,
                         'varchar' , 'tinytext' , 'text'     , 'mediumtext', 'longtext', 
                         'binary'  , 'varbinary', 
                         'tinyblob', 'blob'     , 'mediumblob', 'longblob', 
                         'enum'    , 'set'      , 'date'      , 'datetime', 'time', 'timestamp', 'year'];
        
        $this->indexTypes = ['index', 'unique', 'primary'];
    }
    
    function clean()
    {
        $this->columns = [];
    }
    
    function create($tableName, $comment)
    {
        $this->db->rawQuery("CREATE TABLE $tableName ($columns)");
        $this->clean();
    }
    
    function column($columnName, $comment)
    {
        $this->columns[] = ['name'          => $columnName,
                            'type'          => null,
                            'length'        => null,
                            'comment'       => null,
                            'primary'       => null,
                            'unique'        => null,
                            'index'         => null,
                            'autoIncrement' => false,
                            'default'       => false,
                            'nullable'      => false,
                            'extras'        => []];
    }

    function __call($method, $args)
    {
        switch($method)
        {
            /** Length required */
            case 'tinyint'   :
            case 'smallint'  :
            case 'mediumint' :
            case 'int'       :
            case 'bigint'    :
            case 'char'      :
            case 'varchar'   :
            case 'binary'    :
            case 'varbinary' :
                return $this->setType($method, $args[0]);
                break;
            
            /** No length needed */
            case 'tinytext'  :
            case 'text'      :
            case 'mediumtext':
            case 'longtext'  :
            case 'tinyblob'  :
            case 'blob'      :
            case 'mediumblob':
            case 'longblob'  :
            case 'date'      :
            case 'datetime'  :
            case 'time'      :
            case 'timestamp' :
            case 'year'      :
                
            case 'float'     :
            case 'double'    :
            case 'decimal'   :
            case 'bit'       :
            
            
            case 'enum'      :
            case 'set'       :
            
        }
    }
    
    function comment($comment)
    {
        $lastColumn = &$this->lastColumn();
        
        $lastColumn['comment'] = $comment;
        
        return $this;
    }
    
    function setIndex()
    {
    }
    
    function lastColumn()
    {
        return end($this->columns);
    }
    
    function setType($type, $length, $extras = null)
    {
        $lastColumn = &$this->lastColumn();
        
        $lastColumn['type']   = $type;
        $lastColumn['length'] = $length;
        $lastColumn['extras'] = $extras;
        
        return $this;
    }
    
    function nullable()
    {
        $lastColumn = &$this->lastColumn();
        
        $lastColumn['nullable'] = true;
        $lastColumn['default']  = null;
        
        return $this;
    }
    
    function unsigned()
    {
        $lastColumn = &$this->lastColumn();
        
        $lastColumn['unsigned'] = true;
        
        return $this;
    }
}
?>

