<?php


	/***********************************************
	 *
	 *	D5Framework
	 *	D5Power Studio
	 *	author:Benmouse		date:2007-10-14
	 *	Update:2008-06-10	Ver:1.0
	 *	第五动力工作室 - D5轻型开发框架
	 *	
	 *
	 **********************************************/

	// Database class
	
	class D5DB{
	
		private $sqlinfo;			//SQL语言信息
		private $num;   			//记录数
		private $result;				//操作结果
		private $row; 				//数据查询结果
		private $db_ver;			//取得MYSQL版本号
		private $mysqli;
		//构造函数
		function __construct()
		{
			$this->mysqli = new mysqli();
		}
		
		function connect($db_name='')
		{
			

			if(empty($GLOBALS['config']['db'])) die('Please setup database first.');
			if($db_name=='') $db_name = $GLOBALS['config']['db']['dbname'];
			$this->mysqli->connect($GLOBALS['config']['db']['hostname'],$GLOBALS['config']['db']['username'],$GLOBALS['config']['db']['password'],$db_name);
			if($this->mysqli->connect_error) back(D5Error::ERR_DB_UNCONNECT,array($this->mysqli->connect_errno,$this->mysqli->connect_error));
			
			$result = $this->mysqli->query("select version()");
			$db_ver = $result->fetch_array();
			$db_ver = floatval(substr($db_ver['version()'],0,3));											//取得MYSQL版本号
			
			//高于指定版本，指定编码
			$this->mysqli->query("SET NAMES '".str_replace("-","",$GLOBALS['config']['db']['encode'])."'");
			$this->db_ver = $db_ver;
		}
		
		/**
		 * 获取某字段重复值的列表
		 */
		function getRepetList($table,$field)
		{
			$sql = 'SELECT  `'.$field.'` , COUNT(  `'.$field.'` ) AS count
					FROM  `'.$table.'` 
					GROUP BY  `'.$field.'` 
					HAVING COUNT(  `'.$field.'` ) >1
					ORDER BY count DESC';
			return $this->vals($sql,true);
		}
		
		//插入新数据或更新原来的数据
		function query($sqlinfo){
			if(DEBUG)
			{
				$this->result = $this->mysqli->query($sqlinfo);
			}else{
				$this->result = $this->mysqli->query($sqlinfo);
			}
			if($this->result===false) back(D5Error::ERR_DB_QUERYERR,$this->mysqli->error);
			return $this->result;
		}
	
		//数据查询
		function db_select($sqlinfo){
			$this->result=$this->mysqli->query($sqlinfo);
			$this->num=$this->result->num_rows;
			$this->row=$this->result->fetch_array();
			
			if($this->result){
				return $this->row;
			}else{
				return false;
			}
		
		}
	 
		//数据指针移动
		function db_seek($i){
			if($this->result){
				$this->result->data_seek($i);
				$this->row=$this->result->fetch_array($this->result);
			}else{			
				return false;
			}
		}
		//获取结果数组
		function fetch_array(){
			if($this->result)
			{
				$this->row=$this->result->fetch_array();
				return $this->row;
			}else{
				return false;
			}
		}
		
		/**
		 *	根据某字段特定值获得另外一字段的值
		 *	@param	$value		值
		 *	@param	$field		$value所对应的字段
		 *	@param	$key		要取得的字段
		 *	@param	$table		要查询的表
		 */
		 function getValue($key,$field,$value,$table)
		 {
			 $this->val("select {$key} from {$table} where {$field}='{$value}'");
			 return $this->row[$key];
		 }
		 
		 /**
		 *	根据某字段特定值获得另外若干个字段的值
		 *	@param	$value		值
		 *	@param	$field		$value所对应的字段
		 *	@param	$key		要取得的字段
		 *	@param	$table		要查询的表
		 */
		 function getValues($key,$field,$value,$table)
		 {
		 	$this->val("select {$key} from {$table} where {$field}='{$value}'");
			return $this->row;
		 }
		 
		 /**
		  *	根据sql获取单行的值
		  *	@param $sql
		  */
		 public function val($sql)
		 {
			 $this->query($sql);
			 if($this->fetch_array())
			 {
				 return $this->row;
			 }else{
			 	return false;
			 }
		 }
		 
		 /**
		  *	根据sql获取全部返回数据
		  *	@param	$sql
		  *	@param	$start	开始位置
		  *	@param	$max	最大记录数
		  *	@param	$getAll	是否获取全部数据（不分页）
		  */
		  public function vals($sql,$getAll=false,$start=0,$max=0)
		  {
			  $max = $max==0 ? $GLOBALS['config']['page']['default'] : $max;
			  $start = $start==0 ? $GLOBALS['nowrecord'] : $start;
			  $sql = (strstr($sql,'LIMIT') || $getAll) ? $sql : $sql." LIMIT {$start},{$max}";
			  $this->query($sql);
			  if($this->fetch_array())
			  {
				  $result = array();
				  do
				  {
					array_push($result,$this->row);
				  }while($this->fetch_array());
				  return $result;
			  }else{
			  	return NULL;
			  }
		  }
	
		 /**
		  *	获得数据库版本号
		  */
		public function get_db_ver()
		{
			return $this->db_ver;
		}
	
		 /**
		  *	获得记录数量
		  */
		public function getnum(){
			return $this->num;
		}
		
		/**
		  *	最新插入ID
		  */
		public function insert_id()
		{
			return $this->mysqli->insert_id;
		}
		
		/**
		  *	获得影响行数
		  */
		public function affrows()
		{
			return $this->mysqli->affected_rows;
		}
	
		//关闭数据库连接
		public function close()
		{
			$this->mysqli->close();
		}
	}
?>
