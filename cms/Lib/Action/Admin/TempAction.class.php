<?php
/* 
 * TempAction.class.php
 */

header("Content-Type:text/html;charset=utf-8");

/**
 * 电子书信息控制器实现类
 * @version 1.0 
 * @author wm_void
 */
class TempAction extends BaseAction {
    protected $tempbook_obj;
    protected $tempchapter_obj;
    protected $book_obj;
    protected $bookchapter_obj;
    protected $book_sort;
    protected $templinkchapter_obj;
    protected $templinkbook_obj;
    protected $tempcontent_obj;
    protected $temptype_obj;
    protected $templink_type_obj;
    protected $tempbook_list_obj;
    protected $templinkresource_obj;
    protected $tempurl_obj;

    /**
     * 电子书信息类的构造函数
     */
    public function  __construct() {
        parent::__construct();
        $this->tempbook_obj = M('Temp_book');
        $this->tempchapter_obj = M('Temp_chapter');
        $this->templinkchapter_obj = M('Temp_link_chapter');
        $this->templinkbook_obj = M('Temp_link_book');
        $this->tempcontent_obj = M('Temp_content');
        $this->book_obj = M('Book');
        $this->bookchapter_obj = M('Book_chapter');
        $this->book_sort = M('Book_sort');
        $this->temptype_obj = M('Temp_type');
        $this->templink_type_obj = M('Temp_link_type');
        $this->tempbook_list_obj = M('Temp_book_list');
        $this->templinkresource_obj = M('Temp_link_resource');
        $this->tempurl_obj = M('Temp_url');
    }

    /**
     * 本类的索引类
     */
    public function Index(){
        $this->tempManage();
    }

    /**
     * 获取指定状态的临时库中所有电子书的ID字符串
     */
    public function getBookId(){
        $book_state = $_GET['s'];
        $strBookId = "";
        if($book_state != ''){
            $listTemp = $this->tempbook_obj->where("book_state = {$book_state}")->select();
            for($i = 0; $i < count($listTemp); $i++){
                $strBookId .= $listTemp[$i]['book_id'].',';
            }
        }
        echo $strBookId;
    }

    /**
     * 删除临时库中书信息的动作函数
     */
    public function deleteBookInfo(){
        $book_id = $_GET['book_id'];
        $this->tempbook_obj->where("book_id = {$book_id}")->delete();
        $this->jumpPage('成功从临时库中删除，页面跳转中', '?s=admin/temp/tempmanage');
    }

    /**
     * 执行临时库中电子书的入正式库
     */
    public function putBook(){
        $time = time();
        $book_id = preg_split('/,/', $_GET['book_id']);
        for($i = 0; $i < count($book_id); $i++){
            if($book_id[$i] != ''){
                $listTempBook = $this->tempbook_obj->join("temp_link_type ON temp_book.book_id = temp_link_type.book_id")->join("temp_type ON temp_link_type.type_id = temp_type.type_id")->where("temp_book.book_id = {$book_id[$i]}")->select();
                $listTempChapter = $this->tempbook_obj->join("temp_link_book ON temp_book.book_id = temp_link_book.book_id")->join("temp_chapter ON temp_link_book.chapter_id = temp_chapter.chapter_id")->join("temp_link_chapter ON temp_chapter.chapter_id = temp_link_chapter.chapter_id")->join("temp_content ON temp_link_chapter.content_id = temp_content.content_id")->where("temp_book.book_id = {$book_id[$i]}")->select();
                $dataBook = array(
                    'sort_id' => $listTempBook[0]['sort_id'],
                    'book_name' => $listTempBook[0]['book_name'],
                    'author' => $listTempBook[0]['book_man'],
                    'keywords' => $listTempBook[0]['book_key'],
                    'last_update' => idate('U',strtotime($listTempBook[0]['book_date'])),
                    'post_time' => $time,
                    'is_vip' => 0,
                    'is_power' => 1,
                    'is_first' => 0,
                    'is_full' => 1,
                    'total_size' => $listTempBook[0]['book_sum'],
                    'image_url' => $listTempBook[0]['book_affix'],
                    'introduce' => $listTempBook[0]['book_introduce']
                );
                $this->book_obj->add($dataBook);
                $book_id_insert = $this->book_obj->getLastInsID();
                $data['book_state'] = 2;
                $data['post_id'] = $book_id_insert;
                $data['book_put_date'] = date('Y-m-d H:i:s');
                $this->tempbook_obj->where("book_id = {$book_id[$i]}")->save($data);
                $countTemp = count($listTempChapter);
                for($j = 0; $j < $countTemp; $j++){
                    $dataChapter = array(
                        'chapter_name' => $listTempChapter[$j]['chapter_name'],
                        'book_id' => $book_id_insert,
                        'chapter_detail' => $listTempChapter[$j]['content_text'],
                        'chapter_size' => $listTempChapter[$j]['chapter_sum'],
                        'volume_id' => $listTempChapter[$j]['chapter_volume'],
                        'update_time' => $listTempChapter[$j]['chapter_update'],
                        'post_time' => $time,
                        'chapter_order' => $j
                    );
                    $this->bookchapter_obj->add($dataChapter);
                }
                // 最后更新章节放入书籍表
                $chapter_id = $this->bookchapter_obj->getLastInsID();
                $countTemp = $countTemp - 1;
                $dataBookTemp2 = array(
                    'last_chapterid' => $chapter_id,
                    'last_chapter' => $listTempChapter[$countTemp]['chapter_name'],
                    'book_id' => $book_id_insert
                );
                $this->book_obj->save($dataBookTemp2);
            }
        }
        $this->jumpPage('入库成功，页面跳转中', '?s=admin/book');
    }

