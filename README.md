HtmlImageDownloader
=========================================
简单html源码图片下载器,简单用于一些没有限制图片访问的情况下使用

## 功能
* 下载
* 处理图片大小
* 将图片保存文件夹压缩成压缩包
* 清理图片保存文件夹

## 使用示例
主要说明的processing()的参数,有点多余<br>
还是主要查要看代码 **HtmlImageDownloader::processing**

```php
/**
 * 处理并下载并重置大小图片
 * 暂只提供一种图片宽高同等缩小
 * @param string|array $html 
 * @param int $width 图片宽度限制
 * @return array
 */
public function processing($html,$width = 750)

//@param $html 值为html代码字符串时,则会启用解析成数组,下载出来的图片会以序号做为文件名
//@param $html 值为数组时,这里分两种情况
//一种情况是数组里直接是url字符串值,这种情况下载出来也是以序号做为文件名
//另一种情况是数组里是url信息的数组array('url' => '','fileName' => 'test','extName' => 'jpg'),这种情况下载的文件名会为test.jpg

//在于传入的
```

## 单元测试
```
#cd app director path,进入代码目录

#单测试某个方法
phpunit tests/src/HtmlImageDownloaderTest.php --bootstrap="tests/bootstrap.php" --filter="::testProcessingFromUrlArray"
```

### TODO 增加gif修改保持动画功能
目前的gif的动画图会变成表态的图片,解决的方法是添加im扩展,或者使用其他的原生php解析代码