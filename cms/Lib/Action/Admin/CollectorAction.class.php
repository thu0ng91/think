<?php
/*
 * CollectorAction.class.php
 */

header("Content-Type:text/html;charset=utf-8");

/**
 * CollectorAction类
 * 采集电子书实现控制器类，此类从ThinkPHP的Action类继承
 * @version 1.0
 * @author wm_void
 */
class CollectorAction extends BaseAction {
    protected $objCollect;
    protected $collector_id;
    protected $collector_obj;
    protected $tempbook_obj;
    protected $tempchapter_obj;
    protected $tempcontent_obj;
    protected $templink_book_obj;
    protected $templink_chapter_obj;
    protected $temptype_obj;
    protected $templink_type_obj;
    protected $tempbook_list_obj;
    protected $tempurl_obj;

    /**
     * 采集电子书实现类的构造函数
     * 这里实现对所用的到几个表的数据模型的实例化，和部分成员变量的初始化
     *
     */
    public function  __construct() {
        parent::__construct();
        vendor('Collect.Collect');
        $this->objCollect = new Collect();
        $this->collector_obj = M('Collector');
        $this->tempbook_obj = M('Temp_book');
        $this->tempchapter_obj = M('Temp_chapter');
        $this->tempcontent_obj = M('Temp_content');
        $this->temptype_obj = M('Temp_type');
        $this->templink_type_obj = M('Temp_link_type');
        $this->templink_book_obj = M('Temp_link_book');
        $this->templink_chapter_obj = M('Temp_link_chapter');
        $this->tempbook_list_obj = M('Temp_book_list');
        $this->tempurl_obj = M('Temp_url');
        $this->collector_id = $_GET['collector_id'];
        $listTemp = $this->collector_obj->where("collector_id = {$this->collector_id}")->select();
        //设置采集器规则，三个规则同时设置
        $this->objCollect->setRole($listTemp[0]['collector_site_role'], $listTemp[0]['collector_book_role'], $listTemp[0]['collector_chapter_role']);
    }

    /**
     * 本类引导函数
     */
    public function Index(){
        $this->listCollector();
    }

    /**
     * 采集器列表显示
     */
    public function listCollector(){
        $listTemp = $this->collector_obj->order('collector_update_date desc')->select();
        $this->assign('list', $listTemp);
        $this->display('./Public/admin/collector/listcollector.html');
    }

    /**
     * 添加采集器,显示页面
     */
    public function addCollector(){
        $this->display('./Public/admin/collector/addcollector.html');
    }

    /**
     * 添加采集器执行动作函数
     */
    public function addCollectorCGI(){
        $dateTemp = date('Y-m-d H:i:s');
        $collector_name = $_POST['collector_name'];
        $site_coding = $_POST['site_coding'];
        $local_img = $_POST['local_img'];
        $collector_site_role = $_POST['collector_site_role'];
        $collector_site_role[7] = $site_coding;
        $collector_site_role[8] = $local_img;
        $collector_addr = $collector_site_role[0];
        $collector_book_role = $_POST['collector_book_role'];
        $collector_chapter_role = $_POST['collector_chapter_role'];

        $data = array(
            'collector_name' => $collector_name,
            'collector_create_date' => $dateTemp,
            'collector_update_date' => $dateTemp,
            'collector_addr' => $collector_addr,
            //这里将规则数组转化成文本字符串存入数据库
            'collector_site_role' => $this->objCollect->startArrayToText($collector_site_role),
            'collector_book_role' => $this->objCollect->startArrayToText($collector_book_role),
            'collector_chapter_role' => $this->objCollect->startArrayToText($collector_chapter_role)
        );
        $this->collector_obj->add($data);
        $this->jumpPage('采集器添加成功，页面跳转中', '?s=admin/collector/listcollector');        
    }

