<?php

class MySql
{
	private $m_instance;
	
	function __construct($type = false){
		#此处填写自己公司的数据库信息
		#$this->m_instance = mysql_connect('…….aliyuncs.com','……','eac9……');	
		mysql_query("SET NAMES 'latin1'");
		
		if (!($this->m_instance))
		{
			echo "it failed to connect the database";
		}

		if(!mysql_select_db("fastcast",$this->m_instance))
		{
			echo "it failed to set the database.";
		}
	
	}

	function select($sql,&$resultset){
		
		$result = mysql_query($sql, $this->m_instance);
		if(!$result)
		{
			return 0;
		}
		$num_rows = mysql_num_rows($result) ;

		//MYSQL_NUM：索引数组
		//MYSQL_ASSOC：关联数组
		//MYSQL_BOTH
		$resultset = array();
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			array_push($resultset,$row);
		}
		mysql_free_result($result);
		return $num_rows;
	}
	
	function insert($table, $data){
		$set = array();
		foreach($data as $k => $v){
			$set[$k] = $v;	
		}
		$keys = array_keys($set);
		$values = array_values($set);
		$sql = "INSERT INTO ".$table." (".implode(', ', $keys).") VALUES ('".implode('\', \'', $values)."')";
		$result = mysql_query($sql, $this->m_instance);
                if(!$result)
                {	
                        echo "insert data failed!".$table.PHP_EOL;
			$this->save($sql);
			return false;
                }
                return  mysql_insert_id($this->m_instance) ;

	}	

	function delete_id($table, $id){
		if($id){
		
			$sql = "DELETE FROM ".$table." WHERE id = ".$id;
			
                	$result = mysql_query($sql, $this->m_instance);
                	if(!$result)
                	{
                        	echo "delete data failed!".$table;
                	}
                	return  mysql_affected_rows($this->m_instance) ;

		}
	}
        function delete($sql){

                $result = mysql_query($sql, $this->m_instance);
                if(!$result)
                {
                        echo "delete data failed!".$sql;
                }
                return  mysql_affected_rows($this->m_instance) ;

        }

        function update($sql){

                $result = mysql_query($sql, $this->m_instance);
                if(!$result)
                {
                        echo "update data failed!".$sql;
                }
                return  mysql_affected_rows($this->m_instance) ;

        }

        function save($str){
                $day = date('Y-m-d',time());
                $file = '/tmp/'.$day.'.log';
                $fp = fopen($file,'a+');
                flock($fp, LOCK_EX);
                fwrite($fp,$str.PHP_EOL);
                flock($fp, LOCK_UN);
                fclose($fp);
        }

    function destroy(){
        $this->__destruct();
    }	
	function __destruct()
	{
	    	if($this->m_instance) {
			mysql_close($this->m_instance);
            		$this->m_instance = null;
	    	}
	}
}

?>
