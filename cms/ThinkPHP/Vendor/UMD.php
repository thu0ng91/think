<?php
/**
 +------------------------------------------------------------------------------
 * UMD编码,文本转umd文件,测试可用在支持umd的阅读器上
 +------------------------------------------------------------------------------
 * @HXPHP Framwork
 * @Author ieliwb	<ieliwb@gmail.com>
 * @Copyright (c) www.ieliwb.com
 +------------------------------------------------------------------------------
 */
class UMD
{
	public $bookinfo = array
	(
		"id" 		=> 		0,
		"title" 	=> 		"umd book",
		"author" 	=> 		"unknow",
		"year" 		=> 		"0",
		"month" 	=> 		"0",
		"day" 		=> 		"0",
		"sort"	 	=> 		"default",
		"publisher" => 		"ChinaPub",
		"seller" 	=> 		"DIY_GENERATED",
		"cover" 	=> 		""
	);
	public $chapters = array();
	public $chaptercount = 0;
	public $articlelen = 0;
	public $chaptitlelen = 0;
	public $charset = "GBK";
	public $handle;

	function __construct()
	{
		$this->bookinfo['year'] = date("Y");
		$this->bookinfo['month'] = date("n");
		$this->bookinfo['day'] = date("j");
	}

	/**
	 * 设置书籍编码
	 *
	 * @param String $charset
	 */
	function setCharset($charset)
	{
		$this->charset = $charset;
	}

	/**
	 * 设置添加书籍头信息
	 *
	 * @param Array $bookinfo
	 */
	function addBookInfo($bookinfo = array())
	{
		foreach($this->bookinfo as $key => $value)
		{
			if(isset($bookinfo[$key]))
			{
				$this->bookinfo[$key] = $bookinfo[$key];
			}
			if(($key != "id") && ($this->charset != "UCS"))
			{
				$this->bookinfo[$key] = iconv($this->charset,"UCS-2LE//IGNORE",$this->bookinfo[$key]);
			}
		}
	}

	/**
	 * 设置添加章节
	 *
	 * @param String $c_title
	 * @param String $c_content
	 */
	function addChapter($c_title,$c_content)
	{
		if ( $this->charset != "UCS" )
		{
			$c_title = iconv($this->charset,"UCS-2LE//IGNORE",$c_title);
			$c_content = iconv($this->charset,"UCS-2LE//IGNORE",str_replace("\r","",$c_content));
		}
		$this->chapters[$this->chaptercount] = array
		(
			"title" => $c_title,
			"content" => $c_content
		);
		++$this->chaptercount;
		$this->chaptitlelen += strlen($c_title);
		$this->articlelen += strlen($c_content);
	}

	/**
	 * 写入简介及其他相关信息
	 *
	 * @param String $string
	 * @param Int $node
	 * @return String
	 */
	function makeInfo($string,$node)
	{
		$data  = chr(35).chr($node).chr(0).chr(0);
		$data .= $this->dec2hex(strlen($string) + 5,1);
		$data .= $string;
		return $data;
	}

	/**
	 * 十进制转十六进制
	 *
	 * @param String $string
	 * @param Int $length
	 * @return String
	 */
	function dec2hex($string,$length)
	{
		$data = "";
		$length *= 2;
		$c_string = substr(sprintf("%0".$length."s",dechex($string)),0 - $length);
		for ($i = 0;$i < $length;$i += 2)
		{
			$data = chr(hexdec(substr($c_string,$i,2))).$data;
		}
		return $data;
	}

	/**
	 * 写入章节偏移量
	 *
	 * @param Int $fontSize
	 * @param Int $screenWidth
	 * @param Int $PID
	 */
	function writePageOffset($fontSize,$screenWidth,$PID)
	{
		$h = mt_rand(28672,32767);
		$content_len = $this->articlelen + $this->chaptercount * 2;
		$data = pack('H*',"2387");
		$data .= pack('n',$PID);
		$data .= chr(0x0B);
		$data .= chr($fontSize).chr($screenWidth);
		$data .= $this->dec2hex($h,4);
		$data .= chr(36);
		$data .= $this->dec2hex($h,4);
		$random = 17;
		$data .= $this->dec2hex($random,4);
		$random = 0;
		$data .= $this->dec2hex($random,4);
		$data .= $this->dec2hex($content_len,4);
		//$data .= $this->dec2hex(floor($content_len / 2),4);
		fwrite($this->handle,$data,strlen($data));
		unset($data);
	}