    /**
     * 从正式库中删除已入库书
     */
    public  function deletePutBook(){
        $book_id = $_GET['book_id'];
        $listTemp =  $this->tempbook_obj->where("book_id = {$book_id}")->select();
        $post_id = $listTemp[0]['post_id'];
        $data['book_state'] = 1;
        $this->tempbook_obj->where("book_id = {$book_id}")->save($data);
        $this->book_obj->where("book_id = {$post_id}")->delete();
        $this->jumpPage('成功从正式库中删除，页面跳转中', '?s=admin/temp/tempmanage');
    }

    /**
     * 临时库电子书列表
     */
    public function tempManage(){
        import("ORG.Util.Page");
        $count1 = $this->tempbook_obj->where("book_state = 1")->count();
        $Page1 = new Page($count1,20); //  实例化分页类 传入总记录数和每页显示的记录数
        $show1 = $Page1->show();
        $listTemp1 = $this->tempbook_obj->join("temp_link_type ON temp_book.book_id = temp_link_type.book_id")
                                        ->join("temp_type ON temp_link_type.type_id = temp_type.type_id")
                                        ->where("book_state = 1")->order( 'temp_book.book_id' )->limit($Page1->firstRow.','.$Page1->listRows)->select();
        for($i = 0; $i < count($listTemp1); $i++){
            $state = '<font color="green">完全采集</font>';
            $book_id = $listTemp1[$i]['book_id'];
            $countChapterTemp = $this->templinkbook_obj->where("book_id = {$book_id}")->count();
            if($countChapterTemp == 0){
                $state = '<font color="red">章节未采集</font>';
            }else{
                $countContentTemp = $this->templinkchapter_obj->join("temp_link_book ON temp_link_book.chapter_id = temp_link_chapter.chapter_id")
                                         ->where("book_id = {$book_id}")->count();
                if($countContentTemp == 0){
                    $state = '<font color="red">内容未采集</font>';
                }                         
            }
            $listTemp1[$i]['state'] = $state;
        }
        $this->assign('list1', $listTemp1);
        $this->assign('page1', $show1);
        $listTemp3 = $this->temptype_obj->select();
        $this->assign('sel1', $listTemp3);
        $listTemp4 = $this->book_sort->select();
        $this->assign('sel2', $listTemp4);
        $this->display('./Public/admin/collector/tempmanage.html');
    }

    /**
     * 已入正式库电子书列表
     */
    public function tempManage2(){
        import("ORG.Util.Page");
        $count2 = $this->tempbook_obj->where("book_state = 2")->count();
        $Page2 = new Page($count2,20);
        $show2 = $Page2->show();
        $listTemp2 = $this->tempbook_obj->join("temp_link_type ON temp_book.book_id = temp_link_type.book_id")->join("temp_type ON temp_link_type.type_id = temp_type.type_id")->where("book_state = 2")->order( 'temp_book.book_id' )->limit($Page2->firstRow.','.$Page2->listRows)->select();
        $this->assign('list2', $listTemp2);
        $this->assign('page2', $show2);
        $listTemp3 = $this->temptype_obj->select();
        $this->assign('sel1', $listTemp3);
        $listTemp4 = $this->book_sort->select();
        $this->assign('sel2', $listTemp4);
        $this->display('./Public/admin/collector/tempmanage2.html');
    }

