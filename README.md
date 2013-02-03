百度UEditor Yii插件
================

Install
---------------
1. 把整个目录放置于 ext.Ueditor
2. 把UFunction放置到Components目录下,

关于作者
---------------------
* Author： Rogee
* Email: rogeecn@gmail.com
* Blog: http://www.qoophp.com

调用方法
-----------------
### 对于存在model实例的调用:
```php
$user = User::model()->findByPk(1);
$this->widget('ext.Ueditor.editor', array(
    'model' => $user,
    'attribute' => 'username',
    'options' => array(//配置方法可以参考editor.php引导文件
        'toolbars'=>array( array('insertvideo', 'insertimage','attachment', 'background', 'scrawl', 'source')),
        'wordCount'=>false,
        'elementPathEnabled'=>false,
    ),
));
```
### 对于没有模型的实例调用:
```php
$this->widget('ext.Ueditor.editor', array(
    'name' => 'test_ueditor',//指定提交表单name选项[*必须填写项目]
    'value' => '这里是测试文本',//指定初始值
    'id' => 'test_ueditor_id',//指定ID
    'options' => array(
        'toolbars'=>array( array('insertvideo', 'insertimage','attachment', 'background', 'scrawl', 'source')),
        'wordCount'=>false,
        'elementPathEnabled'=>false,
    ),
));
```
### 一些上传方法的使用:
```php
/**
 * Ueditor 附件上传
 */
public function actionFileUp()
{
    UFunction::FileUpload('file');
}

/**
 * UEditor远程图片抓取
 */
public function actionRemoteImage()
{
    UFunction::getRemoteImage($uri,$config);
}
/**
 * UEditor 获取视频列表
 */
public function actionGetMovie()
{
    UFunction::GetMovie();
}
/**
 * UEditor 涂鸦板
 */
public function actionScrawUp()
{
    UFunction::scrawUp();
}
/**
 * Ueditor 获取图片列表
 */
public function actionBackImg()
{
    UFunction::backList();
}
/**
 * 图片上传方法
 */
public function actionUploadImg()
{
    UFunction::FileUpload();

}
```