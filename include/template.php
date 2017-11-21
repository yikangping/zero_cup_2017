<?php
class d5_template{
	
	protected $_templatePath;
	
	protected $_cachePath;
	
	protected $_templateDir = 'template';
	
	protected $_cacheDir = 'cache_php';
	
	protected $_ext     = '.htm';
	
	protected $_depth   = 5;
	
	protected $_language = array();
		
	/*
		 * 计算文件的hash
		 *
		 * @param unknown_type $path
		 * @return unknown
	*/
	public function __construct(){
		$this->init();
	}
	
	public function init(){
		
	}
	
	public function setLanguage($lang){
		$this->_language = $lang;
	}
	
	public function setTemplateDir($dir){
		clearstatcache();
		if(is_dir($dir))
		$this->_templateDir = $dir;
		else{
			$this->mkAllDir($dir);
		}
	}
	
	public function setCacheDir($dir){
		clearstatcache();
		if(is_dir($dir))
		$this->_cacheDir = $dir;
		else {
			$this->mkAllDir($dir);
		};
	}
	
	public function setTemplatePath($filePath){
		$this->_templatePath = $this->_templateDir.'/'.$filePath.$this->_ext;		
	}
	
	public function setCachePath($filePath){
		$this->_cachePath = $this->_cacheDir.'/'.$filePath.'.php';	
	}

	protected function mkAllDir($path,$mode=0777){
		$dirList = explode('/',$path);
		$dirPath = '';
		foreach($dirList as $value){
			$dirPath .= $value.'/';
			if(!is_dir($dirPath)){
				@mkdir($dirPath,$mode);
			}
		}
	}
	/*
	 * 获得模板hash值
	 * @param void
	 * @return string
	 * */
	protected function file_hash(){
		return md5_file($this->_templatePath);
	}
	/*
	 * 匹配模板与缓存的修改时间
	 * @param void
	 * @return string
	 */
	protected function match_filemtime(){
		clearstatcache();
		$tp_mtime = filemtime($this->_templatePath);
		$ch_mtime = is_file($this->_cachePath) ? filemtime($this->_cachePath) : 0;
		return $tp_mtime <= $ch_mtime;
	}
	/*
		 * 对比模板文件与缓存文件的hash值
		 *
		 * @param unknown_type $file
		 * @return unknown
	*/
	
	protected function match_hash(){
		$read_hash = $this->file_read($this->_cachePath,48);
		$html_hash = file_hash();
		if(preg_match("/".$html_hash."/i",$read_hash)){
			return true;
		}		
	}
	
	
	 /**
		 * 读取文件内容
		 *
		 * @param unknown_type $path
		 * @return unknown
	 */
	protected function file_read($path,$length=0){
		$fp = @fopen($path,"rb");
		if($length==0) {
			$contents = @fread($fp,filesize($path));
		} else {
			$contents = @fread($fp,$length);
		}
		if(!empty($fp))
			fclose($fp);
		return $contents;
	}
	
	
	/*
		 *
		 * 写入文件内容
		 *
		 * @param unknown_type $path
		 * @param unknown_type $puts
	*/
	protected function file_write($path,$puts) {
		if(!$this->file_test($path,"w+")) {
			$fp = @fopen($path,"w+");
			flock($fp, 2);
			@fwrite($fp,$puts);
			fclose($fp);
		}	
	}
	
	/*
	 *
	 * 检查文件是否存在并且有读取权限
	 *
	 * @param unknown_type $path
	*/
	        
	protected function file_test($path,$method) {
		if(!file_exists($path) || !fopen($path,$method)) {
			echo "模板文件不存在,或没有操作权限";
			return false;
		}
	}
	/* 模板引擎处理函 ------------------------------------------- START ----------------------------------------------- 
	 */	
	protected function transamp($str) {
		//$str = str_replace('&', '&amp;', $str);
		$str = str_replace('&amp;amp;', '&amp;', $str);
		$str = str_replace('\"', '"', $str);
		return $str;
	}

	protected function addquote($var) {
		return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
	}
		
	protected function stripvtags($expr, $statement) {
		$expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
		$statement = str_replace("\\\"", "\"", $statement);
		return $expr.$statement;
	}
		
	protected function stripscriptamp($s) 
	{
		$s = str_replace('&amp;', '&', $s);
		return "<script src=\"$s\" type=\"text/javascript\"></script>";
	}

/*	protected function setImage($start,$url,$end){
		$url = $this->_templateDir.'/'.$url;
		$start = $this->transamp($start);
		$end = $this->transamp($end);
		return "<img ".$start." src=\"".$url."\" ".$end.">";
	}
	*/
	protected function setLink($start,$url,$end){
		$url = $this->_templateDir.'/'.$url;
		$start = $this->transamp($start);
		$end = $this->transamp($end);
		return "<link ".$start." href=\"".$url."\" ".$end.">";
	}

