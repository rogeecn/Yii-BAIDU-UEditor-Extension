<?php
/**
 * Ueditor插件功能列表调用
 * @Author: Rogee<rogeecn@gmail.com>
 * Date: 12-12-17 下午1:36
 * $Id$
 */
Yii::import('ext.Ueditor.Plugin.Uploader');
include_once("Uploader.php");
class UFunction
{
    private static $file_type = array(
        'image' => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" ),
        'file' =>array( ".rar" , ".doc" , ".docx" , ".zip" , ".pdf" , ".txt" , ".swf" , ".wmv" )
    );
    /**
     * 获取视频
     */
    public static function GetMovie()
    {
        $key =htmlspecialchars($_POST["searchKey"]);
        $type = htmlspecialchars($_POST["videoType"]);
        $html = file_get_contents('http://api.tudou.com/v3/gw?method=item.search&appKey=myKey&format=json&kw='.$key.'&pageNo=1&pageSize=20&channelId='.$type.'&inDays=7&media=v&sort=s');
        echo $html;
    }
    /**
     * 文件上传
     * @param $type 'image/file'
     * @param $max_size kb单位
     */
    public static function FileUpload($type = 'image', $max_size=51200, $savePath = 'upload/')
    {
        //上传配置
        $config = array(
            "savePath" => $savePath ,             //存储文件夹
            "maxSize" => $max_size ,                   //允许的文件最大尺寸，单位KB
            "allowFiles" =>  self::$file_type[$type] //允许的文件格式
        );
        //生成上传实例对象并完成上传
        $up = new Uploader( "upfile" , $config );

        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "originalName" => "",   //原始文件名
         *     "name" => "",           //新文件名
         *     "url" => "",            //返回的地址
         *     "size" => "",           //文件大小
         *     "type" => "" ,          //文件类型
         *     "state" => ""           //上传状态，上传成功时必须返回"SUCCESS"
         * )
         */
        $info = $up->getFileInfo();

