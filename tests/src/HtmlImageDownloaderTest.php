<?php

use Hwl\HtmlImageDownloader\HtmlImageDownloader;

/**
 * html源码解析图片下载类测试
 * 测试时的图片地址可能失效,需要修改成有效的图片链接
 */
class HtmlImageDownloaderTest extends \PHPUnit_Framework_TestCase{

    private $testHtml;

    private $downloader;

    public function __construct()
    {
        $this->testHtml = <<<HTML
<p><img src="https://img.weiyemingtong.com/imgextra/i3/2258348879/TB2Msz3bXXXXXbJXXXXXXXXXXXX_!!2258348879.jpg" align="absmiddle"><img align="absmiddle" src="https://img.weiyemingtong.com/imgextra/i4/2258348879/TB22LYWjpXXXXXTXpXXXXXXXXXX_!!2258348879.jpg"><img align="absmiddle" src="https://img.weiyemingtong.com/imgextra/i2/2258348879/TB2z7EcjpXXXXajXXXXXXXXXXXX_!!2258348879.jpg"></p>
HTML;

        $this->downloader = new HtmlImageDownloader();
    }

    public function testProcessing(){
        $testArray = array(
            'https://img.weiyemingtong.com/imgextra/i3/2258348879/TB2Msz3bXXXXXbJXXXXXXXXXXXX_!!2258348879.jpg',
            'https://img.weiyemingtong.com/imgextra/i4/2258348879/TB22LYWjpXXXXXTXpXXXXXXXXXX_!!2258348879.jpg',
        );
        //替换成数组进行测试,也可以使用$this->testHtml来测试
        $this->downloader->processing($testArray);
    }

    /**
     * 测试处理,数据来自于地址数组,即 $url = array('url'=>'','fileName' => '',$extName => '');
     */
    public function testProcessingFromUrlArray(){
        $testArray = array(
            array(
                'url'      => 'https://img.weiyemingtong.com/imgextra/i3/2258348879/TB2Msz3bXXXXXbJXXXXXXXXXXXX_!!2258348879.jpg',
                'fileName' => '睡懒觉',
                'extName'  => ''
            ),
            array(
                'url'      => 'https://img.weiyemingtong.com/imgextra/i4/2258348879/TB2Ep88mXXXXXb_XXXXXXXXXXXX_!!2258348879.jpg',
                'fileName' => '哆啦A梦',
                'extName'  => ''
            ),
            array(
                'url'      => 'https://img.weiyemingtong.com/imgextra/i2/2258348879/TB2kHawipXXXXaFXXXXXXXXXXXX_!!2258348879.gif',
                'fileName' => '',
                'extName'  => ''
            )
        );
        //替换成数组进行测试,也可以使用$this->testHtml来测试
        $this->downloader->processing($testArray);
    }

    public function testParseHtmlStringToImageUrl(){
        $setImageUrls = array(
            'https://img.weiyemingtong.com/imgextra/i3/2258348879/TB2Msz3bXXXXXbJXXXXXXXXXXXX_!!2258348879.jpg',
            'https://img.weiyemingtong.com/imgextra/i4/2258348879/TB22LYWjpXXXXXTXpXXXXXXXXXX_!!2258348879.jpg',
            'https://img.weiyemingtong.com/imgextra/i2/2258348879/TB2z7EcjpXXXXajXXXXXXXXXXXX_!!2258348879.jpg'
        );

        $getImageUrls = $this->downloader->parseHtmlStringToImageUrl($this->testHtml);

        $this->assertEquals($setImageUrls,$getImageUrls);
    }

    /**
     * 测试压缩文件
     * @depends testProcessing
     */
    public function testCompressImagesFile(){
        $ROOT = dirname( dirname( __DIR__ ) ) . '/';
        //该目录必须需要,不存在需要手动创建
        $savePath   = $ROOT . 'tests/compressZips/test.zip';
        $targetPath = $ROOT . 'images/';
        $this->downloader->compressImagesFile($savePath,$targetPath,'根文件夹名称');

        $this->assertFileExists($savePath);
    }

    /**
     * 测试删除设置的保存路径文件夹
     * 测试该方法需要确保路径,以免删除掉其他的文件
     */
    public function testClearDownloadFile(){
        //$this->downloader->clearDownloadFile();
        $this->assertEquals(0,is_dir($this->downloader->getSaveFolderPath()));
    }

    /**
     * 测试获取图片扩展名
     */
    public function testGetImageFileExt(){
        $testJPG = 'http://img.weiyemingtong.com/test.jpg';
        $testGIF = 'http://img.weiyemingtong.com/test.gif';
        $testPNG = 'http://img.weiyemingtong.com/test.png';
        $testDef = 'http://img.weiyemingtong.com/test';

//        echo $this->downloader->getImageFileExt($testGIF);
        $this->assertEquals($this->downloader->getImageFileExt($testJPG),'jpg');
        $this->assertEquals($this->downloader->getImageFileExt($testGIF),'gif');
        $this->assertEquals($this->downloader->getImageFileExt($testPNG),'png');
        $this->assertEquals($this->downloader->getImageFileExt($testDef),'jpg');
    }
}