<?php
/*
 * Collector.php
 */

include_once('Collect.Class.php');
/**
 * Collect 类
 * 本类提供采集器类的入口,封装了采集电子书所用到的函数
 * @version 1.0
 * @author wm_void
 */
class Collect {
    protected $inCInter;
    protected $inRoleArr;
    protected $inResultArr;
    protected $inStr;
    protected $inCharset;

    /**
     * Collect类的构造函数，对本类进行初始化
     */
    public function  __construct() {
        //  这里做了一个判断
        //  跟据服务器所支持的具体函数来对CollectInter接口实例化
        if(function_exists('file_get_contents') == true){
            $this->inCInter = new FileGetContentApp();
        }elseif(function_exists('fopen') == true){
            $this->inCInter = new FopenApp();
        }elseif(function_exists('curl_init') == true){
            $this->inCInter = new CurlApp();
        }else{
            echo '请确认PHP的版本高于5.0或检查PHP的配置';
        }
        $this->inRoleArr = array();
        $this->inResultArr = array();
        $this->inStr = '';
        $this->inCharset = '';
    }

    /**
     * 本函数将获取的页面内容保存到本地文件
     * @param string $strUrl 指定页面URL地址
     * @param string $strFlieName 指定本地文件路径
     * @return bool
     */
    public function saveToFile($strUrl,$strFlieName){
        $this->inStr = $this->inCInter->getUrlContent($strUrl);
        $this->inCInter->saveContent($strFlieName);
        return true;
    }
    
    /**
     * 本函数用于获取页面的内容
     * @param string $strUrl 指定页面URL地址
     * @return string
     */
    public function getContent($strUrl){
        $this->inStr = $this->inCInter->getUrlContent($strUrl);
        // 获得页面的编码方式
        preg_match('/(?<=charset\=).*?(?=")/', $this->inStr, $temp);
        $this->inCharset = $temp[0];
        // 根据编码设置相应的编码
        if($this->inRoleArr['site_role'][7] != "auto"){
           $this->inCharset = $this->inRoleArr['site_role'][7];
        }
        $this->inRoleArr['site_role'] = $this->getMatch($this->inRoleArr['site_role'],1);
        $this->inRoleArr['book_role'] = $this->getMatch($this->inRoleArr['book_role']);
        $this->inRoleArr['chapter_role'] = $this->getMatch($this->inRoleArr['chapter_role']);
        return $this->inStr;
    }

    /**
     * 本函数用于设置inStr变量的内容
     * @param string $str 传入字符串
     */
    public function setContent($str){
        $this->inStr = $str;
    }

    /**
     * 本函数提供对采集器规则设定
     * @param array $strRole1 指定采集器源站点规则数组
     * @param array $strRole2 指定采集器小说信息规则数组
     * @param array $strRole3 指定采集器章节内容规则数组
     * @return bool
     */
    public function setRole($strRole1,$strRole2,$strRole3){
        $this->inRoleArr['site_role'] = $this->startTextToArray($strRole1);
        $this->inRoleArr['book_role'] = $this->startTextToArray($strRole2);
        $this->inRoleArr['chapter_role'] = $this->startTextToArray($strRole3);
        return true;
    }
    
    /**
     * 本函数执行规则字符串转正则表达式字符串
     * @param array $arrTemp 指定要转正则的规则数组
     * @param string $nWay 指定转正则的方式　0为使用预搜索；1为使用普通方式，默认为0
     * @param string $strPart 指定匹配内容标示符
     * @return array
     */
    private function getMatch($arrTemp,$nWay = 0,$strPart = '{内容}'){
        for($i = 0; $i < count($arrTemp); $i++){
            $pos = strrpos($arrTemp[$i],$strPart);
            if($pos !== false){
                $arrTemp2 = preg_split('/'.preg_quote($strPart).'/', $arrTemp[$i]);
                $strTemp1 = preg_quote($arrTemp2[0]);
                $strTemp2 = preg_quote($arrTemp2[1]);
                $strTemp1 = preg_replace('/\//', '\/',$strTemp1);
                $strTemp2 = preg_replace('/\//', '\/',$strTemp2);
                //$arrTemp[$i] = "/(?<={$strTemp1}).*?(?={$strTemp2})/s";
                if($nWay == 0){
                    $arrTemp[$i] = $this->toCharset('utf-8', $this->inCharset, '/(?<='.$strTemp1.')[\s\S]*?(?='.$strTemp2.')/');
                }else{
                    $arrTemp[$i] = $this->toCharset('utf-8', $this->inCharset, '/'.$strTemp1.'[\s\S]*?'.$strTemp2.'/');
                }
            }
        }
        return $arrTemp;
    }

