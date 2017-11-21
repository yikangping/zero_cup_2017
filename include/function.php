<?php

	/** *********************************************
	 *
	 *	D5Framework
	 *	D5Power Studio
	 *	author:Benmouse		date:2007-10-14
	 *	Update:2008-06-10	Ver:1.0
	 *	第五动力工作室 - D5轻型开发框架
	 *	
	 *
	 **********************************************/
   // 将数据表中的数据输出成下拉列表的形式
	function Option($db,$d5f,$table,$where,$selected)
	{
		$list = $db->vals("select * from {$table} ".$where." order by id ",true);
		$result = '';
		if($list)
		{
			$d5f->loop('Option');
			foreach($list as $k=>$v)
			{
				$v['_selected'] = $selected==$v['id'] ? 'selected' : '';
				$d5f->p($v);
				$result.=$d5f->out();
			}
		}
		return $result;
	}
	
	function Option2($db,$d5f,$option,$table,$where,$selected)
	{
	
		$list = $db->vals("select $option as _name,id from {$table} ".$where."order by id ",true);
		$result = '';
		if($list)
		{
			$d5f->loop('Option');
			foreach($list as $k=>$v)
			{
				$v['_selected'] = $selected==$v['id'] ? 'selected' : '';
				$d5f->p($v);
				$result.=$d5f->out();
			}
		}
		return $result;
	}
	

	function getSelect($id,$nowid,$conf,$d5f,$onchange='')
	{
		$select = "<select id='{$id}' name='{$id}' onchange=\"{$onchange}\">";
		$d5f->loop('Options');
		foreach($conf as $key=>$value)
		{
			$arr = array(
				'v'		=>	$value,
				'k'		=>	$key,
				'selected'	=>	$nowid==$key ? 'selected' : '',
			);
			
			$d5f->p($arr);
			$select.=$d5f->out();
		}
		$select.="</select>";
		return $select;
	}


	// 设置SESSION路径=============================================================
	
	function save_session_path()
	{
		session_save_path($GLOBALS['config']['sys']['save_session_path']);
	}
	
	// 密码加密函数================================================================
	
	function encrypt($pass)
	{
		return md5($pass);
	}
	 
	// 字符检测函数================================================================
	
	function CharTest($tester,$mod){
	   if($mod=="" || $mod==NULL || $mod==0){
		  #检测字符内容是否为空
		  
		  if($tester=="" || $tester==NULL){
			  return false;
		  }
		  
	   }else if($mod==1){
	   
		  #检测字符是否包含特殊字符
		  $errorChar=">|<|&";  #特殊字符的正则表达式
		  if(ereg($errorChar,$tester)==1 || $tester=="" || $tester==NULL){
			 return true;
		  }
		  
	   }else if($mod==2){
	   
		  #检测字符是否只包含数字
		  $errorChar="([0-9])";  #数字的正则表达式
		  if(ereg($errorChar,$tester)==1){
			 return false;
		  }else{
			 return true;
		  }
		  
	   }else{
		  #字符合法
		  return true;
		  
	   }
	   
	}
	
	
	#截取函数定义 ========================================
	function msubstr($str, $start, $len, $backfllow='...',$code='UTF-8')
	{
		if(strtolower($code)=='utf-8')
		{
			$pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all($pa, $str, $tmpstr);
			if(count($tmpstr[0]) - $start > $len) return join('', array_slice($tmpstr[0], $start, $len)).$backfllow;
			return join('', array_slice($tmpstr[0], $start, $len));
		}else{
			$tmpstr = "";
			$strlen = $start + $len; 
			for($i = 0; $i < $strlen; $i++)
			{ 
				if(ord(substr($str, $i, 1)) > 0xa0)
				{ 
					$tmpstr .= substr($str, $i, 2); 
					$i++; 
				}else{
					$tmpstr .= substr($str, $i, 1); 
				}
			} 
			
			if(strlen($str)>$len && $sl=="")
			{
				return $tmpstr.$backfllow; 
			}else{
				return $tmpstr;
			}
		}
	}
	
	#信息处理函数 =========================================
	function msg($msg,$msg_type='',$back="")
	{
		global $webservice;
		if($webservice) back(D5Error::ERR_UNDEFINE_CMD,$msg);
		$mainpath="../";
		
		if($msg_type=='') $msg_type=$GLOBALS['lang']['sys']['msg_default_title'];
		if($msg_type=='SMALL')
		{
			$back = $back=='' ? 'defaultCallback()' : $back;
			require_once(makeTemp("error_small"));
		}else{
			$back = $back=='' ? 'javascript:window.history.go(-1)' : $back;
			require_once(makeTemp("error"));
		}
		die();
	}
	
	#模块检测函数 =========================================
	
	function module_is_exists()
	{
		$_path="{$GLOBALS['config']['sys']['module_home']}/{$GLOBALS['module']}";
		if(is_dir($_path))
		{
			return $_path;
		}else{
			msg($GLOBALS['module'].$GLOBALS['lang']['sys']['no_module'],"ERROR");
		}
	}
	
	#驱动检测函数 =========================================
	
	function action_is_exists()
	{
		module_is_exists();
		$_path = "{$GLOBALS['config']['sys']['module_home']}/{$GLOBALS['module']}/{$GLOBALS['action']}.php";
		if(file_exists($_path))
		{
			return $_path;
		}else{
			msg($GLOBALS['action'].$GLOBALS['lang']['sys']['no_action'],"ERROR");
		}	
	}
	
	#后台管理驱动检测函数 =========================================
	
	function admin_action_is_exists()
	{
		$_path = "admin/{$GLOBALS['action']}.php";
		if(file_exists($_path))
		{
			return $_path;
		}else{
			msg($GLOBALS['action'].$GLOBALS['lang']['sys']['no_action'],"ERROR");
		}	
	}
	
	#首页内容驱动检测函数 ==================================
	
	function home_driver_is_exists($driver)
	{
		$_path="{$GLOBALS['config']['sys']['module_home']}/{$driver}";
		
		if(file_exists($_path))
		{
			return $_path;
		}else{
			msg($driver.$GLOBALS['lang']['sys']['no_home_driver'],"ERROR");
		}
	}
	
	
	#主文件生成函数 =======================================
	
	function makeTemp($fname="index",$ftype=0)
	{
		switch($ftype)
		{
			case 0:
			
				// 首页
				$path="{$GLOBALS['template']}/{$fname}.html";
				break;
				
			case 1:
				
				// 后台
				$path="templates/system/admin/{$fname}.html";
				break;
			case 2:
			
				break;
				
			case 3:
				
				// 用户面版
				$path="templates/system/member_admin/{$fname}.html";
				break;
				
			default:
				
				break;
		}
		
		if(file_exists($path))
		{
			return $path;
		}else{
			msg($fname.$GLOBALS['lang']['sys']['no_template'],"ERROR");
		}
	}
	//翻页函数 ======================================
	function pageinfo($allnum,$fun="",$per=0,$var=array("page","ten"),$level=0,$everytime=10)
	{
	
		//参数说明：$allnum 总共多少条记录 $fun AJAX调用 $per 每页显示多少条记录
		//参数说明：$level 显示级别 0 显示上十页下十页 1 在0的基础上显示上一页下一页 2 在1的基础上显示跳转 3 在2的基础上显示统计信息
		//参数说明：$everytime 每次显示多少页
	
		$lang['all']="共";
		$lang['pic']="页";
		$lang['prv']="上一页";
		$lang['nex']="下一页";
		$lang['jump']="跳转";
		
		//定义标志变量
		$page_var=$var[0];
		$ten_var=$var[1];
		
		if($per==0) $per=$GLOBALS['config']['page']['default'];
		$page=empty($_GET[$page_var]) ? 1 : $_GET[$page_var];
		$_GET[$ten_var]=empty($_GET[$ten_var]) ? (ceil($page/$everytime)-1) : intval($_GET[$ten_var]);
		$ten=$_GET[$ten_var] < 0 ? 0 : $_GET[$ten_var];
		if($ten>0 && $page==1) $page=$ten*$everytime+1;
		$allpage=ceil($allnum/$per);
	
		if($allpage<=1) return;
		
		$pageinfo="<div id='pageinfo'>";
		//计算起始页
		
		if($allpage<($page+$everytime))
		{
			$startPage=$allpage-$everytime+1;
		}else{
			$startPage=$page;
		}
		
		foreach($_GET as $key=>$value)
		{
			if($key=='buildPage') continue;
			if($key!="{$page_var}" && $key!="{$ten_var}")
			{
				if(!empty($value)) $getinfo.="{$key}={$value}&";
			}
		}
		
		//fun定义
		if($fun=="") $fun="d5_ajax_loadpage";
		
		if($level>=3) $pageinfo.="{$lang['all']}{$allpage}{$lang['pic']}";
		if($level>=1)
		{
			if($page>1)
			{
				$pageinfo.="<a href='#' onclick=\"{$fun}('{$getinfo}{$page_var}=".($page-1)."&{$ten_var}={$ten}');return false;\">{$lang['prv']}</a>";
			}else{
				$pageinfo.="<a>{$lang['prv']}</a>";
			}
		}
	
		if($level>=0)
		{
			if($ten>0)
			{
				$pageinfo.="<a href='#' onclick=\"{$fun}('{$getinfo}{$page_var}=".(($ten-1)*$everytime+1)."&{$ten_var}=".($ten-1)."');return false;\"><<</a>";
			}else if($page>1 && $ten==0 && $level==0){
				//当不超过10页的时候，上10页相当与上翻
				$pageinfo.="<a href='#' onclick=\"{$fun}('{$getinfo}{$page_var}=".($page-1)."&{$ten_var}={$ten}')\"><<</a>";
			}else{
				//$pageinfo.="<a><<</a>";
			}
		}
		for($i=0;$i<$everytime;$i++)
		{
			$thispage=$startPage+$i;
			if($thispage<=0) continue;
			if($thispage==$page)
			{
				$pageinfo.="<a class='selected'>{$thispage}</a>";
			}else{
				$pageinfo.="<a href='#' onclick=\"{$fun}('{$getinfo}{$page_var}={$thispage}&{$ten_var}={$ten}');return false;\">{$thispage}</a>";
			}
		}
	
		if($level>=0)
		{
			if(($ten+2)*$per>$allpage)
			{
				//$pageinfo.="<a>>></a>";
			}else{
				$pageinfo.="<a href='#' onclick=\"{$fun}('{$getinfo}{$page_var}=".(($ten+1)*$everytime+1)."&{$ten_var}=".($ten+1)."');return false;\">>></a>";
			}
			
		}
		
		
		if($level>=1)
		{
			if($page<$allpage)
			{
				$pageinfo.="<a href='#' onclick=\"{$fun}('{$getinfo}{$page_var}=".($page+1)."&{$ten_var}={$ten}');return false;\">{$lang['nex']}</a>";
			}else{
				$pageinfo.="<a>{$lang['nex']}</a>";
			}
		}
		
		
		if($level>=2) $pageinfo.="
			<input name=\"textfield\" type=\"text\" size=\"3\" id=\"jumpkey\" style=\"border-bottom: 1px solid #CCCCCC; border-left: 1px solid #FFFFFF; border-right: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF\" />
			<span style='cursor:hand;' onclick=\"window.location='{$_SERVER['PHP_SELF']}?{$getinfo}&{$page_var}='+document.getElementById('jumpkey').value\">{$lang['jump']}</span>	
		";
		
		//最简模式
		
		if($level<0)
		{
			$pageinfo="<div id='pageinfo'>";
			if($page>1)
			{
				$pageinfo.="<a href='#' onclick=\"{$fun}('{$getinfo}{$page_var}=".($page_var-1)."')\"><<</a>";
			}else{
				$pageinfo.="<a><<</a>";
			}
			if($page<$allpage)
			{
				$pageinfo.="<a href='#' onclick=\"{$fun}('{$getinfo}{$page_var}=".($page_var+1)."')\">>></a>";
			}else{
				$pageinfo.="<a>>></a>";
			}
		}
		$pageinfo.="</div>";
		if($fun=="d5_ajax_loadpage")
		{
			$pageinfo.="
			<script language='javascript'>
			function d5_ajax_loadpage(sendvar)
			{
				window.location='{$_SERVER['PHP_SELF']}?'+sendvar;
				return false;
			}
			</script>
			";
		}
		
		return $pageinfo;
	}
	
	//是否当前页面函数 ======================================
	function isme($myhttp)
	{
		$http=explode("/",$_SERVER['PHP_SELF']);
		$count=count($http);
		$http="../".$http[($count-2)]."/".$http[($count-1)].getGet();
		if($myhttp==$http)
		{
			return true;
		}else{
			return false;
		}
	}
	
	function getGet()
	{
		if(!empty($_GET))$getinfo="?";
		foreach($_GET as $key=>$value)
		{
			$getinfo.=$key."=".$value."&";
		}
		$getinfo=substr($getinfo,0,strlen($getinfo)-1);
		return $getinfo;
	}
	
	//是否登陆判断 =========================================
	function islogin($type="member")
	{
		switch($type)
		{
			case "member":
				if(empty($_SESSION['member']))
				{
					return false;
				}else{
					return true;
				}
				break;
			case "admin":
			default: break;
		}
	}

	//删除文件夹函数=========================================
	
	function delDir($mydir,$del_dir=true)
	{
		$d = dir($mydir);
		$i = 0;
		if(is_dir($mydir))
		{
			while (false !==($obj = $d->read()))
			{
				if ($obj =='.' || $obj=='..')
				{
				  continue;
				}
		
				$tmp_dir = $mydir.'/'.$obj;
		
				if (!is_dir($tmp_dir))
				{
		
					unlink($tmp_dir);
				}
			
				$i++;
		
			}
			
			if($del_dir)
			{
				$result=rmdir($mydir);
			}else{
				$result=true;
			}			
		}else{
			$result=false;
		}
		$d->close();
		return $result;
	}
	
	//二级域名判断
	function myDomain()
	{
		$host=explode(".",$_SERVER['HTTP_HOST']);		//主机支持泛域名解析
		$host=$host[0];
			
		$url=explode(".",$_SERVER['HTTP_REFERER']);		//主机不支持泛域名解析，采用URL转发
		$url=$url[0];
		$url=str_replace("http://","",$url);
		
		$username=($host=="www") ? $url : $host;				//取得关键字
		return $username;
	}
	
	//HTML语法剔除函数===============================================================
	function HTMLcut($tester){
	   $tester=str_replace("<","",$tester);
	   $tester=str_replace(">","",$tester);
	   $tester=str_replace("&","",$tester);
	   $tester=str_replace("\"","",$tester);
	   return $tester;
	}
	
	//取得MYSQL版本号
	function get_db_ver()
	{
		$_=new db_mouse;
		$db_ver=$_->db_select("select version()");
		$db_ver=floatval(substr($db_ver['version()'],0,3));										//取得MYSQL版本号
		return $db_ver;
	}
	
	//目录读取函数
	function loadDir($mydir,&$flist,$replace='',$arr=array("swf","gif","jpg"))
	{
		$d = dir($mydir);
		$i = 0;
		while (false !==($obj = $d->read()))
		{
			if ($obj =='.' || $obj=='..')  continue;
			$tmp_dir = $mydir.'/'.$obj;
			if (!is_dir($tmp_dir))
			{
				if($flist!==null)
				{
					$list_path = $replace=='' ? $tmp_dir : str_replace($replace, '', $tmp_dir);
					array_push($flist, $list_path);
				}
				if(count($arr)>0 && in_array(strtolower(substr($obj,-3)), $arr))
				{
					$filelist[$i]=$obj;
				}else if($arr==null){
					$filelist[$i]=$obj;
				}
			}else{
				$filelist[$i] = array($obj,loadDir($tmp_dir,$flist,$replace,$arr));
			}
			$i++;
		}
	
		$d->close();
		return $filelist;
	}
	
	// 获得扩展名函数
	
	function get_extname($fname)
	{
		$temp=explode(".",$fname);
		$where=count($temp)-1;
		return $temp[$where];
	}
	
	// 获得纯粹文件名（不含扩展名）
	
	function get_truename($fname)
	{
		$temp=explode(".",$fname);
		
		for($i=0;$i<count($temp)-1;$i++)
		{	
			$where.=$temp[$i];
			if($i!=count($temp)-2) $where.=".";
		}
		return $where;
	}
	
	// 生成缩略图及图象格式转换函数
	// @source 来源文件 @target 目标文件 @width 宽度 @height 高度 @format 转换格式
	// 若按某一特定尺寸（固定高/固定宽）请将另外一个置0
	function D5imger($source,$target,$change_size=false,$width=0,$height=0,$format="jpg")
	{
		// 转换为小写
		// $source=strtolower($source);
		// 获得扩展名
		$ext_name=get_extname($source);
		switch($ext_name)
		{
			case "jpg":
			case "jpeg":
				$temp_img=imagecreatefromjpeg($source);
				break;
			case "png":
				$temp_img=imagecreatefrompng($source);
				break;
			case "gif":
				$temp_img=imagecreatefromgif($source);
				break;
			default:$ext_name_error=true;break;
		}
		
		if($ext_name_error) return false;
		
		$old_width=imagesx($temp_img);
		$old_height=imagesy($temp_img);

		// 缩略图
		if($change_size)
		{
			// 指定宽/高
			if($width==0 && $height>0)
			{
				// 指定高
				$width = $old_width/$old_height*height;
			}else if($height==0 && $width>0){
				// 指定宽
				$height = $width/($old_width/$old_height);
			}else{
				
				// 计算比例
				$source_ratio=$old_width/$old_height;
				$target_ratio=$width/$height;
				
				// 假定高度不变，计算宽度
				$temp_width=$old_height*$target_ratio;
				
				if($temp_width<=$old_width){
					// 宽度未超出范围，高度优先
					$old_width  = $old_height*$target_ratio;
					$old_height = $old_height;
				}else{
					// 宽度超出范围，以宽度优先
					$old_width  = $old_width;
					$old_height = $old_width/$target_ratio;
				}
				
			}
		}else{
			// 不生成缩略图
			$width=$old_width;
			$height=$old_height;
		}
		// die($old_width.":".$old_height."+".$width.":".$height."+".$source_ratio.":".$target_ratio);
		// 创建新的图象
		$new_img = imagecreatetruecolor($width,$height);
		imagecopyresampled($new_img,$temp_img,0,0,0,0,$width,$height,$old_width,$old_height);
		
		$target_ext_name=get_extname(strtolower($target));
		
		switch($ext_name)
		{
			case "jpg":
			case "jpeg":
				imagejpeg($new_img,$target);
				break;
			case "png":
				imagepng($new_img,$target);
				break;
			case "gif":
				imagegif($new_img,$target);
				break;
			default:$ext_name_error=true;break;
		}
		
	}
	
	// 根据给定图片和一个固定尺寸，按照比例进行缩放，获得新的图片尺寸
	function getCubeImg($img,$default_size)
	{
		if(!file_exists($img))
		{
			$result = array(
				"w"		=>	0,
				"h"		=>	0,
			);
		}else{
			$img_size=@getimagesize($img);
			if($img_size[0]>$img_size[1])
			{
				$img_width=$default_size;
				$img_height = ceil($img_width/($img_size[0]/$img_size[1]));
			}else if($img_size[0]<$img_size[1]){
				$img_height=$default_size;
				$img_width=ceil($img_height*($img_size[0]/$img_size[1]));
			}else{
				$img_width=$default_size;
				$img_height=$default_size;
			}
			
			$result = array(
				"w"		=>	$img_width,
				"h"		=>	$img_height,
			);
		}
		return $result;
	}
	
	// 检查文件夹是否存在，如果不存在，则创建
	function is_mkdir($path)
	{
		if(!is_dir($path))
		{
			$r = smkdir($path);
			if(!$r) msg('系统无权建立目录','SMALL');
			return true;
		}else{
			return true;
		}
	}
	
	// 超级文件夹建立器
	function smkdir($path)
	{
		$temp = explode('/',$path);
		$p = '';
		$result = true;
		foreach($temp as $value)
		{
			$p.=$value.'/';
			if(!is_dir($p)) $result=$result&&@mkdir($p);
		}
		return $result;
	}
	
	// 文件上传
	function uploadFile($filed,$target='userdata/',$rename='time',$maxsize=NULL,$allowtype=NULL)
	{
		if(empty($allowtype)) $allowtype=$GLOBALS['config']['upload']['allow'];
		if(empty($maxsize))	$maxsize = $GLOBALS['config']['upload']['max'];
		$upload_file_name = $rename=='time' ? time().rand(0,99).'_'.rand(0,99) : $rename;
				
		$maxsize = intval($maxsize);
		if(substr($target,-1,1)!='/') $target.='/';
				
		if($_FILES[$filed]['size']>0)
		{
			// 文件判断
			$upload_file=$_FILES[$filed]['tmp_name'];
			// 获取扩展名
			$extname=get_extname($_FILES[$filed]['name']);
			// 文件类型判断
			if(!in_array(strtolower($extname),$allowtype)) msg($extname.'没有被系统列为允许上传的格式','SMALL');
			// 新文件名定义
			$upload_file_name=$upload_file_name.".".$extname;
			// 文件大小判断
			$file_max=$maxsize*1024;
			if($_FILES[$filed]['size']>$file_max) msg('您的图片大小为'.intval($_FILES[$filed]['size']/1024).'K，超过了系统允许的最大尺寸'.$maxsize.'K','SMALL');
			// 上传目录检测
			if(!is_dir($target))
			{
				$r=smkdir($target);
				if(!$r) msg('系统无权建立目录'.$target.'，请与系统管理员联系','SMALL');
			}
					
					
			// 开始上传
			$target_path = $target.$upload_file_name;
			move_uploaded_file($upload_file,$target_path) or msg('上传文件失败，权限不足','SMALL');
			return $target_path;
		}else{
			return '';
		}
	}
	
	// 缓存路径生成函数
	// global_vars为在生成缓存后依然需要显示的变量
	function loadCache($save_name='',$save_path='',$global_vars=array())
	{
		global $module;
		global $action;
		global $config;
		global $lang;
		
		if(count($global_vars)>0)
		{
			foreach($global_vars as $key)
			{
				global $$key;
			}
		}
		
		// 若save_name为空，则以action为缓存文件名
		$save_name = $save_name=="" ? "{$action}.html" : $save_name;
		$save_path = $save_path=='' ? "{$config['cache']['box']}/{$module}/" : $save_path;
		
		if(!is_dir($save_path)) msg("{$lang['sys']['no_folder']}{$save_path}");
		
		$cache = "{$save_path}/{$save_name}";
		if(intval($_GET['buildPage'])==0 && file_exists($cache))
		{
			require_once($cache);
			die();
		}
	}

	// 静态页面生成函数
	function buildPage($module,$action,$var='',$save_name='',$save_path='')
	{
		global $config;
		global $lang;
		
		// 若save_name为空，则以action为缓存文件名
		$save_name = $save_name=="" ? "{$action}.html" : $save_name;
		// 判断保存路径
		if($save_path=="")
		{
			if(!is_mkdir("{$config['cache']['box']}/{$module}/")) msg("{$lang['sys']['can_not_create_folder']}{$config['cache']['box']}/{$module}/");
			$save_path = "{$config['cache']['box']}/{$module}/";
		}else{
			if(!is_dir($save_path))
			{
				if(!smkdir($save_path)) msg("{$lang['sys']['no_folder']}{$save_path}");
			}
		}
		
		$save_path = substr($save_path,-1,1)!="/" ? $save_path."/" : $save_path;
		$target_name= $save_path.$save_name;

		if(file_exists($target_name)) unlink($target_name) or msg("{$lang['sys']['can_not_overwrite']}{$target_name}");
		
		$f=file_get_contents("{$config['sys']['myhome']}index.php?module={$module}&action={$action}&buildPage=1&{$var}") or msg("{$lang['sys']['can_not_load_cache_source']}({$config['sys']['module_home']}/{$module}/{$action}.php");
		fwrite(fopen($target_name,"w"),$f) or msg("{$lang['sys']['can_not_overwrite']}{$target_name}");
		
		// 载入缓存
		require_once($target_name);
		die();
	}
	
	// 检查缓存是否存在
	function checkCache($module='',$action='',$target='')
	{
		global $config;
		if($module=='' && $action=='' && $target=='') return false;
		
		$target = $target=='' ? "{$config['cache']['box']}/{$module}/{$action}.html" : $target;
		
		// 当文件存在，或buildPage标记为1时，返回真（不需要刷新换存）
		$result = file_exists($target) || intval($_GET['buildPage'])==1;
		return $result;
	}
	
	// 两者取一(You or Me)
	function yom($y,$m)
	{
		$y = empty($y) ? $m : $y;
		return $y;
	}
	
	function fmsg($msg="",$alert=false,$goto='')
	{
		// 如果设置了goto，则自动跳转
		if($goto!='') $gotoScript = "try{changePage('{$goto}');}catch(e){try{parent.changePage('{$goto}');}catch(e){}}";
		
		
		
		// 如果设置了alert为假，则不弹出消息提示，采用系统消息函数MSG进行通知，并在5秒后自动关闭
		if(!$alert)
		{
			$msgScript = "try{MSG('{$msg}');setTimeout('MSG()',5000)}catch(e){try{parent.MSG('{$msg}');parent.setTimeout('MSG()',5000)}catch(e){}}";
		}else{
		// 如果设置了alert为真，则弹出消息提示
			$msgScript = "try{MSG('');}catch(e){try{parent.MSG('');}catch(e){}}";
			$alertScript = "parent.alert('{$msg}');";
		}
		
		die("<script language='javascript'>
		{$alertScript}
		{$msgScript}
		{$gotoScript}
		</script>");
	}
	
	/**
	 *	远程数据读取
	 *	
	 *	从$url参数为地址的远程页面抓取数据
	 *	
	 */
	function getRemoteContent($url,$source_encode='')
	{
		if($source_encode=='') $source_encode = $GLOBALS['config']['sys']['encode'];
		$i=0;
		do
		{
			if($i>10) break;
			$data = @file_get_contents($url);
			if(!empty($data)) break;
			$i++;
		}while(true);
		
		if($i>10)
		{
			return '';
		}else{
			if($source_encode!=$GLOBALS['config']['sys']['encode'] && function_exists("iconv"))
			{
				$data = iconv($source_encode,$GLOBALS['config']['sys']['encode'],$data);
			}
			return $data;
		}
	}
	
	/**
	 *	提取某段文字内的远程文件并下载到本地
	 *	@param src 源文件
	 *	@param target 目标路径
	 *	@param mode 下载模式 默认为img 自动下载全部图片
	 *	return array('downloaded'->成功下载记数,'undownload'->未成功下载记数,'result'->成功下载的源地址与新地址的配对数组)
	 */
	function remoteDown($src,$target='userdata/remote/',$mode='img')
	{
		if(empty($src)) return array();
		
		// 驱除魔法引号
		$src = stripslashes($src);
		$downlist = array();
		$undownload = 0;
		$downloaded = 0;
		$result = array();
		
		switch($mode)
		{
			case 'img':
				// 正则匹配所有图片地址
				preg_match_all("/src=[\"|'|\s]{0,}(http:\/\/([^>]*)\.(gif|jpg|png))/isU",$src,$downlist);
				break;
			default:	
				return array();
				break;
		}
		
		foreach($downlist[1] as $key=>$value)
		{
			$data = getRemoteContent($value);
			
			if(empty($data))
			{
				$undownload++;
				continue;
			}else{
				$file = time().'_'.$key.'.'.get_extname($value);
				if(!is_dir($target)) smkdir($target) or msg('系统无权建立目录'.$target);

				$targetfile = substr($target,-1)!='/' ? $target.'/'.$file : $target.$file;
				$fp = @fopen($targetfile,'w');
				fwrite($fp,$data);
				fclose($fp);
				$downloaded++;
				array_push($result,array($value,$targetfile));
			}
		}
		
		$return = array(
		'downloaded'		=>	$downloaded,
		'undownload'		=>	$undownload,
		'result'				=>	$result,
		);
		
		return $return;
	}
	
	
	// 呼叫父级JS
	function parentCall($action)
	{
		die("<script language='javascript'>{$action}</script>");
	}
	
	/**
	 *	获取远程数据
	 *	@param	$ip			主机域名
	 *	@param	$condition	变量内容
	 *	@param 	$url			文件地址
	 */
	function fget_contents($ip,$condition,$url)
	{  
		$req=$condition;  
		$header .= "POST $url HTTP/1.0\r\n";  
		$header .= "User-Agent: Mozilla 4.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";  
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n{$req}";  
      
		$fp = fsockopen($ip,80, $errno, $errstr,30); 
		$res=9;
		if(!$fp)
		{  
			$res = 'NOSERVER';
		}else{
			while (!feof($fp))
			{  
				fputs($fp, $header);  
				$res = fgets ($fp, 1024);  
			}  
		}  
		fclose ($fp);  
  
		return $res;  
	}  
        
        
        	# 防注入函数 =============================== 
	
	/**
	 * 统一处理输入变量
	 * @param unknown_type $array
	 */
	function sec(&$array)
	{ 
		//如果是数组，遍历数组，递归调用 
		if (is_array ( $array ))
		{ 
			foreach ( $array as $k => $v )
			{ 
				$array [$k] = sec ( $v ); 
			} 
		} else if (is_string ( $array )) { 
			//使用addslashes函数来处理 
			$array = addslashes ( $array ); 
		} else if (is_numeric ( $array )) { 
			$array = intval ( $array ); 
		} 
		return $array; 
	}
	
	/**
	 * 数字检查
	 * @param unknown_type $array
	 */
	function num_check($id)
	{ 
		if (! $id)
		{ 
			msg( '安全验证：参数不能为空！' ); 
		}else if (inject_check ( $id )) { 
			msg( '安全验证：非法参数' ); 
		}else if (! is_numeric ( $id )) { 
			msg( '安全验证：非法参数' ); 
		} 
		//数字判断 
		$id = intval ( $id ); 
		//整型化 
		return $id; 
	}
	
	
	/**
	 * 字符串检查
	 * @param unknown_type $array
	 */
	function str_check($str)
	{ 
		if (inject_check ( $str )) { 
			msg( '安全验证：非法参数' ); 
		} 
		//注入判断 
		$str = htmlspecialchars ( $str ); 
		//转换html 
		return $str; 
	}
	
	/**
	 * 搜索过滤
	 * @param unknown_type $array
	 */
	function search_check($str)
	{ 
		$str = str_replace ( "_", "\_", $str ); 
		//把"_"过滤掉 
		$str = str_replace ( "%", "\%", $str ); 
		//把"%"过滤掉 
		$str = htmlspecialchars ( $str ); 
		//转换html 
		return $str; 
	}
	
	
	/**
	 * 表单过滤
	 * @param unknown_type $array
	 */
	function post_check($str, $min, $max)
	{ 
		if (isset ( $min ) && strlen ( $str ) < $min)
		{ 
			die ( '最少$min字节' ); 
		} else if (isset ( $max ) && strlen ( $str ) > $max){ 
			die ( '最多$max字节' ); 
		} 
		return stripslashes_array ( $str ); 
	}
	
	/**
	 * 防注入
	 * @param unknown_type $array
	 */
	function inject_check($sql_str)
	{ 
		return eregi ( 'select|inert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|UNION|into|load_file|outfile', $sql_str ); 
	}
	
	function stripslashes_array(&$array)
	{ 
		if (is_array ( $array ))
		{ 
			foreach ( $array as $k => $v ) $array [$k] = stripslashes_array ( $v ); 
		} else if (is_string ( $array )) { 
			$array = stripslashes ( $array ); 
		} 
		return $array; 
	}
	
	function back($cmd,$data=null)
	{
		$result = array('code'=>$cmd,'data'=>$data,'cmd'=>$_GET['do']);
		if($cmd<0) $result['info'] = debug_backtrace();
		die(json_encode($result));
	}
?>