    /**
     * 修改采集器,显示页面
     */
    public function alterCollector(){
        $collector_site_role = array();
        $collector_book_role = array();
        $collector_chapter_role = array();
        $listTemp = $this->collector_obj->where("collector_id = {$this->collector_id}")->select();
        //这里把从数据库里读出来的规则文本转化成数组
        $collector_site_role = $this->objCollect->startTextToArray($listTemp[0]['collector_site_role']);
        $collector_book_role = $this->objCollect->startTextToArray($listTemp[0]['collector_book_role']);
        $collector_chapter_role = $this->objCollect->startTextToArray($listTemp[0]['collector_chapter_role']);

        $this->assign('collector_name',$listTemp[0]['collector_name']);
        $this->assign('collector_id',$this->collector_id);
        for($i = 0; $i < count($collector_site_role); $i++){
             $this->assign("collector_site_role{$i}",htmlspecialchars($collector_site_role[$i]));
        }
        for($i = 0; $i < count($collector_book_role); $i++){
             $this->assign("collector_book_role{$i}",htmlspecialchars($collector_book_role[$i]));
        }
        for($i = 0; $i < count($collector_chapter_role); $i++){
             $this->assign("collector_chapter_role{$i}",htmlspecialchars($collector_chapter_role[$i]));
        }
        $this->display('./Public/admin/collector/altercollector.html');
    }

    /**
     * 修改采集器执行动作函数
     */
    public function alterCollectorCGI(){
        $dateTemp = date('Y-m-d H:i:s');
        $collector_id = $_POST['collector_id'];
        $collector_name = $_POST['collector_name'];
        $site_coding = $_POST['site_coding'];
        $local_img = $_POST['local_img'];
        $collector_site_role = $_POST['collector_site_role'];
        $collector_site_role[7] = $site_coding;
        $collector_site_role[8] = $local_img;
        $collector_addr = $collector_site_role[0];
        $collector_book_role = $_POST['collector_book_role'];
        $collector_chapter_role = $_POST['collector_chapter_role'];

        $data = array(
            'collector_id' => $collector_id,
            'collector_name' => $collector_name,
            'collector_update_date' => $dateTemp,
            'collector_addr' => $collector_addr,
            //这里将规则数组转化成文本字符串存入数据库
            'collector_site_role' => $this->objCollect->startArrayToText($collector_site_role),
            'collector_book_role' => $this->objCollect->startArrayToText($collector_book_role),
            'collector_chapter_role' => $this->objCollect->startArrayToText($collector_chapter_role)
        );
        $this->collector_obj->save($data);
        $this->jumpPage('采集器修改成功，页面跳转中', '?s=admin/collector/listcollector');

    }

    /**
     * 删除采集器动作函数
     */
    public function deleteCollector(){
        $this->collector_obj->where("collector_id = {$this->collector_id}")->delete();
        $this->jumpPage('删除采集器成功，页面跳转中', '?s=admin/collector/listcollector');
    }

    /**
     * 获取书库书目列表
     */
    public function getBookList(){
        $collector_addr = $_GET['collector_addr'];
        $listTemp = $this->objCollect->getBookList($collector_addr);
        $dateNow = date('Y-m-d H:i:s');
        $countTemp = count($listTemp);
        $strListId = '';
        for($i = 0; $i < $countTemp; $i++){
            $dataTemp = array(
                'collector_id' => $this->collector_id,
                'book_name' => $listTemp[$i]['book_name'],
                'book_url' => $listTemp[$i]['book_url'],
                'get_date' => $dateNow
            );
            $book_name = $dataTemp['book_name'];
            $arrTemp = array();
            $listCount = $this->tempbook_list_obj->where("book_url = '{$dataTemp['book_url']}' AND collector_id = {$this->collector_id}")->count();
            if($listCount == 0){
                $this->tempbook_list_obj->add($dataTemp);
                $list_id = $this->tempbook_list_obj->getLastInsID();
                $strListId .= $list_id.'::'.$book_name.',';
            }else{
                $arrTemp = $this->tempbook_list_obj->where("book_url = '{$dataTemp['book_url']}'")->select();
                $list_id = $arrTemp[0]['list_id'];
                //$isCollect = $arrTemp[0]['iscollect'];
                //if($isCollect == 0){
                    $strListId .= $list_id.'::'.$book_name.',';
                //}
            }
        }
        echo $strListId;
    }