    /**
     * 清空所有相关的临时库表
     */
    public function deleteTemp(){
        $this->tempbook_obj->query('TRUNCATE TABLE temp_book');
        $this->tempchapter_obj->query('TRUNCATE TABLE temp_chapter');
        $this->tempcontent_obj->query('TRUNCATE TABLE temp_content');
        $this->templinkbook_obj->query('TRUNCATE TABLE temp_link_book');
        $this->templinkchapter_obj->query('TRUNCATE TABLE temp_link_chapter');
        $this->temptype_obj->query('TRUNCATE TABLE temp_type');
        $this->templink_type_obj->query('TRUNCATE TABLE temp_link_type');
        $this->tempbook_list_obj->query('TRUNCATE TABLE temp_book_list');
        $this->templinkresource_obj->query('TRUNCATE TABLE temp_link_resource');
        $this->tempurl_obj->query('TRUNCATE TABLE temp_url');
        $this->jumpPage('清空临时表成功，页面跳转中', '?s=admin/temp/tempmanage');
    }

    /**
     * 清空本地化的图片目录
     */
    public function deleteImg(){
        $listFile = scandir('files/images');
        for($i = 0; $i < count($listFile); $i++){
            $pos1 = strrpos($listFile[$i],'l_cms_');
            $pos2 = strrpos($listFile[$i],'.jpg');
            if($pos1 !== false && $pos2 !== false){
                unlink('files/images/'.$listFile[$i]);
            }
        }
        $this->jumpPage('本地图片清空成功，页面跳转中', '?s=admin/temp/tempmanage');
    }

    /**
     * 设置临时库与正式库中的类型对应
     */
    public function setType(){
        $sort_id = $_POST['sort_id'];
        $type_id = $_POST['type_id'];
        for($i = 0; $i < count($type_id); $i++){
            $dataTypeTemp = array(
                'type_id' => $type_id[$i],
                'sort_id' => $sort_id[$i]
            );
            $this->temptype_obj->save($dataTypeTemp);
        }
        $this->jumpPage('类型关系设置成功，页面跳转中', '?s=admin/temp/tempmanage');
    }

    /**
     * 获得临时库中指定ID电子书的章节列表
     */
    public function getChapterList(){
        $book_id = $_GET['book_id'];
        $listTemp = $this->tempbook_obj->where("book_id = {$book_id}")->select();
        $listTemp2 = $this->tempchapter_obj->join("temp_link_book ON temp_chapter.chapter_id = temp_link_book.chapter_id")->where("temp_link_book.book_id = {$book_id}")->select();
        $this->assign('book_name',$listTemp[0]['book_name']);
        $this->assign('list', $listTemp2);
        $this->display('./Public/admin/collector/getchapterlist.html');
    }
    
    /**
     * 获得临时库中指库章节的内容
     */
    public function getChapterContent(){
        $book_id = $_GET['book_id'];
        $chapter_id = $_GET['chapter_id'];
        $countTemp = $this->templinkbook_obj->where("book_id = {$book_id} AND chapter_id = {$chapter_id}")->count();
        if($countTemp != 0){
            $listTemp = $this->tempchapter_obj->join("temp_link_chapter ON temp_chapter.chapter_id = temp_link_chapter.chapter_id")->join("temp_content ON temp_link_chapter.content_id = temp_content.content_id")->where("temp_chapter.chapter_id = {$chapter_id}")->select();
            $this->assign('chapter_name',$listTemp[0]['chapter_name']);
            $this->assign('chapter_sum',$listTemp[0]['chapter_sum']);
            $this->assign('chapter_update',$listTemp[0]['chapter_update']);
            $this->assign('book_id',$book_id);
            $this->assign('previous',$chapter_id - 1);
            $this->assign('next',$chapter_id + 1);
            $this->assign('content_text',$listTemp[0]['content_text']);
            $this->display('./Public/admin/collector/getchaptercontent.html');
        }else{
            $this->jumpPage('章节不存在', '?s=admin/temp/getchapterlist/book_id/'.$book_id,0);
        }
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