	/*protected function setImgBut($start,$url,$end){
		$url = $this->_templateDir.'/'.$url;
		$start = $this->transamp($start);
		$end = $this->transamp($end);
		return "<link ".$start." href=\"".$url."\" ".$end.">";
	}*/
	
	protected function replaceSrc($url){
		$url = $this->_templateDir.'/'.$url;
		return " src=\"".$url."\" ";
	}

	protected function languagevar($var) {
		if(isset($this->_language) && is_array($this->_language) && isset($this->_language[$var])) {
				return $this->_language[$var];
		} else {
				return "!$var!";
		}
	}
	/* 模板引擎处理函 -------------------------------------------- END ------------------------------------------------
	 */	

	
	//模板编译
	public function template() {
		
		//模板文件存在检查
		if(!file_exists($this->_templatePath)) {
			die($this->_templatePath." not found");
		}
		if( $this->match_filemtime() ) {
			return $this->_cachePath;
		} else {
			$template = $this->file_read($this->_templatePath, filesize($this->_templatePath));
			
			$var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
			$const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";
		
			$template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
			$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
			$template = preg_replace("/\{lang\s+(.+?)\}/ies", "\$this->languagevar('\\1')", $template);
			$template = str_replace("{LF}", "<?=\"\\n\"?>", $template);
			
			$template = preg_replace("/\{(\\\$[a-zA-Z0-9_\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);
			$template = preg_replace("/$var_regexp/es", "\$this->addquote('<?=\\1?>')", $template);
			$template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/es", "\$this->addquote('<?=\\1?>')", $template);
		
			$template = "<?php if(!defined('IN_SITE')) exit('Access Denied'); ?>\n$template";
			$template = preg_replace("/[\n\r\t]*\{template\s+([a-z0-9_]+)\}[\n\r\t]*/is", "\n<?php include \$this->template('\\1'); ?>\n", $template);
			$template = preg_replace("/[\n\r\t]*\{eval\s+(.+?)\}[\n\r\t]*/ies", "\$this->stripvtags('\n<?php \\1 ?>\n','')", $template);
			$template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ies", "\$this->stripvtags('\n<?php echo \\1; ?>\n','')", $template);
			$template = preg_replace("/[\n\r\t]*\{elseif\s+(.+?)\}[\n\r\t]*/ies", "\$this->stripvtags('\n<?php } elseif(\\1) { ?>\n','')", $template);
			$template = preg_replace("/[\n\r\t]*\{else\}[\n\r\t]*/is", "\n<?php } else { ?>\n", $template);
		
			for($i = 0; $i < $this->_depth; $i++) {
				$template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r]*(.+?)[\n\r]*\{\/loop\}[\n\r\t]*/ies", "\$this->stripvtags('\n<?php if(is_array(\\1)) { foreach(\\1 as \\2) { ?>','\n\\3\n<?php } } ?>\n')", $template);
				$template = preg_replace("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*(.+?)[\n\r\t]*\{\/loop\}[\n\r\t]*/ies", "\$this->stripvtags('\n<?php if(is_array(\\1)) { foreach(\\1 as \\2 => \\3) { ?>','\n\\4\n<?php } } ?>\n')", $template);
				$template = preg_replace("/[\n\r\t]*\{if\s+(.+?)\}[\n\r]*(.+?)[\n\r]*\{\/if\}[\n\r\t]*/ies", "\$this->stripvtags('\n<?php if(\\1) { ?>','\n\\2\n<? } ?>\n')", $template);
			}
		
			$template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
			$template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);
		
			if(!@$fp = fopen($this->_cachePath, 'w')) {
				die("Directory {$this->_CACHEDIR} not found or have no access!");
			}
		
			$template = preg_replace("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/e", "\$this->transamp('\\0')", $template);
			$template = preg_replace("/\<script[^\>]*?src=\"(.+?)\".*?\>\s*\<\/script\>/ise", "\$this->stripscriptamp('\\1')", $template);
			$template = preg_replace("/src\s*?\=\"(.*?)\"/ies","\$this->replaceSrc('\\1')",$template);
			//$template = preg_replace("/<img([^>]*?)src\s*?\=\"(.*?)\"(.*?)>/ies","\$this->setImage('\\1','\\2','\\3')",$template); 
			$template = preg_replace("/<link([^>]*?)href\s*?\=\"(.*?)\"(.*?)>/ies","\$this->setLink('\\1','\\2','\\3')",$template); 

			$hash      = $this->file_hash($this->_templatePath);
			$year    = gmdate('Y');
			$month   = gmdate('m');
			$day     = gmdate('d');
			$hour    = gmdate('H')+8;
			$minute  = gmdate('i');
			$second	 = gmdate('s');
			$head_hash = "<?php #tplhash=".$hash." ?>\n<?php #createtime=".$year."-".$month."-".$day." ".$hour.":".$minute.":".$second." ?>\n";
			$this->file_write($this->_cachePath,$head_hash.$template);
			return $this->_cachePath;
		}
	}	
}

?>