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
	
	$config['db'] = array(
//		'hostname'		=>		'192.168.0.212',								# 主机地址
    'hostname' => "localhost", # 主机地址
    'username' => "ivy", # 主机用户名
    'password' => "952254420", # 登陆密码
	'port'		=> "3306",
    'dbname' => "ivy", # 数据库名
    'encode' => 'utf-8'           # 编码方式
);
	
	$config['sys']=array(
		
		'is_debug'			=>		true,									# 是否为测试
		'module_home'		=>		'modules',								# 模块仓库
		'save_session_path' =>		'/var/wwwroot/agewar2/session',   							# session保存目录
// 		'save_session_path' =>		'/var/wwwroot/agewar/session',   							# session保存目录
		'session_name'		=>		'paila',								# session名
		
// 		'myhome'			=>		'http://59.175.238.109/agewar/',					# 主页地址
    'myhome' => 'http://59.175.238.109/d5power.admin/', # 主页地址
    'small_format' => '.gif', # 全站缩略图生成格式
    'template' => 'default', # 模版名
    'encode' => 'utf-8', # 前台编码
    'lang' => 'chinese', # 前台语言
    'time_format' => 'Y-m-d H:i:s', # 日期格式
    'date_format' => 'Y-m-d', # 日期格式
    'time_area' => 'Asia/Chongqing', # 时区设置
    'name' => '王老师教室-后台', # 标题
    'ver' => '1.0', # 版本
);

	$placelist=array("-","万科","百瑞景","藏龙岛",9=>"网络");
	$subject=array("-","魔方","数独","巧算24");
	$type=array("-","基础班","进阶班","连报班");
$config['page'] = array(
    'default' => '50', # 列表显示数量
);

$config['cache'] = array(
    'box' => 'cache', # 缓村（静态页面）保存目录
);

$config['upload'] = array(
    'max' => 1500, # 允许上传文件的最大尺寸
    'allow' => array('jpg', 'gif', 'jpeg', 'bmp', 'png'), # 允许上传文件的类型
);



// 系统常量定义
define('DEBUG', $config['sys']['is_debug']);
define('IN_SITE', true); //站点状态常量
define('TEMPLATE_PATH', 'template'); //模板总目录常量
define('CACHE_PATH', 'cache_php'); //缓存总目录常量
define('PLATFORM_PATH', '/var2/wwwroot/wedding/');
define('PLATFORM_URL', 'http://wedding.5rpg.com/');
define('UPGRADE_CENTER_PATH', '/var/wwwroot/gftyUpdate/');
define('MEETING_RES_LIB','/var2/wwwroot/meeting/resource_lib/');


// D5CENTER定义
define('D5CENTER', '');
define('D5KEY', 'D5PowerStudio@88753232$%^');
$lang = array(); //系统语言包数组

