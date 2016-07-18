<?php

class Hoff
{
    private $types = [];
    
    private $indexTypes = [];
    
    private $db = null;
    
    public $columns = [];
    
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
    
    function create($tableName, $comment = null)
    {
        $columns = $this->columnBuilder();
        
        echo var_dump($columns);
        
        $this->db->rawQuery("CREATE TABLE $tableName ($columns)");
        $this->clean();
        
        return $this;
    }
    
    function column($columnName, $comment = null)
    {
        $this->columns[] = ['name'          => $columnName,
                            'type'          => null,
                            'length'        => null,
                            'comment'       => $comment,
                            'unsigned'      => false,
                            'primary'       => null,
                            'unique'        => null,
                            'index'         => null,
                            'autoIncrement' => false,
                            'default'       => false,
                            'nullable'      => false,
                            'extras'        => []];
        return $this;
    }

    function __call($method, $args)
    {
        switch($method)
        {
            /** One length required */
            case 'tinyint'   :
            case 'smallint'  :
            case 'mediumint' :
            case 'int'       :
            case 'bigint'    :
            case 'char'      :
            case 'varchar'   :
            case 'binary'    :
            case 'varbinary' :
            case 'bit'       :
                /** bit(1) */
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
                /** year() */
                return $this->setType($method);
                break;
            
            /** Two lengths required */
            case 'double' :
            case 'decimal':
                /** double([0, 2]) */
                return $this->setType($method, $args[0]);
                break;
            
            /** One or two lengths required */
            case 'float':
                /** float([0, 2]) or float([1]) or float(1) */
                return $this->setType($method, $args[0]);
                break;
            
            /** Options length */
            case 'enum':
            case 'set' :
                /** enum(['A', 'B', 'C']) */
                return $this->setType($method, $args[0]);
                break; 
            
            /** Default functions */
            default:
                return call_user_func_array([$this, $method], $args);
        }
    }
    
    function columnBuilder()
    {
        $query = '';
        
        foreach($this->columns as $column)
        {
            extract($column);
            
            $lengthForQuery       = is_array($length) ? implode(', ', $length) 
                                                      : null;
            $lengthForQueryQuotes = is_array($length) ? "'" . implode("','", $length) . "'" 
                                                      : null;
                                                      
            /**
             * Column name
             */
             
            $query .= "$name ";
                
            /**
             * Data types
             */
             
            /** VARCHAR(30) */
            if($type && $length && !is_array($length))
                $query .= "$type($length) ";
            
            /** FLOAT(1, 2) */
            elseif($type && $length && is_array($length) && isset($length[0]) && is_int($length[0])) 
                $query .= "$type($lengthForQuery) ";
            
            /** ENUM('A', 'B', 'C') */
            elseif($type && $length && is_array($length) && isset($length[0]) && !is_int($length[0]))
                $query .= "$type($lengthForQueryQuotes) ";
            
            /**
             * Unsigned
             */
            
            if($unsigned)
                $query .= 'UNSIGNED ';
            
            /**
             * Nullable
             */
             
            if(!$nullable)
                $query .= 'NOT NULL ';
            
            /**
             * Primary key
             */
            
            if($primary && !is_array($primary))
                $query .= 'PRIMARY ';
            
            /**
             * Comment
             */
             
            if($comment)
                $query .= "COMMENT='$comment'";
                
            /**
             * End
             */
            
            $query .= ', ';
        }
        
        /** Remove the last unnecessary comma */
        $query = rtrim($query, ', ');
    }
    
    function tableBuilder()
    {
        $tableName = null;
        $columns = null;
        $type = ['MYISAM', 'INNODB'];
        $query = "CREATE TABLE $tableName ($columns) TYPE=$type COMMENT='123'";
    }
    
    function comment($comment)
    {
        end($this->columns);
        $lastColumn = &$this->columns[key($this->columns)];
        
        $lastColumn['comment'] = $comment;
        
        return $this;
    }
    
    function setIndex()
    {
    }
    
    function primary($with = [])
    {
    }

    function setType($type, $length = null, $extras = null)
    {
        end($this->columns);
        $lastColumn = &$this->columns[key($this->columns)];
        
        $lastColumn['type']   = $type;
        $lastColumn['length'] = $length;
        $lastColumn['extras'] = $extras;
        
        return $this;
    }
    
    function nullable()
    {
        end($this->columns);
        $lastColumn = &$this->columns[key($this->columns)];
        
        $lastColumn['nullable'] = true;
        $lastColumn['default']  = null;
        
        return $this;
    }
    
    function unsigned()
    {
        end($this->columns);
        $lastColumn = &$this->columns[key($this->columns)];
        
        $lastColumn['unsigned'] = true;
        
        return $this;
    }
}
?>