    /**
     * 本函数执行采集源站点书库小说列表
     * @return array
     */
    public function getBookList($collector_addr){
        $this->getContent($collector_addr);
        $web_site = $this->inRoleArr['site_role'][0];
        $web_role1 = $this->inRoleArr['site_role'][4];
        $web_role2 = $this->inRoleArr['site_role'][5];
        $web_role3 = $this->inRoleArr['site_role'][6];
        $this->inResultArr = array();
        $arrTemp = array();
        $arrTemp1 = array();
        $arrTemp2 = array();
        preg_match($web_role1, $this->inStr, $arrTemp);
        $strTemp = $arrTemp[0];
        // 匹配满足规则的书目的所以行数
        preg_match_all($web_role2, $strTemp, $arrTemp);
        $arrTemp1 = $arrTemp[0];
        $countTemp = count($arrTemp[0]);
        for($i = 0; $i < $countTemp; $i++){
            $strTemp = $arrTemp1[$i];
            preg_match($web_role3, $strTemp, $arrTemp);
            $strTemp2 = $this->toCharset($this->inCharset, 'utf-8', $arrTemp[0]);
            // 从a标签中解析出URL地址
            $strUrl = $this->getLink($strTemp2);
            // URL处理
            $strUrl = $this->getUrl($strUrl, $collector_addr);
            // 从a标签中解析出链接名
            $strBookName = $this->getLink($strTemp2,1);
            $arrTemp2[$i]['book_name'] = $strBookName;
            $arrTemp2[$i]['book_url'] = $strUrl;
        }
        $this->inResultArr = $arrTemp2;
        return $this->inResultArr;
    }

    /**
     * 本函数执行电子书信息的采集
     * @param string $strUrl 指定要采集小说URL
     * @return array
     */
    public function getBookInfo($strUrl){
        $chapter_list_role = $this->inRoleArr['site_role'][2];
        $this->getContent($strUrl);
        $web_site = $this->inRoleArr['site_role'][0];
        $book_role = $this->inRoleArr['book_role'];
        $this->inResultArr = array();
        for($i = 0; $i < count($book_role); $i++){
            $arrTemp = array();
            preg_match($book_role[$i], $this->inStr, $arrTemp);
            $this->inResultArr[$i] = $this->toCharset($this->inCharset, 'utf-8', $arrTemp[0]);
        }
        $this->inResultArr[2] = $this->removeHtml2($this->inResultArr[2]);
        $this->inResultArr[3] = $this->removeHtml2($this->inResultArr[3]);
        $this->inResultArr[4] = $this->removeHtml2($this->inResultArr[4]);
        $this->inResultArr[5] = $this->removeHtml2($this->inResultArr[5]);
        $this->inResultArr[9] = $this->getLink($this->inResultArr[9], 2);
        $this->inResultArr[9] = $this->getUrl($this->inResultArr[9], $strUrl);
        $this->inResultArr[9] = $this->getRemoteImg($this->inResultArr[9]);
        // 以下是对章节URL方式的判断
        $pos = strrpos($chapter_list_role,'{*}');
        if($pos === false){
            $arrTemp1 = array();
            $arrTemp2 = array();
            $arrTemp3 = array();
            $arrTemp1[0] = $chapter_list_role;
            $arrTemp3 = $this->getMatch($arrTemp1);
            $chapter_list_role = $arrTemp3[0];
            preg_match($chapter_list_role, $this->inStr, $arrTemp2);
            $this->inResultArr[10] = $this->removeHtml2($this->toCharset($this->inCharset, 'utf-8', $arrTemp2[0]));
            $this->inResultArr[10] = $this->getUrl($this->inResultArr[10], $strUrl);
        }else{
            $this->inResultArr[10] = preg_replace('/\{\*\}/', $this->inResultArr[0], $chapter_list_role);
        }
        return $this->inResultArr;
    }

