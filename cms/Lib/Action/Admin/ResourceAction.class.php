<?php
/*
 * ResourceAction.class.php
 */

header("Content-Type:text/html;charset=utf-8");

/**
 * 资源库控制器实现类
 * @version 1.0
 * @author wm_void
 */
class ResourceAction extends BaseAction{
    protected $tempbook_obj;
    protected $tempchapter_obj;
    protected $templinkchapter_obj;
    protected $templinkbook_obj;
    protected $tempcontent_obj;
    protected $temptype_obj;
    protected $templinktype_obj;
    protected $templinkresource_obj;
    protected $objXml;
    protected $rs_url;

        /**
     * 电子书信息类的构造函数
     */
    public function  __construct() {
        parent::__construct();
        vendor('Resource.Xml');
        $this->objXml = new Xml();
        $this->tempbook_obj = M('Temp_book');
        $this->tempchapter_obj = M('Temp_chapter');
        $this->templinkchapter_obj = M('Temp_link_chapter');
        $this->templinkbook_obj = M('Temp_link_book');
        $this->tempcontent_obj = M('Temp_content');
        $this->temptype_obj = M('Temp_type');
        $this->templinktype_obj = M('Temp_link_type');
        $this->templinkresource_obj = M('Temp_link_resource');
        $this->rs_url = 'http://192.168.18.233/itrs/';
    }

    /**
     * 本类索引函数
     */
    public function Index(){
        $this->listResource();
    }
    
    /**
     * 资源库列表
     */
    public function listResource(){
        $url = "{$this->rs_url}resourcelist.php";
        $rsList = $this->objXml->getResourceList($url);
        for($i = 0; $i < count($rsList); $i++){
            $strTemp = '<font color="green">无更新</font>';
            $strTemp2 = '<font color="green">最新</font>';
            $nCount = 0;
            $urlTemp = $rsList[$i]['rs_addr'];
            $url3 = "{$urlTemp}booklist.php?rs_id={$rsList[$i]['rs_id']}";
            $bookList = $this->objXml->getBookList($url3);
            for($j = 0; $j < count($bookList); $j++){
                $countTemp = $this->templinkresource_obj->where("book_id1 = {$bookList[$j]['book_id']}")->count();
                if($countTemp == 0){
                    $nCount++;
                }else{
                    $url4 = "{$urlTemp}chapterlist.php?book_id={$bookList[$j]['book_id']}";
                    $chapterList = $this->objXml->getChapterList($url4);
                    if($countTemp < count($chapterList)){
                        $strTemp = '<font color="red">章节有更新</font>';
                        $strTemp2 = '<font color="red">更新</font>';
                    }
                }
            }
            if($nCount == count($bookList)){
                $strTemp = '<font color="blue">未入库</font>';
                $strTemp2 = '<font color="blue">入库</font>';
            }else if($nCount > 0){
                $strTemp = '<font color="red">书有更新</font>';
                $strTemp2 = '<font color="red">更新</font>';
            }
            $rsList[$i]['state'] = $strTemp;
            $rsList[$i]['state2'] = $strTemp2;
        }
        $this->assign('list', $rsList);
        $this->display('./Public/admin/collector/listresource.html');
    }

    public function listBook(){
        $rs_id = isset($_GET['rs_id'])?$_GET['rs_id']:0;
        $type_id = isset($_GET['type_id'])?$_GET['type_id']:0;
        $bookList = array();
        $way = '';
        $url = "{$this->rs_url}resourcelist.php";
        $rslist = $this->objXml->getResourceList($url);
        $urlTemp = '';
        for($i = 0; $i < count($rslist); $i++){
            if($rslist[$i]['rs_id'] == $rs_id){
                $urlTemp = $rslist[$i]['rs_addr'];
            }
        }
        if($type_id == 0){
            $url = "{$urlTemp}booklist.php?rs_id={$rs_id}";
            $bookList = $this->objXml->getBookList($url);
            $way = "/rs_id/{$rs_id}";
        }else if($type_id != 0){
            $url = "{$urlTemp}typelistbook.php?rs_id={$rs_id}&type_id={$type_id}";
            $bookList = $this->objXml->getBookList($url);
            $way = "rs_id/{$rs_id}/type_id/{$type_id}";
        }
        for($j = 0; $j < count($bookList); $j++){
            $strTemp = '<font color="green">无更新</font>';
            $strTemp2 = '<font color="green">最新</font>';
            $countTemp = $this->templinkresource_obj->where("book_id1 = {$bookList[$j]['book_id']}")->count();
            $url4 = "{$urlTemp}chapterlist.php?book_id={$bookList[$j]['book_id']}";
            $chapterList = $this->objXml->getChapterList($url4);
            if($countTemp == 0){
                $strTemp = '<font color="blue">未入库</font>';
                $strTemp2 = '<font color="blue">入库</font>';
            }else if($countTemp < count($chapterList)){
                $strTemp = '<font color="red">章节有更新</font>';
                $strTemp2 = '<font color="red">更新</font>';
            }
            $bookList[$j]['state'] = $strTemp;
            $bookList[$j]['state2'] = $strTemp2;
        }
        $url2 = "{$urlTemp}typelist.php?rs_id={$rs_id}";
        $typeList = $this->objXml->getTypeList($url2);
        for($i = 0; $i < count($typeList); $i++){
            $typeList[$i]['rs_id'] = $rs_id;
        }
        $this->assign('list1', $typeList);
        $this->assign('way', $way);
        $this->assign('rs_id', $rs_id);
        $this->assign('list', $bookList);
        $this->display('./Public/admin/collector/listbook.html');
    }