    /**
     * 从规则中分析出书库URL
     */
    public function getBookListUrl(){
        $collector_site_role = array();
        $listTemp = $this->collector_obj->where("collector_id = {$this->collector_id}")->select();
        //这里把从数据库里读出来的规则文本转化成数组
        $collector_site_role = $this->objCollect->startTextToArray($listTemp[0]['collector_site_role']);
        $web_addr = $collector_site_role[1];
        $web_page = $collector_site_role[3];
        $strUrlList = '';
        $web_page_arr = preg_split('/-/', $web_page);
        for($i = $web_page_arr[0]; $i <= $web_page_arr[1]; $i++){
            $strUrlList .= preg_replace('/\{\*\}/', $i, $web_addr).',';
        }
        echo $strUrlList;
    }

    /**
     * 获取书籍信息
     */
    public function getBookInfo(){
        $list_id = $_GET['list_id'];
        $dateNow = date('Y-m-d H:i:s');
        $listTemp =  $this->tempbook_list_obj->where("collector_id = {$this->collector_id} AND list_id = {$list_id}")->select();
        $book_url = $listTemp[0]['book_url'];
        $book_name = $listTemp[0]['book_name'];
        $listTempBook = $this->objCollect->getBookInfo($book_url);
        $book_man = $listTempBook[5];
        if($book_man != '' && $book_name != ''){
            $countBookTemp = $this->tempbook_obj->where("book_name = '{$book_name}' AND book_man = '{$book_man}'")->count();
            if($countBookTemp == 0){
                $dataTempBook = array(
                    'book_no' => $listTempBook[0],
                    'book_key' => $listTempBook[2],
                    'book_introduce' => $listTempBook[3],
                    'book_abstract' => $listTempBook[4],
                    'book_man' => $listTempBook[5],
                    'book_date' => $listTempBook[7],
                    'book_sum' => $listTempBook[8],
                    'book_affix' => $listTempBook[9],
                    'chapter_list_url' => $listTempBook[10],
                    'book_name' => $book_name,
                    'book_url' => $book_url,
                    'book_get_date' => $dateNow
                );
                $dataTempType = array(
                    'type_name' => $listTempBook[6]
                );
                $this->tempbook_obj->add($dataTempBook);
                $book_id = $this->tempbook_obj->getLastInsID();

                // 在数据库中查找此书类型名是否存在!
                $type_id = 0;
                $countTempType = $this->temptype_obj->where("type_name = '{$dataTempType['type_name']}'")->count();
                if($countTempType == 0){
                    $this->temptype_obj->add($dataTempType);
                    $type_id = $this->temptype_obj->getLastInsID();
                }else{
                    $arrTemp = $this->temptype_obj->where("type_name = '{$dataTempType['type_name']}'")->select();
                    $type_id = $arrTemp[0]['type_id'];
                }
                $dataTempLinkType = array(
                    'book_id' => $book_id,
                    'type_id' => $type_id
                );
                $this->templink_type_obj->add($dataTempLinkType);
                $dataTempListBook = array(
                    'list_id' => $list_id,
                    'iscollect' => 1
                );
                $this->tempbook_list_obj->save($dataTempListBook);
                echo $book_id.',';
            }else{
                $listBookTemp = $this->tempbook_obj->where("book_name = '{$book_name}' AND book_man = '{$book_man}'")->select();
                $book_id = $listBookTemp[0]['book_id'];
                $dataTempBook = array(
                    'book_id' => $book_id,
                    'book_no' => $listTempBook[0],
                    'book_key' => $listTempBook[2],
                    'book_introduce' => $listTempBook[3],
                    'book_abstract' => $listTempBook[4],
                    'book_man' => $listTempBook[5],
                    'book_date' => $listTempBook[7],
                    'book_sum' => $listTempBook[8],
                    'book_affix' => $listTempBook[9],
                    'chapter_list_url' => $listTempBook[10],
                    'book_name' => $book_name,
                    'book_url' => $book_url,
                    'book_get_date' => $dateNow
                );
                $this->tempbook_obj->save($dataTempBook);

                echo $book_id.'::'.$book_name.',';
            }
        }
    }

