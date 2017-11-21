<?php
	
	/**
	 *	
	 *	D5Power Studio D5Framework Main class
	 *	ver 2.0
	 *	Author:D5Power.Howard
	 *	Build for loop html in specile template
	 *	
	 *	example: 	$d5f = new D5F();
	 *				$d5f->loop("loopUserinfo",makeTemp("looptest"));
	 *				$d5f->parse("userdate","Howard D5power");
	 *				$loopUserinfo.=$d5f->out();
	 *
	 *
	 *	html:		<!-- loopUserinfo
	 *				<div>{$userdate}</div>
	 *				loopUserinfo -->
	 */
	
	
	class D5F
	{
		var $template;
		var $lable;
		var $looper;
		var $loopbox;
		var $files;
		var $t;				# file content for template output at the end.
		var $useCache;		# if this var is true,D5F will write the cache file into configed cache path.
		
		public function D5F($lable=NULL,$template=NULL)
		{
			if(!empty($template)) $this->loop($lable,$template,true);
		}
		
		/**
		 *	Search context which started by '<!-- lablename' and terminated by 'lablename -->'
		 *	And setup the context to loop.
		 *	$lable - lablename $template - which template will be used
		 */
		public function loop($lable='',$template='',$isinit=false)
		{
			if($template!='')
			{
				# 取文件
				if(!$fp = fopen($template,"r")) msg("File not exist.");
				$this->files = fread($fp,filesize($template));
				fclose($fp);
				
				# 设置模版主文件
				if($isinit) $this->t = $this->files;
				if(empty($lable)) return;
			}else{
				if(empty($this->files)) msg('Please tell D5F which template be needed.');
			}
			$reg = "/\<\!\-\- {$lable}(.*){$lable} \-\-\>/is";
			preg_match($reg,$this->files,$result);
			$this->loopbox = $result[1];
			$this->looper = $this->loopbox;
		}
		
		/**
		 *	Template parse
		 *	
		 *	
		 *	
		 */
		public function parse($lable,$value)
		{
			if(empty($this->loopbox)) msg('Please run loop public function first to setup the loop templates.');
			$this->looper = str_replace('{$'.$lable.'}',$value,$this->looper);
		}
		
		/**
		 *	Advance template parse
		 *
		 *
		 */
		public function p($lable,$value=NULL)
		{
			global $module,$action;
			if(empty($this->loopbox)) msg('Please run loop public function first to setup the loop templates.');
			if(is_array($lable) && is_array($value))
			{
				# 自动编译module,action和template系统变量
				array_push($lable,'module');
				array_push($lable,'action');
				array_push($lable,'template');
				
				array_push($value,$module);
				array_push($value,$action);
				array_push($value,$GLOBALS['config']['sys']['template']);
				# 调用模式一：双数组调用
				foreach($lable as $key=>$l)
				{
					$this->parse($l,$value[$key]);
				}
				
			}else if(is_array($lable) && $value==NULL){
				
				# 自动编译module和action
				$lable['module']=$module;
				$lable['action']=$action;
				$lable['template']=$GLOBALS['config']['sys']['template'];
				
				# 调用模式二：单数组调用
				foreach($lable as $key=>$l)
				{
					$this->parse($key,$l);
				}
			
			}else if(!is_array($lable) && !is_array($value)){
				
				# 调用模式三:普通字符串调用
				$this->parse($lable,$value);
			}else{
				msg('Wrong prase mode.Please check your code.');
			}
		}
		
		/**
		 *	
		 *	output
		 *	
		 *	
		 */
		public function out()
		{
			$result=$this->looper;
			$this->looper=$this->loopbox;
			return $result;
		}
		
		/**
		 *	template
		 *	
		 *	
		 */
		public function template()
		{
			global $module,$action,$config;
			/*
			if(!checkCache('','',"{$config['cache']['box']}/{$module}/{$action}.html"))
			{
				buildPage($module,$action,'',$action.'.php');
			}else{
				$is_cache = " 现在是缓存查看.";
				loadCache($action.'.php');
			}
			*/
			$file_cache = "{$config['cache']['box']}/{$module}/{$action}/{$action}.php";
			

			if($this->useCache)
			{
				if(!file_exists($file_cache))
				{
					$template = $this->parseTemplate();
					$path = "{$config['cache']['box']}/{$module}/{$action}";
					if(smkdir($path))
					{
						$fp = fopen($file_cache,'w');
						fwrite($fp,$template);
						fclose($fp);
					}else{
						msg('Can not make catch dir!');
					}
				}
			}else{
				
			}
			return $file_cache;
		}
		
		public function parseTemplate()
		{
			$template = $this->t;
			$template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9A-Z_\/]+)\}[\n\r\t]*/is", "\n<?php require_once(makeTemp('\\1')); ?>\n", $template);
			$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?php echo \\1;?>", $template);
			return $template;
		}
		
		/**
		 *
		 *
		 *
		 *
		 *
		 */
		public function clear()
		{
			$this->looper = '';
			$this->loopbox = '';
			$this->files = '';
			unset($this->looper);
			unset($this->loopbox);
			unset($this->files);
		}
	}

?>