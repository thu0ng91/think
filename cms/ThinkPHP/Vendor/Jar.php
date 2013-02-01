<?php

/**
 * Description of Jar
 *
 * @author wingofsky
 */
class Jar {
    var $bookInfo = array('id' => 0,'title' => "BOOK CMS书籍",'author' => "BOOK CMS作者",'sort' => "default",'publisher' => "BOOK CMS",'seller' => "DIY_GENERATED",'corver' =>"",'copyright' => "BOOK CMS");
    var $chapters = array();
    var $chapterCount = 0;
    var $articleLen = 0;
    var $chaptitleLen = 0;

    function jar()
    {
        
    }

    //添加章节
    function addChapter($title,$content)
    {

        //$title = iconv("utf-8","UTF-8//IGNORE",$title); 标题为UTF-8编码，如有不同需要可专门放置编码变量生成。

        $content = iconv("utf-8","UCS-2LE//IGNORE",$content); //章节显示JAR手机需UCS-2LE编码

        $this->chapters[$this->chapterCount] = array("title" => $title, "content" =>$content);
        ++$this->chapterCount;
        $this->chaptitleLen += strlen($title);
        $this->articleLen += strlen($content); 
    }

    //生成JAR文件
    function makeJar($jarFileName = "", $jadName = "")
    {

        $zip = new ZipArchive();
        if ($zip->open($jarFileName,ZipArchive::CREATE)!==TRUE)
        {
            exit("创建失败\n");
        }
        $zip->addFile('jar/a.class','a.class');
        $zip->addFile('jar/b.class','b.class');
        $zip->addFile('jar/c.class','c.class');
        $zip->addFile('jar/d.class','d.class');
        $zip->addFile('jar/e.class','e.class');
        $zip->addFile('jar/f.class','f.class');
        $zip->addFile('jar/g.class','g.class');
        $zip->addFile('jar/h.class','h.class');
        $zip->addFile('jar/i.class','i.class');
        $zip->addFile('jar/j.class','j.class');
        $zip->addFile('jar/k.class','k.class');
        $zip->addFile('jar/l.class','l.class');
        $zip->addFile('jar/m.class','m.class');
        $zip->addFile('jar/n.class','n.class');
        $zip->addFile('jar/o.class','o.class');
        $zip->addFile('jar/icon.png','icon.png');
        $zip->addFile('jar/MBook.class','MBook.class');

        $manifestvar = "Manifest-Version: 1.0\r\nMicroEdition-Configuration: CLDC-1.0\r\nMIDlet-Name: ".$this->bookInfo['title']."\r
\nCreated-By: 1.4.2_09 (Sun Microsystems Inc.)\r\nMIDlet-Vendor: http://www.baidu.com\r\nMIDlet-1: ".$this->bookInfo['title'].", /0.png, MBook\r\nMIDlet-Version: 1.0\r\nMicroEdition-Profile: MIDP-1.0\r\n";

        //$zip->addFromString('META-INF/MANIFEST.MF', $manifestvar);
        $zip->addFromString("META-INF/MANIFEST.MF",$manifestvar);

        //重点，0文件的内容由以下代码产生
        $jarhead = "";
        $jarhead .= chr(0).chr(1).chr(48);
        $jarhead .= $this->dechexs(strlen($this->bookInfo['title']),2).$this->bookInfo['title'];

        //章节数目
        $chapNum = strval (count ($this->chapters));
        $jarhead .= $this->dechexs(strlen($chapNum), 2).$chapNum;
        
        //章节文件输出
        $chapFilNum = 1;    //文件名
        foreach ($this->chapters as $key => $var)
        {

            


            $zip->addFromString(strval($chapFilNum), $var['content']);
            $temp = $chapFilNum.",".strlen($var['content']).",".$var['title'];
            $jarhead .= $this->dechexs(strlen($temp), 2).$temp;
            ++$chapFilNum;
        }

        //JAR电子书封面信息生成
        $jarCover = "";
        $jarCover .= "书名：";
        $jarCover .= $this->bookinfo['title']."\r\n";
        $jarCover .= "作者：";
        $jarCover .= $this->bookinfo['author']."\r\n";
        $jarCover .= "制作：";
        $jarCover .= $this->bookinfo['publisher']."\r\n";
        $jarCover = substr($jarCover,0,-2);

        $jarhead .= $this->dechexs(strlen($jarCover), 4).$jarCover;

        //写入0文件
        $zip->addFromString('0', $jarhead);

        $zip->close();


        //JAD文件生成

        $jarFileSize = filesize($jarFileName);
        /*$manifestvar = "Manifest-Version: 1.0\r\nMicroEdition-Configuration: CLDC-1.0\r\nMIDlet-Name: ".$this->bookInfo['title']."\r
\nCreated-By: 1.4.2_09 (Sun Microsystems Inc.)\r\nMIDlet-Vendor: http://www.baidu.com\r\nMIDlet-1: ".$this->bookInfo['title'].", /0.png, MBook\r\nMIDlet-Version: 1.0\r\nMicroEdition-Profile: MIDP-1.0\r\n";*/
        $jadFile = "Manifest-Version: 1.0\r\nMicroEdition-Configuration: CLDC-1.0\r\nMIDlet-Name: ".$this->bookInfo['title']."\r
\nCreated-By: 1.4.2_09 (Sun Microsystems Inc.)\r\nMIDlet-Vendor: http://www.baidu.com\r\nMIDlet-1: ".$this->bookInfo['title'].", /0.png, MBook\r\nMIDlet-Version: 1.0\r\nMicroEdition-Profile: MIDP-1.0\r\nMIDlet-Jar-Size: ".
$jarFileSize."\r\nMIDlet-Jar-URL: ".basename($jarFileName)."\r\n";
        if (empty ($jadName))
        {
            $chapFilNum = strrpos($jarFileName, ".");
            if (0 < $chapFilNum)
            {
                $jadName = substr($jarFileName,0,$chapFilNum);
                
            }
            $jadName .= ".jad";
        }
        $file = fopen($jadName, "w");
        fwrite($file, $jadFile);
        fclose($file);


    }

    function makeJad($jarFileName = "", $jarName = "")
    {

        $temp = filesize($jarFileName);
        $jadfile = "Manifest-Version: 1.0\r\nAnt-Version: Apache Ant 1.7.0\r\nMIDlet-1: ".$this->bookinfo['title'].", /icon.png, JavaBook\r\nMIDlet-Jar-Size: ".
$temp."\r\nMIDlet-Jar-URL: ".basename( $jarFileName )."\r\nMIDlet-Name: ".$this->bookinfo['title']."\r\nMIDlet-Vendor: BOOK CMS \r
\nMIDlet-Version: 1.0\r\nMicroEdition-Configuration: CLDC-1.0\r\nMicroEdition-Profile: MIDP-1.0\r\n";
        $file = fopen($jarFileName, "w");
        fwrite($file,$jadfile);
        fclose($file);


    }



    //乱数函数
    function dechexs( $mcse, $mcze )
    {
        $mcz13 = "";
        $mcze *= 2;
        $mcs = substr( sprintf( "%0".$mcze."s", dechex( $mcse ) ), 0 - $mcze );
        $mcfvar = 0;
        for ( ; $mcfvar < $mcze; $mcfvar += 2 )
        {
            $mcz13 .= chr( hexdec( substr( $mcs, $mcfvar, 2 ) ) );
        }
        return $mcz13;
    }

    function fileSize($fileName)
    {
        
    }



}
?>