	/**
	 * 编译生成UMD
	 *
	 * @param String $filename
	 * @return Boolean
	 */
	function makeUmd($filename)
	{
		$this->handle = fopen($filename,"wb");
		if(!$this->handle)
		{
			return false;
		}
		flock($this->handle,LOCK_EX);

		$data  = "";
		$data .= pack('H*',"899B9ADE");								//头 umd文件标志
		$data .= pack('H*',"230100000801");							//0x01--文件开始
		$data .= $this->dec2hex(mt_rand(1025,32767),2);
		$data .= $this->makeInfo($this->bookinfo['title'],2);		//0x02--标题
		$data .= $this->makeInfo($this->bookinfo['author'],3);		//0x03--作者
		$data .= $this->makeInfo($this->bookinfo['year'],4);		//0x04--年
		$data .= $this->makeInfo($this->bookinfo['month'],5);		//0x05--月
		$data .= $this->makeInfo($this->bookinfo['day'],6);			//0x06--日
		$data .= $this->makeInfo($this->bookinfo['sort'],7);		//0x07--小说类型
		$data .= $this->makeInfo($this->bookinfo['publisher'],8);	//0x08--出版商
		$data .= $this->makeInfo($this->bookinfo['seller'],9);		//0x09--零售商
		fwrite($this->handle,$data,strlen($data));

		//0x0b--内容长度
		$data = "";
		$data .= pack('H*',"230B000009");
		$data .= $this->dec2hex($this->articlelen + $this->chaptercount * 2,4);

		//0x83--章节偏移 写入章节数
		$data .= pack('H*',"2383000109");
		$random = mt_rand(12288,16383);
		$data .= $this->dec2hex($random,4);
		$data .= pack('H*',"24");
		$data .= $this->dec2hex($random,4);
		$random = $this->chaptercount * 4 + 9;
		$data .= $this->dec2hex($random,4);
		$chapteroffset = 0;

		foreach($this->chapters as $key => $value)
		{
			$data .= $this->dec2hex($chapteroffset,4);
			$chapteroffset += strlen($value['content']) + 2;
		}

		//0x84--章节标题，正文
		$data .= pack('H*',"2384000109");
		$random = mt_rand(16384,20479);
		$data .= $this->dec2hex($random,4);
		$data .= pack('H*',"24");
		$data .= $this->dec2hex($random,4);
		$random = 9 + $this->chaptitlelen + $this->chaptercount;
		$data .= $this->dec2hex($random,4);

		foreach($this->chapters as $key => $value)
		{
			$random = strlen($value['title']);
			$data .= $this->dec2hex($random,1);
			$data .= $value['title'];
		}
		fwrite($this->handle,$data,strlen($data));

		$ss  = 0;
		$oo = 32768;
		$chapstr = "";
		foreach($this->chapters as $key => $value)
		{
			$chapstr .= $value['content'].chr(41).chr(32);
		}
		$chap_len = strlen($chapstr);

		$maximum = ceil($chap_len / $oo);
		$num_1 = mt_rand(0,$maximum - 1);
		$num_2 = mt_rand(0,$maximum - 1);
		$aa = array();
		for($i = 0;$i < $maximum;++$i)
		{
			$data = "";
			$data .= chr(36);
			$numrand = mt_rand(4.02653e+009,4.29497e+009);
			$aa[$i] = $numrand;
			$data .= $this->dec2hex($numrand,4);
			$c_chapstr = substr($chapstr,$ss,$oo);
			$ss += $oo ;
			$z_chapstr = gzcompress($c_chapstr);
			$random = 9 + strlen($z_chapstr);
			$data .= $this->dec2hex($random,4);
			$data .= $z_chapstr ;
			if($i == $num_1)
			{
				$data .= pack('H*',"23F100001500000000000000000000000000000000");
			}
			if ($i == $num_2)
			{
				$data .= pack('H*',"230A000009");
				$data .= $this->dec2hex($this->bookinfo['id'] + 268435456,4);
			}
			fwrite($this->handle,$data,strlen($data));
		}

		//0x81--正文写入完毕
		$data = "";
		$data .= pack('H*',"2381000109");
		$random = mt_rand(8192,12287);
		$data .= $this->dec2hex($random,4);
		$data .= chr(36);
		$data .= $this->dec2hex($random,4);
		$random = 9 + $maximum * 4;
		$data .= $this->dec2hex($random,4);
		for($i = 0;$i < $maximum;++$i)
		{
			$data .= $this->dec2hex($aa[$i],4);
		}
		fwrite($this->handle,$data,strlen($data));

		//0x82--封面
		$data = "";
		if(!empty($this->bookinfo['cover']) || is_file($this->bookinfo['cover']))
		{
			$data .= pack('H*',"238200011001");
			$random = mt_rand(4096,8191);
			$data .= $this->dec2hex($random,4);
			$data .= chr(36);
			$data .= $this->dec2hex($random,4);
			$coverstream = file_get_contents($this->bookinfo['cover']);
			$random = strlen($coverstream) + 9;
			$data .= $this->dec2hex($random,4);
			$data .= $coverstream;
			fwrite($this->handle,$data,strlen($data));
			$data = "";
		}

		//0x87--PageOffset
		$this->writePageOffset(0x10,0xD0,0x01);
		$this->writePageOffset(0x10,0xB0,0x01);
		$this->writePageOffset(0x0C,0xD0,0x01);
		$this->writePageOffset(0x0C,0xB0,0x01);
		$this->writePageOffset(0x0A,0xA6,0x05);

		//0x0c--文件结束
		$data .= pack('H*',"230C000109");
		$random = 4 + strlen($data) + ftell($this->handle);
		$data .= $this->dec2hex($random,4);
		fwrite($this->handle,$data,strlen($data));

		unset($data);
		flock($this->handle,LOCK_UN);
		fclose($this->handle);
		@chmod($filename,0755);
		return true;
	}

}
?>