    /**
     * 本函数执行采集源站点书库小说列表
     * @param string $strNum 指定小说序号
     * @return array
     */
    public function getBookChapter($strUrl){
        $this->getContent($strUrl);
        $web_site = $this->inRoleArr['site_role'][0];
        $chapter_vol = $this->inRoleArr['chapter_role'][0];
        $chapter_one = $this->inRoleArr['chapter_role'][1];
        $chapter_role = array(
            0 => $this->inRoleArr['chapter_role'][2],
            1 => $this->inRoleArr['chapter_role'][3],
            2 => $this->inRoleArr['chapter_role'][4],
            3 => $this->inRoleArr['chapter_role'][5],
            4 => $this->inRoleArr['chapter_role'][6],
        );
        $this->inResultArr = array();
        // 采集分卷内容
        $arrTemp = array();
        $arrTemp1 = array();
        preg_match_all($chapter_vol, $this->inStr, $arrTemp);
        $arrTemp1 = $arrTemp[0];
        $countTemp = count($arrTemp[0]);
        $nCount = 0;
        for($i = 0; $i < $countTemp; $i++){
            // 按分卷进行章节采集
            $arrTemp2 = array();
            $arrTemp3 = array();
            preg_match_all($chapter_one, $arrTemp1[$i], $arrTemp2);
            $arrTemp3 =$arrTemp2[0];
            $countTemp1 = count($arrTemp2[0]);
            for($j = 0; $j < $countTemp1; $j++){
                for($k = 0; $k < count($chapter_role); $k++){
                    $arrTemp4 = array();
                    preg_match($chapter_role[$k], $arrTemp3[$j], $arrTemp4);
                    $this->inResultArr[$nCount][$k] = $this->removeHtml($this->toCharset($this->inCharset, 'utf-8', $arrTemp4[0]));
                }
                $this->inResultArr[$nCount][4] = $this->getUrl($this->inResultArr[$nCount][4], $strUrl);
                // 分卷号
                $this->inResultArr[$nCount][5] = $i;
                $nCount++;
            }
        }
        return $this->inResultArr;
    }
    
    /**
     * 本函数执行电子书章节的采集
     * @param string $strUrl 章节内容的URL
     * @return array
     */
    public function getBookContent($strUrl){
        $this->getContent($strUrl);
        $web_site = $this->inRoleArr['site_role'][0];
        $content_role = array(
            0 => $this->inRoleArr['chapter_role'][2],
            1 => $this->inRoleArr['chapter_role'][3],
            2 => $this->inRoleArr['chapter_role'][4],
            3 => $this->inRoleArr['chapter_role'][5],
            4 => $this->inRoleArr['chapter_role'][7],
            5 => $this->inRoleArr['chapter_role'][8],
        );
        $this->inResultArr = array();
        for($i = 0; $i < count($content_role); $i++){
            $arrTemp = array();
            preg_match($content_role[$i], $this->inStr, $arrTemp);
            $this->inResultArr[$i] = $this->removeHtml($this->toCharset($this->inCharset, 'utf-8', $arrTemp[0]));
        }
        return $this->inResultArr;
    }

    /**
     *  本函数远程图片本地化,目前使用图片远程复制法，需GD库支持，以为会完善多种方式支持
     * @param string $strUrl 传入图片的URL
     * @param string $strDir 设置本地目录
     * @return string
     */
    private function getRemoteImg($strUrl,$strDir = 'files/images/'){
        $imgUrl = $strUrl;
        $img_role = $this->inRoleArr['site_role'][8];
        if($img_role == "1"){
            $dateNow = time();
            $imgName = "{$strDir}l_cms_{$dateNow}.jpg";
            $src_im = imagecreatefromjpeg($imgUrl);
            $srcW = ImageSX($src_im);                                       //获得图像的宽
            $srcH = ImageSY($src_im);                                       //获得图像的高
            $dst_im = ImageCreateTrueColor($srcW,$srcH);                    //创建新的图像对象
            imagecopy($dst_im, $src_im, 0, 0, 0, 0, $srcW, $srcH);
            touch($imgName);
            imagejpeg($dst_im, $imgName, 100);
            $imgUrl= $imgName;
        }
        return $imgUrl;
    }

    /**
     * 本函数返回执行采集后的结果数组
     * @return array
     */
    public function getResult(){
        return $this->inResultArr;
    }

     /**
     * 本函数执行组数到规则字符串的转换
     * @param string $arrTemp 要传换的规则数组
     * @param string $strPart 分隔符
     * @return string
     */
    public function startArrayToText($arrTemp,$strPart = '{#}'){
        $strTemp = '';
        for($i = 0; $i < count($arrTemp); $i++){
            $strTemp2 = $arrTemp[$i];
            $strTemp2 = preg_replace('/\r/', '[r]', $strTemp2);
            $strTemp2 = preg_replace('/\n/', '[n]', $strTemp2);
            $strTemp .= $strTemp2.$strPart;
        }
        return $strTemp;
    }

    /**
     * 本函数执行规则字符串到数组的转换
     * @param string $strText 要传换的普通文本字符串
     * @param string $strPart 分隔符
     * @return string
     */
    public function startTextToArray($strText,$strPart = '{#}'){
        $arrTemp = preg_split('/'.preg_quote($strPart).'/',$strText);
        for($i = 0; $i < count($arrTemp); $i++){
            $arrTemp[$i] = preg_replace('/\[r\]/', "\r", $arrTemp[$i]);
            $arrTemp[$i] = preg_replace('/\[n\]/', "\n", $arrTemp[$i]);
            $arrTemp[$i] = stripslashes($arrTemp[$i]);
        }
        return $arrTemp;
    }

