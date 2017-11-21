<?php

	class Decoder
	{
		public function _6($data)
		{
			$arr = unpack("Nresult", $data);
			return $arr;
		}
		
		public function _88($data)
		{
			//取字符串用         a.长度
// 			$arr = unpack("a".strlen($data)."result", $data);
			$arr = unpack("Nresult", $data);
			return $arr;
		}
	}

?>