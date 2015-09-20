<?php
/*
 * TODO $error
 * TODO multiquery
 * */
namespace Repositories;

class MySqliAdapter {

	private $preparedSql;
    private $dbConnection;
    public  $num_rows;
    public  $insert_id;
    public  $binded_value;
    public  $error;
    private $resource;

	public function __construct(){

		$connectionHelper =new ConnectionHelper();
		$this->dbConnection = $connectionHelper->dbConnect();

	}

	/**
	 * prepared statement for mysql_connect
	 * @param $sql String
	 * @return MySqliAdapter
	 */
	public function prepare($sql){
		$this->preparedSql = $sql;
		return $this;
	}
    /**
     * bind parameter for mysql_connect
     * @param $parameterTypes String
     * @return MySqliAdapter
     */
	public function bind_param(){

        $parameters = func_get_args();

        for($i=1; $i<count($parameters); $i++){
            $this->preparedSql = substr_replace($this->preparedSql, sanitize_string($parameters[$i]) ,strpos($this->preparedSql, "?"),1);
        }
        return $this;
	}

	/**
	 * prepared statement for mysql_connect
	 * @return MySqliAdapter
	 */
	public function execute(){
		$this->resource = @mysql_query($this->preparedSql, $this->dbConnection);
        $this->insert_id = @mysql_insert_id($this->dbConnection);
        $this->error = @mysql_error($this->dbConnection);
		return $this;
	}

    /**
     * set charset for mysql_connect
     * @return Boolean
     */
	public function set_charset($charset){
        return mysql_set_charset($charset, $this->dbConnection);
	}
    /**
     * get_result statement for mysql_connect
     * @return MySqliAdapter
     */
	public function get_result(){
        $this->num_rows = mysql_num_rows($this->resource);
        return $this;
	}

    /**
     * return associative array for mysql_connect result
     * @return Array
     */
	public function fetch_assoc(){
        return mysql_fetch_assoc($this->resource);
	}

    /**
     * return enumerated array for mysql_connect result
     * @return Array
     */
	public function fetch_row(){
        return mysql_fetch_row($this->resource);
	}

    /**
     * raw query for mysql_connect
     * @return MySqliAdapter
     */
	public function query($sql){

        $this->resource = mysql_query($sql);
        $this->insert_id = @mysql_insert_id($this->dbConnection);
        $this->error = @mysql_error ($this->dbConnection);
        return $this;

    }

	/**
     * free resource for mysql_connect
     * @return Boolean
     */
	public function free_result(){
        if(mysql_free_result($this->resource)){
            $this->resource = null;
            $this->preparedSql = null;
            $this->num_rows = null;
            return true;
        }else{
            return false;
        }
	}

    /**
     * TODO study mysqli and learn its bind_result method
     * TODO Just binding the result on my own now
     * _anz
     * bind result to a variable
     * @return Boolean
     */
	public function bind_result(&$var){

        $this->binded_value = &$var;
        $this->binded_value = @mysql_fetch_array($this->resource, MYSQL_ASSOC);
        $var = $this->binded_value;
	}

    /**
     * TODO study mysqli to and learn its fetch method
     * TODO just returning a variable now
     * _anz
     * bind result to a variable
     * @return Boolean
     */
	public function fetch(){
        if(count($this->binded_value) == 1){
            $this->binded_value = array_pop($this->binded_value);
        };
        return true;
	}

    /* TRANSACTION FUNCTIONS
     *
     * mysql_connect -- no function for starting and rolling back transactions
     * use mysql_query("BEGIN"), mysql_query("COMMIT") and mysql_query("ROLLBACK")
     * won't work for table that doesn't support transaction like MyISAM
     * _anz
     **/

    /**
     * autocommit for mysql_connect
     * @return Boolean
     */
    public function autocommit($boolean){
        if($boolean){
            if(@mysql_query("SET AUTOCOMMIT = 0")){
                if(@mysql_query('START TRANSACTION')){
                    return true;
                }
            }
        }
        else{
            if(@mysql_query("SET AUTOCOMMIT = 1")){
                if(@mysql_query('START TRANSACTION')){
                    return true;
                }
            }
        }
    }


    /**
     * commit for mysql_connect
     * @return Boolean
     */
    public function commit(){
        return @mysql_query("COMMIT");
    }


    /**
     * rollback for mysql_connect
     * @return Boolean
     */
    public function rollback(){
        return @mysql_query("ROLLBACK");
    }

}