    /**
     * 获取章节信息
     */
    public function getBookChapter(){
        $book_id = $_GET['book_id'];
        $dateNow = date('Y-m-d H:i:s');
        $strChapterid = "";
        $listBookTemp = $this->tempbook_obj->where("book_id = {$book_id}")->select();
        // 从电子书信息表中读取章节列表URL
        $chapter_list_url = $listBookTemp[0]['chapter_list_url'];
        $book_name = $listBookTemp[0]['book_name'];
        $listChapterTemp = $this->objCollect->getBookChapter($chapter_list_url);
        for($i = 0; $i < count($listChapterTemp); $i++){
            $chapter_name = $listChapterTemp[$i][1];
            if($chapter_name != ''){
                $countChapterTemp = $this->tempchapter_obj->join("temp_link_book ON temp_link_book.chapter_id = temp_chapter.chapter_id")
                                                          ->where("chapter_name = '{$chapter_name}' AND book_id = {$book_id}")
                                                          ->count();
                if($countChapterTemp == 0){
                    $dataChapterTemp = array(
                        'chapter_no' => $listChapterTemp[$i][0],
                        'chapter_name' => $listChapterTemp[$i][1],
                        'chapter_sum' => $listChapterTemp[$i][2],
                        'chapter_update' => $listChapterTemp[$i][3],
                        'chapter_content_url' => $listChapterTemp[$i][4],
                        'chapter_volume' => $listChapterTemp[$i][5],
                        'chapter_get_date' => $dateNow
                    );
                    $this->tempchapter_obj->add($dataChapterTemp);
                    $chapter_id = $this->tempchapter_obj->getLastInsID();

                    $urlTemp = $listChapterTemp[$i][4];
                    $dataUrlTemp = array(
                        'chapter_id' => $chapter_id,
                        'url' => $urlTemp
                    );
                    $this->tempurl_obj->add($dataUrlTemp);
                    $strChapterid .= $chapter_id.'::'.$book_name.'::'.$chapter_name.',';

                    $dataLinkBookTemp = array(
                        'book_id' => $book_id,
                        'chapter_id' => $chapter_id
                    );
                    $this->templink_book_obj->add($dataLinkBookTemp);
                }else{
                    $listChapterTemp2 = $this->tempchapter_obj->join("temp_link_book ON temp_link_book.chapter_id = temp_chapter.chapter_id")
                                                          ->where("chapter_name = '{$chapter_name}' AND book_id = {$book_id}")
                                                          ->select();
                    $chapter_id = $listChapterTemp2[0]['chapter_id'];
                    // 根据URL作判断，此章节是否已采集过
                    $urlTemp = $listChapterTemp[$i][4];
                    $countUrlTemp = $this->tempurl_obj->where("url = '{$urlTemp}'")->count();
                    $state = 0;
                    if($countUrlTemp == 0){
                        $dataUrlTemp = array(
                            'chapter_id' => $chapter_id,
                            'url' => $urlTemp
                        );
                        $this->tempurl_obj->add($dataUrlTemp);
                    }else{
                        $listUrlTemp = $this->tempurl_obj->where("url = '{$urlTemp}'")->select();
                        $state = $listUrlTemp[0]['state'];
                    }
                    if($state == 0){
                        $strChapterid .= $chapter_id.'::'.$book_name.'::'.$chapter_name.',';
                    }

                }
            }
        }
        $dataBookTemp = array(
            'book_id' => $book_id,
            'book_state' => 1
        );
        $this->tempbook_obj->save($dataBookTemp);
        echo $strChapterid;
    }

