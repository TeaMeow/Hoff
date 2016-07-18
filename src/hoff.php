<?php
class Hoff
{
    /**
     * The MysqliDB object.
     * 
     * @var MysqliDb
     */
     
    private $db = null;
    
    /**
     * The columns of the table.
     * 
     * @var array
     */
     
    public $columns = [];
    
    /**
     * The current table information.
     * 
     * @var array
     */
     
    public $table = [];
    
    /**
     * The last generated query.
     * 
     * @var string
     */
    
    public $lastQuery = '';
    
    
    
    
    /**
     * CONSTRUCT
     * 
     * @codeCoverageIgnore
     */
     
    function __construct($db)
    {
        $this->db = $db;
        
        $this->clean();
    }
    
    

 
    /**
     * CALL
     */

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
            case 'double'    :
            case 'decimal'   :
            case 'float'     :
            case 'enum'      :
            case 'set'       :
                /** bit(1), double([0, 2]), float([0, 2]), float([1]), enum(['A', 'B', 'C']) */
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
            
            /** Table types */
            case 'InnoDB':
            case 'MyISAM':
                return $this->setTableType($method);
                break;
            
            /** Indexs */
            case 'primary' :
            case 'unique'  :
            case 'index'   :
                $groupName = isset($args[0]) ? $args[0] : null;
                $columns   = isset($args[1]) ? $args[1] : null;
                return $this->setIndex($method, $groupName, $columns);
                break;
            
            /** Default functions */
            default:
                return call_user_func_array([$this, '_' . $method], $args);
                break;
        }
    }
    
    
    
    
    /**
     * Clean the previous data.
     * 
     * @return Hoff
     */
     
    function clean()
    {
        $this->columns = [];
        $this->table   = ['name'        => null,
                          'type'        => 'INNODB',
                          'uniqueKeys'  => [],
                          'primaryKeys' => [],
                          'indexKeys'   => [],
                          'comment'     => null];
        
        return $this;
    }
    
    
    
    
    /**
     * Set the table types
     * 
     * @param string $type   The type of the table, InnoDB or MyISAM.
     * 
     * @return Hoff
     */
     
    function setTableType($type)
    {
        $this->table['type'] = $type;
        
        return $this;
    }
    
    
    
    
    /**
     * Set the type of the column(last).
     * 
     * @param string $type     The data type of the column.
     * @param mixed  $length   Can be the options of the type, or length of the type.
     * 
     * @return Hoff
     */
 
    function setType($type, $length = null)
    {
        $this->setLastColumnValue('type'  , $type);
        $this->setLastColumnValue('length', $length);

        return $this;
    }
    
    
    
    
    /***********************************************
    /***********************************************
    /************** B U I L D E R S ****************
    /***********************************************
    /***********************************************
    
    /**
     * Build the query from the columns.
     * 
     * @return string   The query.
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
                $query .= "COMMENT '$comment' ";
                
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
     * Build the whole query which used to create a table.
     * 
     * @return string   The query.
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

        return $query;
    }
    
    
    
    
    /**
     * The builder which used to generate the query of the index, unique, primary keys.
     * 
     * @param string $indexName   The index prefix, ex: `PRIMARY KEY`, `UNIQUE KEY`, `INDEX`.
     * @param array  $keys        The assoc or 1D array, assoc array for grouping keys.
     * 
     * @return string   The query.
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
     * Set the index, unqiue, primary keys.
     * 
     * @param string $indexType   The type of the index, ex: `primary`, `unique`, `index`.
     * @param mixed  $groupName   The name of the group, make an anonymous index when this is an array.
     * @param array  $columns     The columns of the group of the index.
     * 
     * @return Hoff
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
    /****************** M A I N ********************
    /***********************************************
    /***********************************************
    
    /**
     * Generate the query, execute it, create the table.
     * 
     * @param string      $tableName   The name of the table.
     * @param string|null $comment     The comment of the table.
     * 
     * @return Hoff
     */
     
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
    
    
    
    
    /**
     * Create a column.
     * 
     * @param string $columnName   The name of the column.
     * 
     * @return Hoff
     */
     
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
 
    
    
    
    /***********************************************
    /***********************************************
    /*************** H E L P E R S *****************
    /***********************************************
    /***********************************************
    
    /**
     * Set the value of the last column.
     * 
     * @param string $name    The name of the data.
     * @param mixed  $value   The value of the data.
     * 
     * @return Hoff
     */
     
    function setLastColumnValue($name, $value)
    {
        end($this->columns);
        $lastColumn = &$this->columns[key($this->columns)];
        
        $lastColumn[$name] = $value;
        
        return $this;
    }
    
    
    
    
    /**
     * Make the column nullable, and set it's DEFAULT as NULL.
     * 
     * @return Hoff
     */
     
    function _nullable()
    {
        $this->setLastColumnValue('nullable', true);
        $this->setLastColumnValue('default', null);

        return $this;
    }
    
    
    
    
    /**
     * Make the column UNSIGNED.
     * 
     * @return Hoff
     */
     
    function _unsigned()
    {
        $this->setLastColumnValue('unsigned', true);
        
        return $this;
    }
    
    
    
    
    /**
     * Set a comment of the column.
     * 
     * @param string $comment   The comment of the column.
     * 
     * @return Hoff
     */
    
    function _comment($comment)
    {
        $this->setLastColumnValue('comment', $comment);
        
        return $this;
    }
    
    
    
    
    /**
     * Set the DEFAULT value of the column.
     * 
     * @param mixed $default   The default value of the column.
     * 
     * @return Hoff
     */
    
    function _default($default)
    {
        $this->setLastColumnValue('default', $default);

        return $this;
    }
    
    
    
    
    /**
     * Make the column AUTO_INCREMENT.
     * 
     * @return Hoff
     */
     
    function _autoIncrement()
    {
        $this->setLastColumnValue('autoIncrement', true);
        
        return $this;
    }
}
?>

