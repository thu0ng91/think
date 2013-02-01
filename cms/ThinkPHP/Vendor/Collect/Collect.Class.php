<?php
/**
 * Class.php
 *
 */
include_once('Collect.Interface.php');
/**
 * CurlApp类
 * 本类提供对CollectInter接口方法的实现
 * @version 1.0
 * @author wm_void
 */
class CurlApp implements CollectInter{
    protected $inUrl;
    protected $inCurlHandle;
    protected $inStr;
    protected $inFileHandle;
    protected $inOptArr;

    /**
     * CurApp类的构造函数,执行对类的初始化
     */
    public function __construct(){
        $this->inUrl = '';
        $this->inCurlHandle = '';
        $this->inOptArr = array();
        $this->inStr = '';
        $this->inFileHandle = '';
    }

    /**
     * 本函数用于获取指定URL的页面内容
     * @param string $strUrl 指定要获取页面的URL
     * @return string 
     */
    public function getUrlContent($strUrl){
        $this->inUrl = $strUrl;
        $this->inCurlHandle = curl_init();
        $this->inOptArr = array(
            CURLOPT_URL => $this->inUrl,
            CURLOPT_HEADER => 0,
            CURLOPT_NOBODY => 0,
            CURLOPT_PORT => 80,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => 1,
        );
        curl_setopt_array($this->inCurlHandle, $this->inOptArr);
        $this->inStr = curl_exec($this->inCurlHandle);
        curl_close($this->inCurlHandle);
        return $this->inStr;
    }

    /**
     * 本函数用于将获取的页面内容存入本地文件
     * @param string $strFlieName 指定要输出到的本地文件路径
     * @return bool
     */
    public function saveContent($strFlieName){
        $this->inFileHandle = fopen($strFlieName,'w');
        fwrite($this->inFileHandle, $this->inStr);
        fclose($this->inFileHandle);
        return true;
    }

     /**
     * CurApp类的析构函数,执行对本类所用资源进行释放
     */
    public function  __destruct() {
        unset($this->inUrl);
        unset($this->inStr);
        unset($this->inCurlHandle);
        unset($this->inFileHandle);
        unset($this->inOptArr);
    }
}

/**
 * FileGetContentApp类
 * 本类提供了对CollectInter接口方法的实现
 * @version 1.0
 * @author wm_void
 */
class FileGetContentApp implements CollectInter {
    protected $inUrl;
    protected $inStr;
    protected $inFileHandle;

    /**
     * FileGetContentApp类的构造函数,执行对类的初始化
     */
    public function __construct(){
        $this->inUrl = '';
        $this->inStr = '';
        $this->inFileHandle = '';
    }

    /**
     * 本函数用于获取指定URL的页面内容
     * @param string $strUrl 指定要获取页面的URL
     * @return string
     */
    public function getUrlContent($strUrl){
        $this->inUrl = $strUrl;
        $this->inStr = file_get_contents($this->inUrl);
        return $this->inStr;
    }

    /**
     * 本函数用于将获取的页面内容存入本地文件
     * @param string $strFlieName 指定要输出到的本地文件路径
     * @return bool
     */
    public function saveContent($strFlieName){
        $this->inFileHandle = fopen($strFlieName, 'w');
        fwrite($this->inFileHandle, $this->inStr);
        fclose($this->inFileHandle);
        return true;
    }

    /**
     * FileGetContentApp类的析构函数,执行对本类所用资源进行释放
     */
    public function  __destruct() {
        unset($this->inUrl);
        unset($this->inStr);
        unset($this->inFileHandle);
    }
}

/**
* FopenApp类
 * 本类提供了对CollectInter接口方法的实现
 * @version 1.0
 * @author wm_void
 */
class FopenApp implements CollectInter {
    protected $inUrl;
    protected $inStr;
    protected $inUrlHandle;
    protected $inFileHandle;

    /**
     * FopenApp类的构造函数,执行对类的初始化
     */
    public function __construct(){
        $this->inUrl = '';
        $this->inUrlHandle = '';
        $this->inStr = '';
        $this->inFileHandle = '';
    }

    /**
     * 本函数用于获取指定URL的页面内容
     * @param string $strUrl 指定要获取页面的URL
     * @return string
     */
    public function getUrlContent($strUrl){
        $this->inUrl = $strUrl;
        $this->inUrlHandle = fopen($this->inUrl, 'r');
        $this->inStr = '';
        while(!feof($this->inUrlHandle)){
            $this->inStr .= fread($this->inUrlHandle, 1024);
        }
        fclose($this->inUrlHandle);
        return $this->inStr;
    }

    /**
     * 本函数用于将获取的页面内容存入本地文件
     * @param string $strFlieName 指定要输出到的本地文件路径
     * @return bool
     */
    public function saveContent($strFlieName){
        $this->inFileHandle = fopen($strFlieName, 'w');
        fwrite($this->inFileHandle, $this->inStr);
        fclose($this->inFileHandle);
        return true;
    }
    
    /**
     * FopenApp类的析构函数,执行对类的资源回收
     */
    public function  __destruct() {
        unset($this->inUrl);
        unset($this->inStr);
        unset($this->inFileHandle);
        unset($this->inUrlHandle);
    }
}

?>