   /**
    * 获取章节内容
    */
    public function getBookContent(){
        $chapter_id = $_GET['chapter_id'];
        $dateNow = date('Y-m-d H:i:s');
        $listUrlTemp = $this->tempurl_obj->where("chapter_id = {$chapter_id}")->select();
        // 从章节信息表中读取章节内容URL
        $content_url = $listUrlTemp[0]['url'];
        $url_id = $listUrlTemp[0]['id'];
        $listContentTemp = $this->objCollect->getBookContent($content_url);
        $content_text = $listContentTemp[4];
        if($content_text != ''){
            $dataContentTemp = array(
                'content_text' => $listContentTemp[4],
                'content_affix' => $listContentTemp[5]
            );
            $this->tempcontent_obj->add($dataContentTemp);
            $content_id = $this->tempcontent_obj->getLastInsID();
            $dataLinkChapterTemp = array(
                'chapter_id' => $chapter_id,
                'content_id' => $content_id
            );
            $this->templink_chapter_obj->add($dataLinkChapterTemp);

            //　这里下面用了几个?:表达式，主要是判断，章节表里这些信息采集到没有，如没采到，则在章节内容页里采集到的数据更新
            $dataChapterTemp = array(
                'chapter_id' => $chapter_id,
                'chapter_no' => $listChapterTemp[0]['chapter_no'] != '' ? $listChapterTemp[0]['chapter_no'] : $listContentTemp[0],
                //'chapter_name' => $listChapterTemp[0]['chapter_name'] != '' ? $listChapterTemp[0]['chapter_name'] : $listContentTemp[1],
                'chapter_sum' => $listChapterTemp[0]['chapter_sum'] != 0 ? $listChapterTemp[0]['chapter_sum'] : $listContentTemp[2],
                'chapter_update' => $listChapterTemp[0]['chapter_update'] != null ? $listChapterTemp[0]['chapter_update'] : $listContentTemp[3]
            );
            $this->tempchapter_obj->save($dataChapterTemp);

            $dataUrlTemp = array(
                'id' => $url_id,
                'state' => 1
            );
            $this->tempurl_obj->save($dataUrlTemp);
            }
    }

    /**
     * 单篇采集，显示页面
     */
    public function getBookOne(){
        $this->assign('collector_id', $this->collector_id);
        $this->display('./Public/admin/collector/getbookone.html');
    }

