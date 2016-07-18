<?php

class Hoff
{
    private $types = [];
    
    private $indexTypes = [];
    
    private $db = null;
    
    public $columns = [];
    public $table = [];
    public $primarys = [];
    
    public $lastQuery = '';
    
    function __construct($db)
    {
        $this->db    = $db;
        
        $this->types = ['tinyint' , 'smallint' , 'mediumint', 'int'       , 'bigint'  , 
                         'float'   , 'double'   , 'decimal'  , 'bit'       , 'char'    ,
                         'varchar' , 'tinytext' , 'text'     , 'mediumtext', 'longtext', 
                         'binary'  , 'varbinary', 
                         'tinyblob', 'blob'     , 'mediumblob', 'longblob', 
                         'enum'    , 'set'      , 'date'      , 'datetime', 'time', 'timestamp', 'year'];
        
        $this->table = ['name'        => null,
                        'type'        => 'INNODB',
                        'uniqueKeys'  => [],
                        'primaryKeys' => [],
                        'indexKeys'   => []];
        
        $this->indexTypes = ['index', 'unique', 'primary'];
    }
    
    function clean()
    {
        $this->columns = [];
    }
    
    function setTableType($type)
    {
        $this->table['type'] = $type;
    }
 
    
    function create($tableName, $comment = null)
    {
        if($comment)
            $this->table['comment'] = $comment;
        
        $this->db->rawQuery($this->tableBuilder());
        $this->clean();
        
        return $this;
    }
    
    function column($columnName)
    {
        $this->columns[] = ['name'          => $columnName,
                            'type'          => null,
                            'length'        => null,
                            'comment'       => null,
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
            
            case 'InnoDB':
            case 'MyISAM':
                return $this->setTableType($method);
                break;
            
            case 'nullable':
            case 'primary' :
            case 'unique'  :
            case 'index'   :
            case 'comment' :
            case 'unsigned':
                return call_user_func_array([$this, '_' . $method], $args);
                break;
            
            /** Default functions */
            default:
                return call_user_func_array([$this, $method], $args);
        }
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
    
    
    
    
    /***********************************************
    /***********************************************
    /************** B U I L D E R S ****************
    /***********************************************
    /***********************************************
    
    /**
     *
     */
    
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
        
        return $query;
    }
    
    
    
    
    /**
     *
     */
     
    function tableBuilder()
    {
        $columns = $this->columnBuilder();
        
        extract($this->table);
        
        $uniqueKeys = $this->uniqueBuilder($uniqueKeys);
        
        $query = "CREATE TABLE $name ($columns) TYPE=$type COMMENT='$comment'";
    }
    
    
    
    
    /**
     * 
     */
    
    function uniqueBuilder($uniqueKeys)
    {
        $query = '';
        
        foreach($uniqueKeys as $groupName => $columns)
        {
            $columns = "`" . implode("`,`", $columns) . "`";
            
            $query .= "UNIQUE KEY `$groupName` ($columns), ";
        }
        
        /** Remove the last unnecessary comma */
        $query = rtrim($query, ', ');
        
        return $query;
    }
    
    
    
    
    /***********************************************
    /***********************************************
    /**************** I N D E X S ******************
    /***********************************************
    /***********************************************
    
    /**
     * 
     */
     
    function _primary($name = null, $with = [])
    {
        return $this;
    }
    
    
    
    
    /**
     * 
     */
     
    function _unique($name = null, $with = [])
    {
        return $this;
    }
    
    
    
    
    /**
     * 
     */
     
    function _index($name = null, $with = [])
    {
        return $this;
    }
    
    
    
    
    /***********************************************
    /***********************************************
    /*************** H E L P E R S *****************
    /***********************************************
    /***********************************************
    
    /**
     * 
     */
     
    function _nullable()
    {
        end($this->columns);
        $lastColumn = &$this->columns[key($this->columns)];
        
        $lastColumn['nullable'] = true;
        $lastColumn['default']  = null;
        
        return $this;
    }
    
    
    
    
    /**
     * 
     */
     
    function _unsigned()
    {
        end($this->columns);
        $lastColumn = &$this->columns[key($this->columns)];
        
        $lastColumn['unsigned'] = true;
        
        return $this;
    }
    
    
    
    
    /**
     * 
     */
    
    function _comment($comment)
    {
        end($this->columns);
        $lastColumn = &$this->columns[key($this->columns)];
        
        $lastColumn['comment'] = $comment;
        
        return $this;
    }
}
?>

