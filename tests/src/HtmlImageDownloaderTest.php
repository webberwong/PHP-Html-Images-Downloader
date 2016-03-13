<?php

use Hwl\HtmlImageDownloader\HtmlImageDownloader;

/**
 * html源码解析图片下载类测试
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
}