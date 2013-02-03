<?php
/**
 * 百度编辑器Yii扩展
 * @Author: Rogee<rogeecn@gmail.com>
 * Date: 12-12-17 pm 1:36
 */

class editor extends CInputWidget
{

    //编辑器配置选项
    public $options = array(
        'wordCount' => false,
    );

    /**
     * 设置加载JS的服务器, 默认本地加载,可以填写'baidu'其它字符来使用服务器加载
     * @var string
     */
    public $file_server = 'local';
    public $UEDITOR_HOME_URL = '/';
    private $_base_url;
    private $_js;
    private $_editor_id;
    public function init()
    {
        //处理textarea的显示
        if( $this->hasModel() )
        {
            echo CHtml::activeTextArea($this->model, $this->attribute,$this->htmlOptions);
            list($_model_name, $_model_id) = $this->resolveNameID();
            $this->_editor_id = $_model_id;
        }
        else
        {
            if( !isset($this->name) )
                throw new Exception('name 为必须设置属性');
            else{
                //如果未设置的话自动加入ID
                if(!isset($this->htmlOptions['id']))
                    $this->htmlOptions['id'] = $this->id;
                $this->_editor_id = $this->htmlOptions['id'];
                echo CHtml::textArea($this->name, $this->value, $this->htmlOptions);
            }
        }

        //注册JS
        $assetPrefix = Yii::app()->assetManager->publish(dirname(__FILE__).'/editor', true, -1, defined('YII_DEBUG'));
        $cs = Yii::app()->clientScript;
        $cs->registerScriptFile($assetPrefix.'/editor_config.js');
        if( isset($this->options['file_server']) && $this->options['file_server']!=='baidu' )
        {
            $cs->registerScriptFile($assetPrefix.'/editor_all_min.js');
        }
        else
        {
            //$cs->registerScriptFile('http://ueditor.baidu.com/ueditor/editor_config.js');
            $cs->registerScriptFile('http://ueditor.baidu.com/ueditor/editor_all_min.js');
        }

        $this->_base_url = $assetPrefix;
        $this->genarateJs();


    }

    public function run()
    {
        if( false !== $this->_js )
        {
            Yii::app()->clientScript->registerScript('ueditor'.$this->getId(), $this->_js, CClientScript::POS_END );
        }
    }
    /**
     * 生成必须的JS代码
     */
    private function genarateJs()
    {
        if(!isset($this->options) || empty($this->options) )
            return false;

        $this->options = array_merge($this->_getDefaultSetting(), $this->options);
        $this->options['UEDITOR_HOME_URL']=$this->_base_url.$this->UEDITOR_HOME_URL ;
        $options = $this->encode($this->options);
        //$toolbars = $this->genarateToolBar($options['toolbars']);
        $this->_js = <<<EOT
        var editorOption = {$options};
        var editor_a = new baidu.editor.ui.Editor(editorOption);
        editor_a.render( '{$this->_editor_id}' );
EOT;
    }

    /**
     * 生成Toolbar
     * @param $toolbar array()
     */
    private function genarateToolBar($toolbarOptions)
    {
        $toolbar = '';
        foreach($toolbarOptions as $value)
        {
            if( is_array($value))
            {
                $value = $this->genarateToolBar($value);
                $toolbar .=",{$value}";
            }
            else
            {
                $toolbar .=",'{$value}'";
            }
        }

        $toolbar = "[".trim($toolbar, ',')."]";
        return $toolbar;
    }

    /**
     * 获取编辑器的默认配置项目
     * @return array
     */
    private function _getDefaultSetting()
    {
        $_baseUrl = Yii::app()->request->baseUrl;
        return array(
            'toolbars' => array(
                array('fullscreen', 'source', '|', 'undo', 'redo', '|','bold', 'italic', 'underline', 'strikethrough', 'superscript',
                    'subscript', 'removeformat','formatmatch','autotypeset','blockquote', 'pasteplain', '|', 'forecolor', 'backcolor',
                    'insertorderedlist', 'insertunorderedlist','selectall', 'cleardoc', '|',
                    'rowspacingtop', 'rowspacingbottom','lineheight','|',
                    'customstyle', 'paragraph', 'fontfamily', 'fontsize', '|',
                    'directionalityltr', 'directionalityrtl', 'indent', '|',
                    'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|',
                    'touppercase','tolowercase','|',
                    'link', 'unlink', 'anchor', '|',
                    'imagenone', 'imageleft', 'imageright','imagecenter', '|',
                    'insertimage', 'emotion','scrawl', 'insertvideo','music','attachment', 'map', 'gmap', 'insertframe',
                    'highlightcode','webapp','pagebreak','template','background', '|',
                    'horizontal', 'date', 'time', 'spechars','snapscreen', 'wordimage', '|',
                    'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol',
                    'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|',
                    'print', 'preview', 'searchreplace','help')
            ),
            'initialFrameWidth'=>'100%', //默认宽度
            'wordCount'=> true, //统计字数
            'focus'=>false, //自动焦点
            'autoFloatEnabled' => true, //工具栏浮动
            'autoClearinitialContent' =>false, //自动焦点清空内容
            'autoHeightEnabled' => true, //自动长高
            'elementPathEnabled' => true, //底部路径提示
            'imagePopup'=> true, //图片浮层
            'sourceEditor' => false, // 源码高亮
            /* 图片上传处理*/
            'imageUrl' => $_baseUrl.'/site/uploadimg',
            'imagePath' => $_baseUrl.'/',
            /*附件图片列表*/
            'imageManagerUrl' => $_baseUrl.'/site/backimg',
            'imageManagerPath'=> $_baseUrl.'/',
            /*涂鸦*/
            'scrawlUrl'=>$_baseUrl.'/site/scrawup',
            'scrawlPath' => $_baseUrl.'/',
            /* 附件上传 */
            'fileUrl' => $_baseUrl."/site/fileup",
            'filePath' => $_baseUrl."/",
            /*视频搜索*/
            'getMovieUrl'=>$_baseUrl."/site/getmovie",
            /* Word 转存*/
            'wordImageUrl'=>$_baseUrl.'/site/uploadimg',
            'wordImagePath' => $_baseUrl.'/',
            /*远程抓取*/
            'catcherUrl' => $_baseUrl."/site/RemoteImage",   //处理远程图片抓取的地址
            'catcherPath' => $_baseUrl."/",
        );
    }

    /**
     * 把PHP代码转换成JS
     * @param $value
     * @return string
     */
    private function encode($value)
    {
        $es=array();
        if(($n=count($value))>0 && array_keys($value)!==range(0,$n-1))
        {
            foreach($value as $k=>$v)
                $es[]= CJavaScript::quote($k).":".CJavaScript::encode($v);
            return '{'.implode(',',$es).'}';
        }
        else
        {
            foreach($value as $v)
                $es[]=self::encode($v);
            return '['.implode(',',$es).']';
        }
    }
}

