<?php
namespace Hwl\HtmlImageDownloader;

use PHPHtmlParser\Dom as HtmlDom;
use Symfony\Component\Filesystem\Filesystem;
use Intervention\Image\ImageManagerStatic as ImageHandler;
use Curl\Curl;
use Alchemy\Zippy\Zippy;

/**
 * html源码解析图片下载类
 * Class HtmlImageDownloader
 * @package Hwl\HtmlImageDownloader
 */
class HtmlImageDownloader{

    /**
     * 文件系统操作类
     * @var Filesystem
     */
    private $fs;

    /**
     * 保存文件夹路径
     * @var string
     */
    private $saveFolder = './images/';

    private $imgLists = array();


    /**
     * HtmlImageDownloader constructor.
     * @param string $saveFolderPath 图片保存目录
     */
    public function __construct($saveFolderPath = '')
    {
        $this->fs = new Filesystem();
        if($saveFolderPath && $saveFolderPath != ''){
            $this->saveFolder = $saveFolderPath;
        }
    }

    /**
     * 处理并下载并重置大小图片
     * 暂只提供一种图片宽高同等缩小
     * @param string $html
     * @return array
     */
    public function processing($html){
        $this->createSaveFolder();
        $imgUrls = self::parseHtmlStringToImageUrl($html);
        $imagesStatus = array();

        foreach($imgUrls as $key => $url){
            $savePath = $this->saveFolder . $key . '.jpg';
            $imagesStatus = $this->resizeReducePixel($url,1,$savePath);
        }

        $this->imgLists = $imagesStatus;

        return $this->imgLists;

    }

    /**
     * curl下载文件
     * @param string $url
     * @return resource|false
     */
    public function downloadImage($url){
        //return file_get_contents($url);
        $curl = new Curl();
        $curl->get($url);
        if ($curl->error) {
            echo false;
        }
        else {
            return $curl->response;
        }
    }

    /**
     * 重置图片大小,宽高各自减少相应的像素
     * @param string $url
     * @param int    $pixel
     * @param string $savePath
     * @return array
     */
    public function resizeReducePixel($url,$pixel = 1,$savePath){
        $status = array(
            'url'      => $url,
            'download' => 0,
            'savePath' => ''
        );

        if(strpos($url,'http') !== false){
            $imgData = $this->downloadImage($url);
            //如果成功下载文件
            if($imgData !== false){
                $status['download'] =  1 ;

                $imgObj = ImageHandler::make($imgData);
                $iw = $imgObj->getWidth();
                $ih = $imgObj->getHeight();
                $imgObj->resize($iw - $pixel,$ih - $pixel);
                $imgObj->save($savePath);

                $status['savePath'] = $savePath;
            }

        }


        return $status;
    }

    /**
     * 解析html成图片链接地址数组
     * @param string $html
     * @return array
     */
    public static function parseHtmlStringToImageUrl($html){
        $htmlDom = new HtmlDom();
        $htmlObj = $htmlDom->load($html);
        $lists   = $htmlObj->find('img');
        $imgUrls = array();
        foreach($lists as $key => $img){
            $imgUrls[] = $img->getAttribute('src');
        }
        return $imgUrls;
    }

    /**
     * 创建保存文件夹
     * @return bool|void
     */
    public function createSaveFolder(){
        if(!is_dir($this->saveFolder)){
            $this->fs->mkdir($this->saveFolder);
            return realpath($this->saveFolder);
        }
        return true;
    }

    /**
     * 压缩图片文件
     * @param string $savePath 压缩成功后保存的路径,需要包含生成的压缩包名称,文件夹不存在需手动创建
     * @param string $compressFolderPath 压缩目标文件夹路径
     * @return string
     */
    public function compressImagesFile($savePath = '',$compressFolderPath = ''){
        $compressor = Zippy::load();
        $zip = $compressor->create($savePath,array('NewImages' => $compressFolderPath));
        return $savePath;
    }

    /**
     * 删除生成的缩略图文件
     */
    public function clearDownloadFile(){
        if(realpath($this->saveFolder)){
            return $this->fs->remove($this->saveFolder);
        }
        return false;
    }

    /**
     * 获取保存图片的目录地址
     * @return string
     */
    public function getSaveFolderPath(){
        if($this->fs->isAbsolutePath($this->saveFolder)){
            return $this->saveFolder;
        }
        return __DIR__ . '/' . $this->saveFolder;
    }
}