        /**
         * 向浏览器返回数据json数据
         * {
         *   'url'      :'a.rar',        //保存后的文件路径
         *   'fileType' :'.rar',         //文件描述，对图片来说在前端会添加到title属性上
         *   'original' :'编辑器.jpg',   //原始文件名
         *   'state'    :'SUCCESS'       //上传状态，成功时返回SUCCESS,其他任何值将原样返回至图片上传框中
         * }
         */
        // echo '{"url":"' .$info[ "url" ] . '","fileType":"' . $info[ "type" ] . '",
        //"original":"' . $info[ "originalName" ] . '","state":"' . $info["state"] . '"}';
        $ret_arr = array(
            'url' => $info['url'],
            'fileType' => $info['type'],
            'original' => $info['originalName'],
            'state' => $info['state'],
        );
        echo CJSON::encode($ret_arr);
    }

    /**
     * 涂鸦板上传
     * @param string $tmpPath
     */
    public static function scrawUp($savePath="upload/")
    {
        //上传配置
        $config = array(
            "savePath" => $savePath ,             //存储文件夹
            "maxSize" => 1000 ,                   //允许的文件最大尺寸，单位KB
            "allowFiles" => self::$file_type['image']  //允许的文件格式
        );
        $tmpPath = "tmp/";
        //获取当前上传的类型
        $action = htmlspecialchars( $_GET[ "action" ] );
        if ( $action == "tmpImg" ) { // 背景上传
            //背景保存在临时目录中
            $config[ "savePath" ] = $tmpPath;
            $up = new Uploader( "upfile" , $config );
            $info = $up->getFileInfo();
            /**
             * 返回数据，调用父页面的ue_callback回调
             */
            echo "<script>parent.ue_callback('" . $info[ "url" ] . "','" . $info[ "state" ] . "')</script>";
        } else {
            //涂鸦上传，上传方式采用了base64编码模式，所以第三个参数设置为true
            $up = new Uploader( "content" , $config , true );
            //上传成功后删除临时目录
            if(file_exists($tmpPath)){
                self::delDir($tmpPath);
            }
            $info = $up->getFileInfo();
            $ret_arr = array(
                'url' => $info['url'],
                'state' => $info['state']
            );

            echo CJSON::encode($ret_arr);
        }
    }
    /**
     *  获取背景图片列表
     * @param string $path 图片目录
     */
    public static function backList($path = 'upload/')
    {
        //最好使用缩略图地址，否则当网速慢时可能会造成严重的延时
        $action = htmlspecialchars( $_POST[ "action" ] );
        if ( $action == "get" ) {
            $files = self::getfiles( $path );
            if ( !$files ) return;
            rsort($files,SORT_STRING);
            $str = "";
            foreach ( $files as $file ) {
                $str .= $file . "ue_separate_ue";
            }
            echo $str;
        }
    }
    /**
     * 遍历获取目录下的指定类型的文件
     * @param $path
     * @param array $files
     * @return array
     */
    public static function getfiles( $path , &$files = array() )
    {
        if ( !is_dir( $path ) ) return null;
        $handle = opendir( $path );
        while ( false !== ( $file = readdir( $handle ) ) ) {
            if ( $file != '.' && $file != '..' ) {
                $path2 = $path . '/' . $file;
                if ( is_dir( $path2 ) ) {
                    self::getfiles( $path2 , $files );
                } else {
                    if ( preg_match( "/\.(gif|jpeg|jpg|png|bmp)$/i" , $file ) ) {
                        $files[] = $path2;
                    }
                }
            }
        }
        return $files;
    }

    /**
     * 删除整个目录
     * @param $dir
     * @return bool
     */
    public static function delDir( $dir )
    {
        //先删除目录下的所有文件：
        $dh = opendir( $dir );
        while ( $file = readdir( $dh ) ) {
            if ( $file != "." && $file != ".." ) {
                $fullpath = $dir . "/" . $file;
                if ( !is_dir( $fullpath ) ) {
                    unlink( $fullpath );
                } else {
                    delDir( $fullpath );
                }
            }
        }
        closedir( $dh );
        //删除当前文件夹：
        return rmdir( $dir );
    }

    /**
     * 远程抓取
     * @param $uri
     * @param $config
     */
    public static  function  getRemoteImage($savePath='upload/')
    {
        //远程抓取图片配置
        $config = array(
            "savePath" => $savePath ,            //保存路径
            "allowFiles" =>  self::$file_type['image'], //文件允许格式
            "maxSize" => 3000                    //文件大小限制，单位KB
        );
        $uri = htmlspecialchars( $_POST[ 'upfile' ] );
        $uri = str_replace( "&amp;" , "&" , $uri );

        //忽略抓取时间限制
        set_time_limit( 0 );
        //ue_separate_ue  ue用于传递数据分割符号
        $imgUrls = explode( "ue_separate_ue" , $uri );
        $tmpNames = array();
        foreach ( $imgUrls as $imgUrl ) {
            //http开头验证
            if(strpos($imgUrl,"http")!==0){
                array_push( $tmpNames , "error" );
                continue;
            }
            //获取请求头
            $heads = get_headers( $imgUrl );
            //死链检测
            if ( !( stristr( $heads[ 0 ] , "200" ) && stristr( $heads[ 0 ] , "OK" ) ) ) {
                array_push( $tmpNames , "error" );
                continue;
            }

            //格式验证(扩展名验证和Content-Type验证)
            $fileType = strtolower( strrchr( $imgUrl , '.' ) );
            if ( !in_array( $fileType , $config[ 'allowFiles' ] ) || stristr( $heads[ 'Content-Type' ] , "image" ) ) {
                array_push( $tmpNames , "error" );
                continue;
            }

            //打开输出缓冲区并获取远程图片
            ob_start();
            $context = stream_context_create(
                array (
                    'http' => array (
                        'follow_location' => false // don't follow redirects
                    )
                )
            );
            //请确保php.ini中的fopen wrappers已经激活
            readfile( $imgUrl,false,$context);
            $img = ob_get_contents();
            ob_end_clean();

            //大小验证
            $uriSize = strlen( $img ); //得到图片大小
            $allowSize = 1024 * $config[ 'maxSize' ];
            if ( $uriSize > $allowSize ) {
                array_push( $tmpNames , "error" );
                continue;
            }

            $savePath = $config[ 'savePath' ];
            if ( strrchr( $savePath , "/" ) != "/" ) {
                $savePath .= "/";
            }
            $savePath .= date( "Ymd" ).'/';
            if ( !file_exists( $savePath ) ) {
                if ( !mkdir( $savePath , 0777 , true ) ) {
                    return false;
                }
            }
            //创建保存位置
/*            $savePath = $config[ 'savePath' ];
            if ( !file_exists( $savePath ) ) {
                mkdir( "$savePath" , 0777 );
            }*/
            //写入文件
            $tmpName = $savePath . rand( 1 , 10000 ) . time() . strrchr( $imgUrl , '.' );
            try {
                $fp2 = @fopen( $tmpName , "a" );
                fwrite( $fp2 , $img );
                fclose( $fp2 );
                array_push( $tmpNames ,  $tmpName );
            } catch ( Exception $e ) {
                array_push( $tmpNames , "error" );
            }
        }
        /**
         * 返回数据格式
         * {
         *   'url'   : '新地址一ue_separate_ue新地址二ue_separate_ue新地址三',
         *   'srcUrl': '原始地址一ue_separate_ue原始地址二ue_separate_ue原始地址三'，
         *   'tip'   : '状态提示'
         * }
         */
        echo "{'url':'" . implode( "ue_separate_ue" , $tmpNames ) . "','tip':'远程图片抓取成功！','srcUrl':'" . $uri . "'}";
    }

    /**
     * 创建目录
     * @param $path
     */
    private static function createPath($path)
    {

    }
}
