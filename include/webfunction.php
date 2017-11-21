<?php

	/***********************************************
	 *
	 *	
	 *	D5Power Studio
	 *	author:Benmouse		date:2007-10-14
	 *	Update:2008-06-10	Ver:1.0
	 *	第五动力工作室 - D5轻型开发框架
	 *	全局非框架自身函数库文件
	 *
	 **********************************************/
	
	define("SYSLOG_LOGIN",1);
	define("SYSLOG_LOGOUT",2);
	define("SYSLOG_CMS_PUB",3);
	define("SYSLOG_CMS_MOD",4);
	define("SYSLOG_PASS",5);
	define("SYSLOG_CLASS_MOD",6);
	define("SYSLOG_CLASS_DEL",7);
	define("SYSLOG_UPDATE_ADD",8);
	define("SYSLOG_UPLOAD",9);
	function d5_syslog_name($type)
	{
		switch($type)
		{
			case SYSLOG_LOGIN:
				return "登录";
				break;
			case SYSLOG_LOGOUT:
				return "登出";
				break;
			case SYSLOG_CMS_PUB:
				return "发布文章";
				break;
			case SYSLOG_CMS_MOD:
				return "修改文章";
				break;
			case SYSLOG_PASS:
				return "通过审核";
				break;
			case SYSLOG_CLASS_MOD:
				return "修改课程";
				break;
			case SYSLOG_CLASS_DEL:
				return "删除课程";
				break;
			case SYSLOG_UPDATE_ADD:
				return "上传更新包";
				break;
			case SYSLOG_UPLOAD:
				return "上传文件";
				break;
			default:
				return "其他操作";
				break;
		}
	}
	function d5_syslog($type,$uid,$uname,$key='无',$db=null)
	{
		return;
		if($db==null)
		{
			$db = new d5db();
			$db->connect();
		}
		$uid = intval($uid);
		$type = intval($type);
		$t = time();
		$db->query("insert into d5_log set uname='{$uname}',`key`='{$key}',`type`='{$type}',uid='{$uid}',wdate='{$t}'");
	}

	function getKeySelecter($id,$type)
	{
		global $config;
		$data = file_get_contents($config['sys']['myhome']."index.php?action=keyselecter&id=".$id."&type=".$type);
		
		return $data;
	}


	function addKeyWord($id,$type,$db)
	{
		if(empty($_POST['keys'])) return;
		$data = substr($_POST['keys'],0,-1);
		$list = explode(',',$data);
		$now = $db->vals("select id,word_id from d5_key_mapping where type='{$type}' && data_id='{$id}'");
		$count = count($list)-1;
		
		for($count;$count>=0;$count--)
		{
			$newid = $list[$count];
			if($now)
			{
				//echo "find {$newid} in <br>".print_r($now);
				$find = false;
				for($i=count($now)-1;$i>=0;$i--)
				{
					if($now[$i]['word_id']==$newid)
					{
						$find = true;
						//echo "fined {$newid},".$now[$i]['word_id']." when i is {$i}<br>";
						array_splice($now,$i,1);
						break;
					}
				}

				if($find) continue;	
			}
			//echo "insert new {$newid}<br>";
			$db->query("insert into d5_key_mapping set type='{$type}',word_id='{$newid}',data_id='{$id}'");
			
		}
		if(count($now)>0)
		{
			//echo "delete record.<br>";
			foreach($now as $v) $db->query("delete from d5_key_mapping where id='{$v['id']}'");
		}
	}
?>