    /**
     * 本函数在iconv的基础上，对它进行改进
     * @param string $inCharset 指定输入编码
     * @param string $outCharset　指定输出编码
     * @param string $str　指定要转换的字符串
     * @return string
     */
    private function toCharset($inCharset,$outCharset,$str){
        $strTemp = $str;
        if($inCharset != $outCharset){
            $strTemp = iconv($inCharset, $outCharset, $strTemp);
        }
        //$strTemp = $this->removeHtml($strTemp);
        return $strTemp;
    }

    /**
     * 本函数执行对传入的字符串将其中的HTML代码去掉
     * @param string $str 指定要去HTML代码的字符串
     * @return string
     */
    private function removeHtml($str){
        $strTemp = preg_replace('/\<br\>/', '[br]', $str);
        $strTemp = preg_replace('/\<br \/\>/', '[br]', $strTemp);
        $strTemp = preg_replace('/\<BR\>/', '[br]', $strTemp);
        $strTemp = preg_replace('/\<BR \/\>/', '[br]', $strTemp);
        $strTemp = preg_replace('/\<p\>/', '[p]', $strTemp);
        $strTemp = preg_replace('/\<P\>/', '[p]', $strTemp);
        $strTemp = preg_replace('/\<\/p\>/', '[/p]', $strTemp);
        $strTemp = preg_replace('/\<\/P\>/', '[/p]', $strTemp);
        $strTemp = preg_replace('/\<.*?\>/', '', $strTemp);
        $strTemp = preg_replace('/\<.*?\>/', '', $strTemp);
        $strTemp = preg_replace('/\[br\]/', '<br />', $strTemp);
        $strTemp = preg_replace('/\[p\]/', '<p>', $strTemp);
        $strTemp = preg_replace('/\[\/p\]/', '</p>', $strTemp);
        return $strTemp;
    }

    private function removeHtml2($str){
        $strTemp = preg_replace('/\<.*?\>/', '', $str);
        return $strTemp;
    }

    /**
     * 本函数执行将传入的URL转化为对外站的URL，如果传入外站URL则不作转化
     * @param string $strUrl 传入要转化的URL
     * @param string $web_url 指定当前采集的URL
     * @return string
     */
    private function getUrl($strUrl,$web_url){
        $web_site = $this->inRoleArr['site_role'][0];
        $arrTemp = array();
        preg_match('/^.*\//', $web_url, $arrTemp);
        $cuUrl = $arrTemp[0];
        $tempUrl = $strUrl;
        $pos = strrpos($tempUrl,'/');
        if($pos === false){
            $tempUrl = $cuUrl.$tempUrl;
        }
        $pos = strrpos($tempUrl,'://');
        if($pos === false){
            $tempUrl = $web_site.$tempUrl;
        }
        return $tempUrl;
    }

    /**
     * 本函数执行从链接或图片显示HTML字符串中分析出链接URL，链接名称，图片URL
     * @param string $str 传入带链接或是图片显示HTML字符串
     * @param int $way　分析方式，0表示分析链接URL；1表示分析链接名称；2表示分析图片URL
     * @return string
     */
    private function getLink($str,$way = 0){
        $strTemp = $str;
        $arrTemp = array();
        if($way == 1){
            $re = '/(?<=\>)[\s|\S]*?(?=\<)/';
            preg_match($re, $strTemp, $arrTemp);
            $strTemp = preg_replace('/\r|\n/', '', $arrTemp[0]);
            $strTemp = preg_replace('/^\s+/', '', $strTemp);
            $strTemp = preg_replace('/\s+$/', '', $strTemp);
        }else if($way == 2){
            $re = '/(?<=src=)[\s|\S]*?(?=\s)/';
            preg_match($re, $strTemp, $arrTemp);
            $strTemp = preg_replace('/\r|\n/', '', $arrTemp[0]);
            $strTemp = preg_replace('/^\s+/', '', $strTemp);
            $strTemp = preg_replace('/\s+$/', '', $strTemp);
            $strTemp = preg_replace('/"|\'/', '', $strTemp);
        }else{
            $re = '/(?<=href=).*?(?=\s)/';
            preg_match($re, $strTemp, $arrTemp);
            $strTemp = preg_replace('/\r|\n/', '', $arrTemp[0]);
            $strTemp = preg_replace('/^\s+/', '', $strTemp);
            $strTemp = preg_replace('/\s+$/', '', $strTemp);
            $strTemp = preg_replace('/"|\'/', '', $strTemp);
        }
        return $strTemp;
    }

    /**
     * Collect类的析构函数，对本类所用资源进行释放
     */
    public function  __destruct() {
        unset($this->inCInter);
        unset($this->inRoleArr);
        unset($this->inResultArr);
        unset($this->inStr);
        unset($this->inCharset);
    }
}

?>