    /**
     * 单篇采集，执行动作函数
     */
    public function getBookOneCgi(){
        $book_url = $_GET['book_url'];
        $dateNow = date('Y-m-d H:i:s');
        $listTempBook = $this->objCollect->getBookInfo($book_url);
        $book_man = $listTempBook[5];
        $book_name = $listTempBook[1];
        if($book_man != '' && $book_name != ''){
            $countBookTemp = $this->tempbook_obj->where("book_name = '{$book_name}' AND book_man = '{$book_man}'")->count();
            if($countBookTemp == 0){
                $dataTempBook = array(
                    'book_no' => $listTempBook[0],
                    'book_key' => $listTempBook[2],
                    'book_introduce' => $listTempBook[3],
                    'book_abstract' => $listTempBook[4],
                    'book_man' => $listTempBook[5],
                    'book_date' => $listTempBook[7],
                    'book_sum' => $listTempBook[8],
                    'book_affix' => $listTempBook[9],
                    'chapter_list_url' => $listTempBook[10],
                    'book_name' => $book_name,
                    'book_url' => $book_url,
                    'book_get_date' => $dateNow
                );
                $dataTempType = array(
                    'type_name' => $listTempBook[6]
                );
                $this->tempbook_obj->add($dataTempBook);
                $book_id = $this->tempbook_obj->getLastInsID();

                // 在数据库中查找此书类型名是否存在!
                $type_id = 0;
                $countTempType = $this->temptype_obj->where("type_name = '{$dataTempType['type_name']}'")->count();
                if($countTempType == 0){
                    $this->temptype_obj->add($dataTempType);
                    $type_id = $this->temptype_obj->getLastInsID();
                }else{
                    $arrTemp = $this->temptype_obj->where("type_name = '{$dataTempType['type_name']}'")->select();
                    $type_id = $arrTemp[0]['type_id'];
                }
                $dataTempLinkType = array(
                    'book_id' => $book_id,
                    'type_id' => $type_id
                );
                $this->templink_type_obj->add($dataTempLinkType);
                $dataTempListBook = array(
                    'list_id' => $list_id,
                    'iscollect' => 1
                );
                $this->tempbook_list_obj->save($dataTempListBook);
                echo $book_id.',';
            }else{
                $listBookTemp = $this->tempbook_obj->where("book_name = '{$book_name}' AND book_man = '{$book_man}'")->select();
                $book_id = $listBookTemp[0]['book_id'];
                $dataTempBook = array(
                    'book_id' => $book_id,
                    'book_no' => $listTempBook[0],
                    'book_key' => $listTempBook[2],
                    'book_introduce' => $listTempBook[3],
                    'book_abstract' => $listTempBook[4],
                    'book_man' => $listTempBook[5],
                    'book_date' => $listTempBook[7],
                    'book_sum' => $listTempBook[8],
                    'book_affix' => $listTempBook[9],
                    'chapter_list_url' => $listTempBook[10],
                    'book_name' => $book_name,
                    'book_url' => $book_url,
                    'book_get_date' => $dateNow
                );
                $this->tempbook_obj->save($dataTempBook);
                echo $book_id.',';
            }
        }
    }

    /**
     * 导入采集器规则和更新相关表
     */
    public function importCollector(){
        $this->display('./Public/admin/collector/importcollector.html');
    }

    /**
     * 导入采集器规则和更新相关表接口
     */
    public function importCollectorCGI(){
        $dbModle = M();
        $sqlFileName = $this->upload_file($_FILES['filename'],'sql');
        $strSql = file_get_contents(realpath($sqlFileName));

        // 将SQL文件里的注释去掉，再将SQL语句拆成单条执行
        $strSql = preg_replace('/--.*/', '', $strSql);
        $strSql = preg_replace('/;\n/', '####n####', $strSql);
        $strSql = preg_replace('/\n/', '', $strSql);
        $arrSql = preg_split('/####n####/', $strSql);

        // 执行SQL语句
        for($i = 0; $i < count($arrSql); $i++){
            if($arrSql[$i] != ''){
                $dbModle->execute($arrSql[$i]);
                //$dbModle->query($arrSql[$i]);

            }
        }
        
        // 更新数据库字段缓存
        $arrFile = scandir('Runtime/Data/_fields');
        for($i = 0; $i < count($arrFile); $i++){
            if($arrFile[$i] != '.' || $arrFile[$i] != '..'){
                unlink('Runtime/Data/_fields/'.$arrFile[$i]);
            }
        }
        $this->jumpPage('数据库更新成功', '?s=admin/collector/listcollector');
    }

    private function upload_file($arrFileName,$strPrefix,$strDir = 'files/update/'){
        date_default_timezone_set("Asia/Shanghai");
        $dateTemp = date("YmdHis");
        $arrTemp = preg_split('/\./', $arrFileName['name']);
        $strExt = $arrTemp[1];
        $strNewFileName = $strDir.$strPrefix.'_'.$dateTemp.'.'.$strExt;
        move_uploaded_file($arrFileName['tmp_name'], $strNewFileName);
        return $strNewFileName;
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