    public function listChapter(){
        $book_id = isset($_GET['book_id'])?$_GET['book_id']:0;
        $rs_id = isset($_GET['rs_id'])?$_GET['rs_id']:0;
        $url = "{$this->rs_url}resourcelist.php";
        $rslist = $this->objXml->getResourceList($url);
        $urlTemp = '';
        for($i = 0; $i < count($rslist); $i++){
            if($rslist[$i]['rs_id'] == $rs_id){
                $urlTemp = $rslist[$i]['rs_addr'];
            }
        }
        $url = "{$urlTemp}chapterlist.php?book_id={$book_id}";
        $chapterList = $this->objXml->getChapterList($url);
        $chapterListTitle = $this->objXml->getChapterListTitle($url);
        $this->assign('list', $chapterList);
        $this->assign('title', $chapterListTitle);
        $this->display('./Public/admin/collector/listchapter.html');
    }

    public function putbook(){
        $dateNow = date('Y-m-d H:i:s');
        $book_id = preg_split('/,/', isset($_GET['book_id'])?$_GET['book_id']:0);
        $rs_id = isset($_GET['rs_id'])?$_GET['rs_id']:0;
        $type_id = isset($_GET['type_id'])?$_GET['type_id']:0;
        $bookList = array();
        $url = "{$this->rs_url}resourcelist.php";
        $rslist = $this->objXml->getResourceList($url);
        $urlTemp = '';
        for($i = 0; $i < count($rslist); $i++){
            if($rslist[$i]['rs_id'] == $rs_id){
                $urlTemp = $rslist[$i]['rs_addr'];
            }
        }
        if($type_id == 0){
            $url = "{$urlTemp}booklist.php?rs_id={$rs_id}";
            $bookList = $this->objXml->getBookList($url);
        }else if($type_id != 0){
            $url = "{$urlTemp}typelistbook.php?rs_id={$rs_id}&type_id={$type_id}";
            $bookList = $this->objXml->getBookList($url);
        }
        
        for($i = 0; $i < count($bookList); $i++){
            if($book_id[0] == 'all'){
                $dataTempBook = array(
                    'book_name' => $bookList[$i]['book_name'],
                    'book_man' => $bookList[$i]['book_man'],
                    'book_sum' => $bookList[$i]['book_sum'],
                    'book_date' => $bookList[$i]['book_date'],
                    'book_get_date' => $dateNow,
                    'book_state' => 1,
                    'book_affix' => $bookList[$i]['book_affix'],
                    'book_key' => $bookList[$i]['book_key'],
                    'book_introduce' => $bookList[$i]['book_introduce'],
                    'book_abstract' => $bookList[$i]['book_abstract']
                );
                $dataTempType = array(
                    'type_name' => $bookList[$i]['type_name']
                );

                $book_id1 = $bookList[$i]['book_id'];
                $book_id2 = '';

                // 在数据库中查找此书是否已入库
                $listTempCount = $this->templinkresource_obj->where("book_id1 = {$book_id1}")->count();
                if($listTempCount == 0){
                    $this->tempbook_obj->add($dataTempBook);
                    $book_id2 = $this->tempbook_obj->getLastInsID();
                }else{
                    $listTemp = $this->templinkresource_obj->where("book_id1 = {$book_id1}")->select();
                    $book_id2 = $listTemp[0]['book_id2'];
                }

                // 在数据库中查找此书类型名是否存在!
                $type_id1 = 0;
                $countTempType = $this->temptype_obj->where("type_name = '{$dataTempType['type_name']}'")->count();
                if($countTempType == 0){
                    $this->temptype_obj->add($dataTempType);
                    $type_id1 = $this->temptype_obj->getLastInsID();
                }else{
                    $arrTemp = $this->temptype_obj->where("type_name = '{$dataTempType['type_name']}'")->select();
                    $type_id1 = $arrTemp[0]['type_id'];
                }
                $dataTempLinkType = array(
                    'book_id' => $book_id2,
                    'type_id' => $type_id1
                );
                $this->templinktype_obj->add($dataTempLinkType);

                $url = "{$urlTemp}chapterlist.php?book_id={$book_id1}";
                $chapterList = $this->objXml->getChapterList($url);
                for($j = 0; $j < count($chapterList); $j++){
                    $dataTempChapter = array(
                        'chapter_name' => $chapterList[$j]['chapter_name'],
                        'chapter_volume' => $chapterList[$j]['chapter_volume'],
                        'chapter_sum' => $chapterList[$j]['chapter_sum'],
                        'chapter_update' => $chapterList[$j]['chapter_update'],
                        'chapter_get_date' => $dateNow
                    );

                    // 在数据库中查找此章节是否已入库
                    $listTempCount = $this->templinkresource_obj->where("book_id1 = {$book_id1}　AND chapter_id1 = {$dataTempChapter[$j]['chapter_id']}")->count();
                    if($listTempCount == 0){
                        $this->tempchapter_obj->add($dataTempChapter);

                        $chapter_id1 = $chapterList[$j]['chapter_id'];
                        $chapter_id2 = $this->tempchapter_obj->getLastInsID();

                        $dataTempLinkBook = array(
                                'book_id' => $book_id2,
                                'chapter_id' => $chapter_id2
                        );
                        $this->templinkbook_obj->add($dataTempLinkBook);

                        $dataTempLinkResource = array(
                            'book_id1' => $book_id1,
                            'book_id2' => $book_id2,
                            'chapter_id1' => $chapter_id1,
                            'chapter_id2' => $chapter_id2
                        );
                        $this->templinkresource_obj->add($dataTempLinkResource);
                    }
                }
                $url = "{$urlTemp}contentlist.php?book_id={$book_id1}";
                $contentList = $this->objXml->getContentList($url);
                for($j = 0; $j < count($contentList); $j++){
                    $listTempCount = $this->templinkresource_obj->where("chapter_id2 = {$contentList[$j]['chapter_id']}")->count();
                    if($listTempCount == 0){
                        $dataTempContent = array(
                            'content_text' => $contentList[$j]['content_text'],
                            'content_affix' => $contentList[$j]['content_affix']
                        );
                        $this->tempcontent_obj->add($dataTempContent);
                        $content_id = $this->tempcontent_obj->getLastInsID();

                        $listTemp = $this->templinkresource_obj->where("chapter_id2 = {$contentList[$j]['chapter_id']}")->select();
                        $chapter_id = $listTemp[0]['chapter_id1'];
                        if($chapter_id != ''){
                            $dataTempLinkChapter = array(
                                'chapter_id' => $chapter_id,
                                'content_id' => $content_id
                            );
                            $this->templinkchapter_obj->add($dataTempLinkChapter);
                        }
                    }
                }
            }else if($book_id[0] != 'all'){
                for($k = 0; $k < count($book_id); $k++){
                    if($book_id[$k] == $bookList[$i]['book_id']){
                        $dataTempBook = array(
                            'book_name' => $bookList[$i]['book_name'],
                            'book_man' => $bookList[$i]['book_man'],
                            'book_sum' => $bookList[$i]['book_sum'],
                            'book_date' => $bookList[$i]['book_date'],
                            'book_get_date' => $dateNow,
                            'book_state' => 1,
                            'book_affix' => $bookList[$i]['book_affix'],
                            'book_key' => $bookList[$i]['book_key'],
                            'book_introduce' => $bookList[$i]['book_introduce'],
                            'book_abstract' => $bookList[$i]['book_abstract']
                        );
                        $dataTempType = array(
                            'type_name' => $bookList[$i]['type_name']
                        );

                        $book_id1 = $bookList[$i]['book_id'];
                        $book_id2 = '';

                        // 在数据库中查找此书是否已入库
                        $listTempCount = $this->templinkresource_obj->where("book_id1 = {$book_id1}")->count();
                        if($listTempCount == 0){
                            $this->tempbook_obj->add($dataTempBook);
                            $book_id2 = $this->tempbook_obj->getLastInsID();
                        }else{
                            $listTemp = $this->templinkresource_obj->where("book_id1 = {$book_id1}")->select();
                            $book_id2 = $listTemp[0]['book_id2'];
                        }

                        // 在数据库中查找此书类型名是否存在!
                        $type_id1 = 0;
                        $countTempType = $this->temptype_obj->where("type_name = '{$dataTempType['type_name']}'")->count();
                        if($countTempType == 0){
                            $this->temptype_obj->add($dataTempType);
                            $type_id1 = $this->temptype_obj->getLastInsID();
                        }else{
                            $arrTemp = $this->temptype_obj->where("type_name = '{$dataTempType['type_name']}'")->select();
                            $type_id1 = $arrTemp[0]['type_id'];
                        }
                        $dataTempLinkType = array(
                            'book_id' => $book_id2,
                            'type_id' => $type_id1
                        );
                        $this->templinktype_obj->add($dataTempLinkType);

                        $url = "{$urlTemp}chapterlist.php?book_id={$book_id1}";
                        $chapterList = $this->objXml->getChapterList($url);
                        for($j = 0; $j < count($chapterList); $j++){
                            $dataTempChapter = array(
                                'chapter_name' => $chapterList[$j]['chapter_name'],
                                'chapter_volume' => $chapterList[$j]['chapter_volume'],
                                'chapter_sum' => $chapterList[$j]['chapter_sum'],
                                'chapter_update' => $chapterList[$j]['chapter_update'],
                                'chapter_get_date' => $dateNow
                            );
                            // 在数据库中查找此章节是否已入库
                            $listTempCount = $this->templinkresource_obj->where("book_id1 = {$book_id1}　AND chapter_id1 = {$dataTempChapter[$j]['chapter_id']}")->count();
                            if($listTempCount == 0){
                                $this->tempchapter_obj->add($dataTempChapter);

                                $chapter_id1 = $chapterList[$j]['chapter_id'];
                                $chapter_id2 = $this->tempchapter_obj->getLastInsID();

                                $dataTempLinkBook = array(
                                        'book_id' => $book_id2,
                                        'chapter_id' => $chapter_id2
                                );
                                $this->templinkbook_obj->add($dataTempLinkBook);

                                $dataTempLinkResource = array(
                                    'book_id1' => $book_id1,
                                    'book_id2' => $book_id2,
                                    'chapter_id1' => $chapter_id1,
                                    'chapter_id2' => $chapter_id2
                                );
                                $this->templinkresource_obj->add($dataTempLinkResource);
                            }
                        }
                        $url = "{$urlTemp}contentlist.php?book_id={$book_id1}";
                        $contentList = $this->objXml->getContentList($url);
                        for($j = 0; $j < count($contentList); $j++){
                            $listTempCount = $this->templinkresource_obj->where("chapter_id2 = {$contentList[$j]['chapter_id']}")->count();
                            if($listTempCount == 0){
                                $dataTempContent = array(
                                    'content_text' => $contentList[$j]['content_text'],
                                    'content_affix' => $contentList[$j]['content_affix']
                                );
                                $this->tempcontent_obj->add($dataTempContent);
                                $content_id = $this->tempcontent_obj->getLastInsID();

                                $listTemp = $this->templinkresource_obj->where("chapter_id2 = {$contentList[$j]['chapter_id']}")->select();
                                $chapter_id = $listTemp[0]['chapter_id1'];
                                if($chapter_id != ''){
                                    $dataTempLinkChapter = array(
                                        'chapter_id' => $chapter_id,
                                        'content_id' => $content_id
                                    );
                                    $this->templinkchapter_obj->add($dataTempLinkChapter);
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->jumpPage('资源入库成功，页面跳转中', '?s=admin/resource/listresource');
    }

    /**
     * 页面跳转函数
     * @param string $strInfo 跳转说明
     * @param string $strUrl　跳转URL
     * @param int $nTime 跳转时间，默认为2秒
     */
    private function jumpPage($strInfo,$strUrl,$nTime = 2){
        $this->assign('jumpInfo', $strInfo);
        $this->assign('jumpUrl', $strUrl);
        $this->assign('jumpTime', $nTime);
        $this->display('./Public/admin/collector/jumppage.html');
    }
}
?>
