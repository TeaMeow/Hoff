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
        $this->db = $db;
        
        $this->clean();
    }
    
    function clean()
    {
        $this->columns = [];
        $this->table   = ['name'        => null,
                          'type'        => 'INNODB',
                          'uniqueKeys'  => [],
                          'primaryKeys' => [],
                          'indexKeys'   => [],
                          'comment'     => null];
    }
    
    function setTableType($type)
    {
        $this->table['type'] = $type;
        
        return $this;
    }
 
    
    
    function _create($tableName, $comment = null)
    {
        if($comment)
            $this->table['comment'] = $comment;
        
        $this->table['name'] = $tableName;
        
        $query = $this->tableBuilder();
        
        $this->db->rawQuery($query);
        
        $this->lastQuery = $query;
        
        $this->clean();
        
        return $this;
    }
    
    function _column($columnName)
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
            
            case 'primary' :
            case 'unique'  :
            case 'index'   :
                $groupName = isset($args[0]) ? $args[0] : null;
                $columns   = isset($args[1]) ? $args[1] : null;
                return $this->setIndex($method, $groupName, $columns);
                break;
                
            //case 'nullable':
            //case 'comment' :
            //case 'unsigned':
            //    return call_user_func_array([$this, '_' . $method], $args);
            //    break;
            
            /** Default functions */
            default:
                return call_user_func_array([$this, '_' . $method], $args);
                break;
        }
    }
 
    function setType($type, $length = null, $extras = null)
    {
        $this->setLastColumnValue('type'  , $type);
        $this->setLastColumnValue('length', $length);
        $this->setLastColumnValue('extras', $extras);
        
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
            
            
            $options = [];
            
            if(is_array($length))
                foreach($length as $single)
                    if(is_int($single))
                        $options[] = $single;
                    else
                        $options[] = "'$single'";
            
            $options = empty($options) ? '' : implode(", ", $options);
                                                      
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
            
            /** FLOAT(1, 2) or ENUM(1, 2, 'A', 'B') */
            elseif($type && $length && is_array($length) && isset($length[0])) 
                $query .= "$type($options) ";
            
            /** DATETIME */
            else
                $query .= "$type ";
                
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
             * Auto increment
             */
            
            if($autoIncrement)
                $query .= 'AUTO_INCREMENT ';
            
            /**
             * Default
             */
            
            if($default !== false)
            {
                if(is_int($default))
                    $default = $default;
                elseif(is_null($default))
                    $default = "NULL";
                else
                    $default = "'$default'";
                    
                $query .= "DEFAULT $default ";
            }
            
            /**
             * Keys
             */
            
            if($primary && !is_array($primary))
                $query .= 'PRIMARY KEY ';
            
            if($unique && !is_array($unique))
                $query .= 'UNIQUE ';
            
            if($index && !is_array($index))
                $query .= 'INDEX ';
            
            /**
             * Comment
             */
             
            if($comment)
                $query .= "COMMENT='$comment' ";
                
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
        $query = '';
        
        $columns = $this->columnBuilder();
        
        extract($this->table);
        
        $primaryKeys = !empty($primaryKeys) ? $this->indexBuilder('PRIMARY KEY', $primaryKeys) : null;
        $uniqueKeys  = !empty($uniqueKeys)  ? $this->indexBuilder('UNIQUE KEY' , $uniqueKeys)  : null;
        $indexKeys   = !empty($indexKeys)   ? $this->indexBuilder('INDEX'      , $indexKeys)   : null;
        
        $query = "CREATE TABLE $name ";
        
        if($primaryKeys && !$uniqueKeys && !$indexKeys)
            $query .= "($columns, $primaryKeys) ";
        elseif(!$primaryKeys && $uniqueKeys && !$indexKeys)
            $query .= "($columns, $uniqueKeys) ";
        elseif(!$primaryKeys && !$uniqueKeys && $indexKeys)
            $query .= "($columns, $indexKeys) ";
        elseif($primaryKeys && $uniqueKeys && !$indexKeys)
            $query .= "($columns, $primaryKeys, $uniqueKeys) ";
        elseif($primaryKeys && $uniqueKeys && $indexKeys)
            $query .= "($columns, $primaryKeys, $uniqueKeys, $indexKeys) ";
        elseif(!$primaryKeys && $uniqueKeys && $indexKeys)
            $query .= "($columns, $uniqueKeys, $indexKeys) ";
        elseif($primaryKeys && !$uniqueKeys && $indexKeys)
            $query .= "($columns, $primaryKeys, $indexKeys) ";
        else
            $query .= "($columns) ";
        
        if($type)
            $query .= "ENGINE=$type, ";
            
        if($comment)
            $query .= "COMMENT='$comment', ";
        
        /** Remove the last unnecessary comma */
        $query = rtrim($query, ', ');
        
        echo "\n\n" . $query . "\n\n";
        
        return $query;
    }
    
    
    
    
    /**
     * 
     */
    
    function indexBuilder($indexName, $keys)
    {
        $query = '';
        
        if(array_keys($keys) !== range(0, count($keys) - 1))
        {
            foreach($keys as $groupName => $columns)
            {
                $columns = "`" . implode("`,`", $columns) . "`";
                
                $query .= "$indexName `$groupName` ($columns), ";
            }
        }
        elseif(!empty($keys) && $keys)
        {
            foreach($keys as $columns)
            {
                $columns = "`" . implode("`,`", $columns) . "`";
                
                $query .= "$indexName ($columns), ";
            }
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
     
    function setIndex($indexType, $groupName = null, $columns = [])
    {
        if($indexType === 'primary')
            $indexArray = 'primaryKeys';
        elseif($indexType === 'unique')
            $indexArray = 'uniqueKeys';
        elseif($indexType === 'index')
            $indexArray = 'indexKeys';
        
        /** column()->primary() */
        if(!$groupName && empty($columns))
        {
            end($this->columns);
            $lastColumn = &$this->columns[key($this->columns)];
            
            $lastColumn[$indexType] = true;
        }
        
        /** primary(['username', 'nickname']) */
        elseif(is_array($groupName) && empty($columns))
        {
            $this->table[$indexArray][] = $groupName;
        }
        
        /** primary('groupName', ['username', 'nickname']) */
        elseif($groupName && !empty($columns))
        {
            $this->table[$indexArray][$groupName] = $columns;
        }
        
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
     
    function setLastColumnValue($name, $value)
    {
        end($this->columns);
        $lastColumn = &$this->columns[key($this->columns)];
        
        $lastColumn[$name] = $value;
        
        return $this;
    }
    
    /**
     * 
     */
     
    function _nullable()
    {
        $this->setLastColumnValue('nullable', true);
        $this->setLastColumnValue('default', null);

        return $this;
    }
    
    
    
    
    /**
     * 
     */
     
    function _unsigned()
    {
        $this->setLastColumnValue('unsigned', true);
        
        return $this;
    }
    
    
    
    
    /**
     * 
     */
    
    function _comment($comment)
    {
        $this->setLastColumnValue('comment', $comment);
        
        return $this;
    }
    
    
    
    
    /**
     * 
     */
    
    function _default($default)
    {
        $this->setLastColumnValue('default', $default);

        return $this;
    }
    
    /**
     * 
     */
     
    function _autoIncrement()
    {
        $this->setLastColumnValue('autoIncrement', true);
        
        return $this;
    }
}
?>

