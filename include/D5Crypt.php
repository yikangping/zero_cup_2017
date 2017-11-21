<?php
	
    /**
	 *	PHP加密解密类
	 *	
	 *	D5Framework
	 *	
	 */
    
	class D5Crypt
	{
		var $_key;
		
		public function D5Crypt($k='')
		{
			if($k!='') $this->setupKey($k);
		}
		
		public function setupKey($k)
		{
			$this->_key = $k;
		}
		
		private function keyED($txt)
		{
			if(empty($this->_key)) die('Please setup key first.');
			$encrypt_key = md5($this->_key);
			$ctr=0;
			$tmp = "";
			for ($i=0;$i<strlen($txt);$i++)
			{
				if($ctr==strlen($encrypt_key)) $ctr=0;
				$tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
				$ctr++;
			}
			return $tmp;
		}
		
		public function encode($txt)
		{
			srand((double)microtime()*1000000);
			$encrypt_key = md5(rand(0,32000));
			$ctr=0;
			$tmp = "";
			for($i=0;$i<strlen($txt);$i++)
			{
				if ($ctr==strlen($encrypt_key)) $ctr=0;
				$tmp.= substr($encrypt_key,$ctr,1) . (substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
				$ctr++;
			}
			
			return $this->keyED($tmp);
		}
		
		public function decode($txt)
		{
		
			$txt = $this->keyED($txt);
			$tmp = "";
			for($i=0;$i<strlen($txt);$i++)
			{
				$md5 = substr($txt,$i,1);
				$i++;
				$tmp.= (substr($txt,$i,1) ^ $md5);
			}
			return $tmp;
		}
	}

?>