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
     * 允许的图片扩展名
     * 使用的Intervention/Image库里环境一般为gd库,仅支持jpg,gif,png格式
     * @var array
     */
    private $imgExts = array(
        'jpg','jpeg','gif','png'
    );


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
     * @param string|array $html
     * @param int $width 图片宽度限制
     * @return array
     */
    public function processing($html,$width = 750){
        $this->createSaveFolder();
        //如果是数组,则直接使用数组,不是则用html字符串来解析
        if(is_array($html)){
            $imgUrls = $html;
        }else{
            $imgUrls = self::parseHtmlStringToImageUrl($html);
        }
        //保证一个尺寸
        $width = is_numeric($width) ? intval($width) : 750;

        $imagesStatus = array();

        foreach($imgUrls as $key => $url){
            //判断数组里是否还有数组[url,fileName,extName]
            if(is_array($url)){
                //如果指定了文件名,则不使用序号做文件名
                $fileName = isset($url['fileName']) && $url['fileName'] ? $url['fileName'] : $key;
                $savePath = $this->saveFolder . $fileName . '.'
                    . $this->getImageFileExt($url['url'],$url['extName']);

                $imagesStatus[] = $this->resizeConstraintWidth($url['url'],$width,$savePath);
            }else{
                //使用序号做文件名
                $savePath = $this->saveFolder . $key . '.' . $this->getImageFileExt($url);
                $imagesStatus[] = $this->resizeConstraintWidth($url,$width,$savePath);
            }
            //重置图片大小,宽高各自减少像素
            //$imagesStatus = $this->resizeReducePixel($url,1,$savePath);

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
        //SSL证书不校验
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER,false);
        $curl->get($url);
        if ($curl->error) {
            return false;
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
     * 下载并重置图片,约束宽高比
     * @param string $url
     * @param int $width
     * @param string $savePath
     * @return array
     */
    public function resizeConstraintWidth($url,$width = 1,$savePath){
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
                //如果原始图片小于后面设置的调试,则不调整图片
                if($width < $iw){
                    $imgObj->widen($width);
                }
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
     * @param string $rootFolderName 压缩包的根目录文件夹名称
     * @return string
     */
    public function compressImagesFile($savePath = '',$compressFolderPath = '',$rootFolderName = ''){
        $compressor = Zippy::load();
        $folderName = $rootFolderName;
        if(!is_string($folderName) OR $folderName == ''){
            $filename   = pathinfo($savePath);
            if(isset($filename['filename']) && $filename['filename'] != ''){
                $folderName = $filename['filename'];
            }
        }

        $zip = $compressor->create($savePath,array($folderName => $compressFolderPath));
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

    /**
     * 获取图片文件里的扩展名
     * @param string $filePath 文件路径或文件名(需要加上扩展名)
     * @param string $extName 文件扩展名(如果指定的扩展名不在允许的扩展名列表里,则使用默认jpg)
     * @return string
     */
    public function getImageFileExt($filePath,$extName = 'jpg'){
        $fileExt = pathinfo($filePath,PATHINFO_EXTENSION);
        if($fileExt){
            $extName = $fileExt;
        }
        $extName = strtolower($extName);
        //如果不在支持的扩展名里,则使用jpg
        if(!in_array($extName,$this->imgExts)){
            $extName = 'jpg';
        }
        return $extName;
    }

    /**
     * 设置图片扩展名集
     * @param array $exts
     */
    public function setImageExts($exts){
        $this->imgExts = $exts;
    }
}