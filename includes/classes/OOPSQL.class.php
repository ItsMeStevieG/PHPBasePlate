<?php

class OOPSQL {
    private $host;
    private $user;
    private $pass;
    private $dbname;
    
    //store the single instance
    private static $instance;
    
    private $connection;
    private $results;
    public $numRows;
    
    /**
    * get an instance of the database
    * @return Database
    */
    static function getInstance()
    {
        if(!self::$instance)
        {
            self::$instance=new self();
        }
        return self::$instance;
    }
    
    /**
    * constructor
    */
    private function __construct()
    {    
    }
    
    /**
    * Empty clone magic method to prevent duplication.
    */
    private function __clone(){}
    
    public function connect($host,$user,$pass,$dbName)
    {
        $this->host=$host;
        $this->user=$user;
        $this->pass=$pass;
        $this->dbname=$dbName;
        
        $this->connection=mysqli_connect($this->host,$this->user,$this->pass,$this->dbname);        
    }
    
    public function doQuery($sql)
    {
        if(!$this->results=mysqli_query($this->connection,$sql))
        {
            trigger_error('QUERY FAILURE:'
            . ' ERRNO: '
            . mysqli_errno($this->connection)
            . ' ERROR: '
            . mysqli_error($this->connection)
            . ' QUERY: '
            . $sql,E_USER_ERROR);
        }
        else
        {
            $this->numRows=mysqli_affected_rows($this->connection);   
        }
    }
    
    public function loadObjectList()
    {
        $obj=false;
        if($this->results)
        {
            $obj=mysqli_fetch_assoc($this->results);
        }
        return $obj;
    }
    
    public function escapeString($string)
    {
        $escaped=mysqli_real_escape_string($this->connection, $this->results);
        return $escaped;
    }
}

class table {
    protected $id=NULL;
    protected $table=NULL;
    
    function __constructor()
    {
    }
    
    function bind($data)
    {
        foreach($data as $key=>$value)
        {
            $this->$key=$value;
            //die("K $key | V $value");
        }
    }
    
    function load($id)
    {
        $this->id=$id;
        $dbo=database::getInstance();
        $sql=$this->buildQuery('load');
        
        $dbo->doQuery($sql);
        $row=$dbo->loadObjectList();
        foreach($row as $key => $value)
        {
             if($key=="id")
             {
                 continue;
             }
             $this->$key=$value;
        }
    }
    
    function store()
    {
        $dbo=database::getInstance();
        $sql=$this->buildQuery('store');
        //die("SQL $sql");
        $dbo->doQuery($sql);
    }
    
    protected function buildQuery($task)
    {
        $sql="";
        if($task=="store")
        {
            if($this->id=="")
            {
                $keys="";
                $values="";
                //var_dump($this);
                $classVars=get_class_vars(get_class($this));
                //die("ClassVars:<br/><pre>".print_r($classVars,true)."</pre>");
                $sql.="Insert into {$this->table}";
                foreach($classVars as $key=>$value)
                {
                    if($key=="id" || $key=="table")
                    {
                        continue;
                    }
                    
                    $keys.="{$key},";
                    $values.="'{$this->$key}',";
                }
                $sql.="(".substr($keys,0,-1).") Values (".substr($values,0,-1).")";
                //die("SQL $sql");
                //Insert into table (id,fname,lname) Values (1,'Steven','Graham')
            }
            else
            {
                $classVars=get_class_vars(get_class($this));
                $sql.= "Update {$this->table} set ";
                foreach($classVars as $key=>$value)
                {
                    if($key=="id" || $key=="table")
                    {
                        continue;
                    }
                    
                    $sql.="{$key} = '{$this->$key}', ";
                }
                $sql=substr($sql,0,-2)." where id = {$this->id}";
            }
        }
        else if($task=="load")
        {
            $sql="select * from {$this->table} where id ='{$this->id}'";
        }
        return $sql;
    }
}

?>