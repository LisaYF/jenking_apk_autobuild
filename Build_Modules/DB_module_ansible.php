<?php
date_default_timezone_set('PRC');

class MySql
{
	private $m_instance;
	
	function __construct(){
		# 此处填写自己公司的信息
		#$this->m_instance = new mysqli('……aliyuncs.com','……','eac9……','……'); 

		/* check connection */
                if ($this->m_instance->connect_error) {
                    die('Error : ('. $this->m_instance->connect_errno .') '. $this->m_instance->connect_error);
                }
                $this->m_instance->query("SET NAMES 'latin1'");
	}
	
	function select($sql,&$resultset){
		
		$result = $this->m_instance->query($sql);
		if(!$result)
		{
			return 0;
		}
		$num_rows = $result->num_rows;

		$resultset = array();
		while ($row = $result->fetch_array(MYSQLI_ASSOC))
		{
			array_push($resultset,$row);
		}
		$result->close();
		return $num_rows;
	}
	
	function insert($table, $data){
		//"INSERT INTO ".$table." (\"".implode('", "', $keys)."\") VALUES (".implode(', ', $values).")";
		$set = array();
		foreach($data as $k => $v){
			$set[$k] = $v;	
		}
		$keys = array_keys($set);
		$values = array_values($set);
		$sql = "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES ('".implode('\', \'', $values)."')";
		$result = $this->m_instance->query($sql);
                if(!$result)
                {	
			echo $sql;
                        echo "insert data failed!".PHP_EOL;
                }
                return  $this->m_instance->insert_id ;

	}	

	function delete_id($table, $id){
		if($id){
		
			$sql = "DELETE FROM ".$table." WHERE id = ".$id;
			
                	$result = $this->m_instance->query($sql);
                	if(!$result)
                	{
                        	echo "delete data failed!";
                	}
                	return  $this->m_instance->affected_rows;

		}
	}
        function delete($sql){

                $result = $this->m_instance->query($sql);
                if(!$result)
                {
                        echo "delete data failed!";
                }
                return  $this->m_instance->affected_rows;

        }
        function update($sql){

                $result = $this->m_instance->query($sql);
                if(!$result)
                {
                        echo "update data failed!";
                }
                return  $this->m_instance->affected_rows;
        }

	function destroy(){
		$this->__destruct();
	}

	function __destruct()
	{
	    	if($this->m_instance) {
			$this->m_instance->close();
            		$this->m_instance = null;
	    	}
	}
}

?>

