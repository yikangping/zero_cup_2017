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
	
	define("ROOT_PATH", '/var/wwwroot/ivy/');
	define("EXT_DIR",'d5power');
	# 系统文件包含 ================================================= 

	require_once(ROOT_PATH."include/config.php");
	require_once(ROOT_PATH."include/d5db.php");
	require_once(ROOT_PATH."include/function.php");
	require_once(ROOT_PATH."include/webconfig.php");
	require_once(ROOT_PATH."include/webfunction.php");
	require_once(ROOT_PATH."include/D5F.php");
	require_once(ROOT_PATH."include/user.php");
	
	# 整站防注入 ===================================================
	if (@get_magic_quotes_gpc ())
	{ 
		$_GET = sec ( $_GET ); 
		$_POST = sec ( $_POST ); 
		$_COOKIE = sec ( $_COOKIE ); 
		$_FILES = sec ( $_FILES ); 
	} 
	$_SERVER = sec ( $_SERVER );
	
	# 驱动文件包含 =================================================

	
	# 语言包包含 ================================================
	
	# 时区设置 =================================================
	date_default_timezone_set($config['sys']['time_area']);
	
	# 系统相关设置 ================================================
	ini_set('display_errors',1);            //错误信息
	ini_set('display_startup_errors',1);    //php启动错误信息
	
	
	if(isset($_GET['debug']) || @$_COOKIE['debug']=='1')
	{
		error_reporting(-1);
		setcookie('debug','1',time()+60);
	}else{
		error_reporting("E_ALL ^ E_NOTICE");
	}
	//error_reporting("E_ALL ^ E_NOTICE");                      																		# 错误报告模式
	//error_reporting("E_ALL");
	//header('Access-Control-Allow-Origin:*.5rpg.com');
	
	header("Content-Type: text/html; charset={$config['sys']['encode']}");
	$template = "templates/{$GLOBALS['config']['sys']['template']}";

	# SESSION设置 ================================================
	
	//session_name($config['sys']['session_name']);			// 设置session名
	//save_session_path();									// 设置session保存目录
	if(!empty($_POST['sessionid'])) session_id($_POST['sessionid']);
	session_start();
	
	# 其他处理 ================================================ 
	
	# SUBMIT处理模式 ===========================================
	
	$do=empty($_POST['do']) ? $_GET['do'] : $_POST['do'];

	// 分页设置 ================================================
	$_GET['page'] = empty($_GET['page']) ? $_POST['page'] : $_GET['page'] ;
	$_GET['page'] = empty($_GET['page']) ? 1 : intval($_GET['page']);	
	$page = $_GET['page'];
	$_GET['per'] = empty($_GET['per']) ? $_POST['per'] : $_GET['per'] ;
	$_GET['per'] = empty($_GET['per']) ? $config['page']['default'] : intval($_GET['per']);
	$per = $_GET['per'];
	
	$nowrecord = ($_GET['page']-1)*$per;
	
	// GD支持 =================================================
	$useGD=function_exists('imagecreate');
	
	function __autoload($class_name)
	{
		$class_path = ROOT_PATH.'core/'.$class_name.'.php';
		$util_class_path = ROOT_PATH.'core/d5power/'.$class_name.'.php';
		$ext_class_path = ROOT_PATH.'core/utils/'.EXT_DIR.'/'.$class_name.'.php';
		if(file_exists($class_path)){
			include_once($class_path);
		}else if(file_exists($util_class_path)){
			include_once($util_class_path);
		}else{
			echo '[AutoLoad] Can not found class'.$class_name.' '.__FILE__;
			die();
		}
		/*
		 else if(!empty(EXT_DIR) && file_exists($ext_class_path)){
			include_once($ext_class_path);
		}
		 * */
	}
?>