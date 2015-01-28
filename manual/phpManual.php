<?php
/**
 * phpManual 是一个php函数接口手册类库
 */
class phpManual 
{
	/**
	 * 支持的语言类型
	 */
	private $_supportLangs = array
	(
		'en' => '英语',
		'de' => '德语',
		'es' => '西班牙语',
		'fr' => '法语',
		'pl' => '波兰文',
		'ru' => '俄文',
		'zh' => '中文',
		'pt_BR' => '葡萄牙文',
	);

	/**
	 * 索引文件句柄
	 */
	private $_indexHandle = null;
	/**
	 * 数据文件句柄
	 */
	private $_storeHandle = null;
	/**
	 * 索引文件
	 */
	private $_indexFile = null;
	/**
	 * 数据文件
	 */
	private $_storeFile = null;
	/**
	 * 构造函数
	 * @param $path 函数存储文件的路径
	 */
	public function __construct()
	{
		$this->_dbpath = __DIR__.DIRECTORY_SEPARATOR."phpManual";
		if(!is_dir($this->_dbpath))
		{
			self::error('PATH_NOT_EXISTS');
			return false;
		}
		return true;
	}

	public function init($lang)
	{
		if(!$this->getFileHandle($lang))
		{
			self::error('NOT_SUPPORT_LANG');
			return false;
		}
		$this->_indexHandle = fopen($this->_indexFile, 'r');
		$this->_storeHandle = fopen($this->_storeFile, 'r');
	}
/**
 * 获取函数内容
 * @param 要查找的函数名称
 * @return 返回函数说明的json字符串
 */
public function get($func)
{
	if(!$this->isInit())
		return;
	$begin = 0;
	$end = filesize($this->_indexFile)/4;
	$ret = '["未查找到该函数"]';
	while($begin < $end){
		$mid = floor(($begin + $end)/2);
		$pos = $mid*4; //$mid只是指针变量的位置，还需要乘上指针的长度4
		$pos = $this->_getOneIndex($pos);
		$name = $this->_getStoreLenValFormat($pos);
		$flag = strcmp($func, $name['value']);
		if($flag == 0){
			$val = $this->_getStoreLenValFormat($pos+4+$name['len']);
			$ret = $val['value'];
			break;
		}elseif($flag < 0){
			$end = $end == $mid ? $mid-1 : $mid;
		}else{
			$begin = $begin == $mid ? $mid+1 : $mid;
		}
	}
	return $ret;
}

	/**
	 * 从索引文件中获取一条记录的其实位置
	 * @param 索引文件中的开始位置，从开始位置获取四个字节为一个函数说明的开始位置
	 * @return 返回该索引位置所对应的存储位置指针偏移量
	 */
	private function _getOneIndex($pos)
	{
		fseek($this->_indexHandle, $pos);
		$len = unpack("Nlen", fread($this->_indexHandle, 4));
		return $len['len'];
	}

	/**
	 * 从制定的指针偏移量获取一个len+val型的内容
	 * @param $pos 文件的指针偏移量
	 * @return 返回数组，包括长度和值
	 */
	private function _getStoreLenValFormat($pos){
		fseek($this->_storeHandle, $pos);
		$len = unpack("Nlen", fread($this->_storeHandle, 4));
		$len = $len['len'];
		$val = fread($this->_storeHandle, $len);
		return array
		(
			'len' => $len,
			'value' => $val,
		);
	}
	/**
	 * 选择文件
	 */
	private function getFileHandle($lang)
	{
		$lang = trim($lang);
		$dbpath = $this->_dbpath."/{$lang}/";
		if(!isset($this->_supportLangs[$lang]) || !is_dir($dbpath))
		{
			return false;
		}

		$this->_indexFile = $dbpath."manualIndex";
		$this->_storeFile = $dbpath."manualStore";
		if(!is_file($this->_indexFile) || !is_file($this->_storeFile))
		{
			return false;
		}
		return true;
	}

	/**
	 * 判断文件句柄是否获取到
	 */
	private function isInit()
	{
		if(
			!$this->_indexHandle ||
			!$this->_indexFile ||
			!$this->_storeHandle ||
			!$this->_storeFile
		){
			self::error("NOT_INIT");
			return false;
		}
		return true;
	}

	/**
	 * 获取二进制文档的信息
	 */
	public function getInfos()
	{
		if(!$this->isInit())
			return;
		fseek($this->_indexHandle, 0);
		$len = fread($this->_indexHandle, 4);
		$len = unpack("Nlen", $len);
		$len = $len['len'] - 1;

		fseek($this->_storeHandle, 0);
		$val = fread($this->_storeHandle, $len);
		$val = unpack("a*value", $val);
		echo $val['value'];
	}

	/**
	 * 错误处理函数
	 * @param $code 错误编号
	 */
	private static function error($code)
	{
		static $codes = array
		(
			'PATH_NOT_EXISTS' => '数据库文件路径不存在',
			'NOT_SUPPORT_LANG' => '不支持该种语言',
			'NOT_INIT' => '还未初始化！',
		);
		echo $codes[$code];
	}
}

