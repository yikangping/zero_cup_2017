<?php
/**
pack/unpack 的摸板字符字符 含义
a 一个填充空的字节串
A 一个填充空格的字节串
b 一个位串，在每个字节里位的顺序都是升序
B 一个位串，在每个字节里位的顺序都是降序
c 一个有符号 char（8位整数）值
C 一个无符号 char（8位整数）值；关于 Unicode 参阅 U
d 本机格式的双精度浮点数
f 本机格式的单精度浮点数
h 一个十六进制串，低四位在前
H 一个十六进制串，高四位在前
i 一个有符号整数值，本机格式
I 一个无符号整数值，本机格式
l 一个有符号长整形，总是 32 位
L 一个无符号长整形，总是 32 位
n 一个 16位短整形，“网络”字节序（大头在前）
N 一个 32 位短整形，“网络”字节序（大头在前）
p 一个指向空结尾的字串的指针
P 一个指向定长字串的指针
q 一个有符号四倍（64位整数）值
Q 一个无符号四倍（64位整数）值
s 一个有符号短整数值，总是 16 位
S 一个无符号短整数值，总是 16 位，字节序跟机器芯片有关
u 一个无编码的字串
U 一个 Unicode 字符数字
v 一个“VAX”字节序（小头在前）的 16 位短整数
V 一个“VAX”字节序（小头在前）的 32 位短整数
w 一个 BER 压缩的整数
x 一个空字节（向前忽略一个字节）
X 备份一个字节
Z 一个空结束的（和空填充的）字节串
@ 用空字节填充绝对位置
*/
class D5Socket
{
	private $socket;
	private $connect;
	private $connected=FALSE;
	
	private $headLength = 7;
	private $loadwait = 5;
	private $loadout = 30;
	
	private $decoder;
	private $encoder;
	
	public function __construct()
	{
		require_once 'Decoder.php';
		require_once 'Encoder.php';
		$this->decoder = new Decoder();
		$this->encoder = new Encoder();
	}
	
	public function connect($host,$port)
	{
		if($connected) return;
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$this->connect = socket_connect($this->socket,$host,$port);
	}
	
	public function call($cmd,$data)
	{
		$temp = pack('n',$cmd);
		$temp.= pack('C',0);
		
		$cmd = dechex($cmd);
		$result = eval("\$this->encoder->_{$cmd}(\$temp,\$data);");
		
		if($result!=NULL && !$result)
		{
			die("Can not call encoder function _{$cmd}.result is ".$result);
		}

		$out = pack('N',strlen($temp)+4);
		$out.=$temp;
		socket_write($this->socket,$out);
		
		return $this->waitBack();
	}
	
	private function parseData($arr,$data=NULL)
	{
		$arr['cmd'] = dechex($arr['cmd']);
		$result = eval("return \$this->decoder->_{$arr['cmd']}(\$data);");
		if($result!=NULL && !$result)
		{
			die("Can not call decoder function _{$arr['cmd']}.");
		}
		return $result;
	}
	
	private function waitBack()
	{
		set_time_limit(2);
		while($buff = socket_read($this->socket, $this->headLength))
		{
			$arr = unpack('Nlength/ncmd/Czip', $buff);
			if($arr['length']==$this->headLength)
			{
				// 数据包已解析完
				 return $this->parseData($arr);
			}else{
				$len = $arr['length']-$this->headLength;
				$start = mktime();
				
				while($buff = socket_read($this->socket, $len))
				{
					usleep($this->loadwait);
					return $this->parseData($arr,$buff);
				}
			}
		}
	}	
}

?>