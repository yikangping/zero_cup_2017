<?php
	
	class Encoder
	{
		public function _6(&$temp,$data)
		{
			$temp.= pack('N',intval($data[0]));
			$key = $data[1];
			$temp.= pack('a'.strlen($key),$key);		
		}
		
		public function _88(&$temp,$data)
		{
			$key = $data[0];
			$country = intval($data[1]);
			$temp.= pack('a'.strlen($key),$key);
			$temp.= pack('N',$country);
			$str = md5($country.$key.PHP_CALL_JAVA_KEY);
			$temp.= pack('a'."32",$str);
		}
	}
	
?>