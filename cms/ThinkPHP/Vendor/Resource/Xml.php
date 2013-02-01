<?php
/* 
 * Xml.php
 */

/**
 * XML解析类
 * @author wm
 */
class Xml {
    protected $inXmlDoc;
    protected $inXmlDoc1;
    protected $inXmlDoc2;
    protected $inXmlDoc3;
    protected $inXmlDoc4;


    public function  __construct() {
        $this->inXmlDoc = new DOMDocument('1.0', 'utf-8');
        $this->inXmlDoc1 = new DOMDocument('1.0', 'utf-8');
        $this->inXmlDoc2 = new DOMDocument('1.0', 'utf-8');
        $this->inXmlDoc3 = new DOMDocument('1.0', 'utf-8');
        $this->inXmlDoc4 = new DOMDocument('1.0', 'utf-8');
    }

    public function getXmlList($strXml){
        $arrTemp = array();
        $i = 0;
        $this->inXmlDoc->load($strXml);
        $list = $this->inXmlDoc->getElementsByTagName('list');
        foreach($list as $list1)
        {
           foreach($list1->childNodes as $node){
               $arrTemp[$i][$node->nodeName] = $node->firstChild->nodeValue;
           }
           $i++;
        }
        return $arrTemp;
    }

    public function getResourceList($strXml){
        $arrTemp = array();
        $i = 0;
        $this->inXmlDoc1->load($strXml);
        $list = $this->inXmlDoc1->getElementsByTagName('list');
        foreach($list as $list1)
        {
           foreach($list1->childNodes as $node){
               $arrTemp[$i][$node->nodeName] = $node->firstChild->nodeValue;
           }
           $i++;
        }
        return $arrTemp;
    }

    public function getBookList($strXml){
        $arrTemp = array();
        $i = 0;
        $this->inXmlDoc2->load($strXml);
        $list = $this->inXmlDoc2->getElementsByTagName('list');
        foreach($list as $list1)
        {
           foreach($list1->childNodes as $node){
               $arrTemp[$i][$node->nodeName] = $node->firstChild->nodeValue;
           }
           $i++;
        }
        return $arrTemp;
    }

    public function getChapterList($strXml){
        $arrTemp = array();
        $i = 0;
        $this->inXmlDoc3->load($strXml);
        $list = $this->inXmlDoc3->getElementsByTagName('list');
        foreach($list as $list1)
        {
           foreach($list1->childNodes as $node){
               $arrTemp[$i][$node->nodeName] = $node->firstChild->nodeValue;
           }
           $i++;
        }
        return $arrTemp;
    }

    public function getChapterListTitle($strXml){
        $strTemp = '';
        $this->inXmlDoc3->load($strXml);
        $root = $this->inXmlDoc3->getElementsByTagName('root');
        $strTemp = $root->item(0)->getAttribute('name');
        return $strTemp;
    }

    public function getContentList($strXml){
        $arrTemp = array();
        $i = 0;
        $this->inXmlDoc3->load($strXml);
        $list = $this->inXmlDoc3->getElementsByTagName('list');
        foreach($list as $list1)
        {
           foreach($list1->childNodes as $node){
               $arrTemp[$i][$node->nodeName] = $node->firstChild->nodeValue;
           }
           $i++;
        }
        return $arrTemp;
    }

    public function getTypeList($strXml){
        $arrTemp = array();
        $i = 0;
        $this->inXmlDoc2->load($strXml);
        $list = $this->inXmlDoc2->getElementsByTagName('list');
        foreach($list as $list1)
        {
           foreach($list1->childNodes as $node){
               $arrTemp[$i][$node->nodeName] = $node->firstChild->nodeValue;
           }
           $i++;
        }
        return $arrTemp;
    }

}

?>
