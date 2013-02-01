<?php
/* 
 * Interface.php
 */

/**
 * CollectInter接口
 * 本接口对采集器所提供的函数进行封装
 * @version 1.0
 * @author wm_void
 */
Interface CollectInter {
    /**
     * 本函数用于获取指定URL的页面内容
     * @method getUrlContent($strUrl)
     * @param string $strUrl 指定要获取页面的URL
     * @access public
     * @return string
     */
    function getUrlContent($strUrl);

    /**
     * 本函数用于将获取的页面内容存入本地文件
     * @method saveContent($strFlieName)
     * @param string $strFlieName 指定要输出到的本地文件路径
     * @access public
     * @return bool
     */
    function saveContent($strFlieName);
}
?>
