<?php
require __DIR__ . '/../lib/vendor/autoload.php';

use WebPConvert\WebPConvert;
use Imagine\Image\Box;
use Imagine\Image\Point;

class ctrlThumbnails
{
    public $app;
    public $browser;

    public function __construct($app)
    {
        $this->app = &$app;
        $this->thumbnails($app);
    }


    function cache() {
        $uri = $this->app->route->uri;
        $info = pathinfo($uri);
        $ext = isset($info['extension']) ? $info['extension'] : "";

        $cachefile=md5($uri).'.'.$ext;
        $cachedir=$this->app->route->path_app.'/uploads/_cache/'.substr($cachefile, 0, 2);
        $destination = $cachedir.'/'.$cachefile;
        if (is_file($destination)) {
            $image=file_get_contents($destination);
            $mime = wbMime($destination);
            header('Content-Type: '.$mime);
            echo $image;
            exit;
        } 
    }

    public function thumbnails($app)
    {
        $_GET = $app->vars('_get');
        $_POST = $app->vars('_post');

        $params=$app->vars('_route.params');
        $this->browser = (object)$this->getInfoBrowser();

        if ($app->vars('_get')) {
            $app->vars('_get', []);
            $app->vars('_get.w', $app->vars('_route.w'));
            $app->vars('_get.h', $app->vars('_route.h'));
        }
        if ($app->vars('_route.params.src') > '') {
            $app->vars('_get.src', $params['src']);
        } else {
            $re = '/[\/thumbc|thumb\/].*\/src\/(.*)/m';
            preg_match($re, $app->vars('_route.uri'), $matches, PREG_OFFSET_CAPTURE, 0);
            if (!count($matches)) {
                $app->vars('_get.src', $app->vars('_route.uri'));
            } else {
                $app->vars('_get.src', $matches[1][0]);
            }
            $app->vars('_route.src') == 'module' ? $app->vars('_get.src', $app->vars('_route.host') .'/'. $app->vars('_get.src')) : null ;
        }

        if (strpos($app->vars('_get.src'), '?')) {
            $src = explode('?', $app->vars('_get.src'));
            $app->vars('_get.src', $src[0]);
            $src= $src[0];
        }

        $app->vars('_get.zc', $app->vars('_route.zc'));
        $this->thumbnail_view($app);
        exit;
    }
    
    private function getInfoBrowser()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        preg_match("/(MSIE|Opera|Firefox|Chrome|Safari|Chromium|Version)(?:\/| )([0-9.]+)/", $agent, $bInfo);
        $browserInfo = ['name'=>'unknown','version'=>'1.0'];
        if (count($bInfo) > 1) {
            $browserInfo['name'] = ($bInfo[1]=="Version") ? "Safari" : $bInfo[1];
            $browserInfo['version'] = $bInfo[2];
        }
        return $browserInfo;
    }
    

    public function thumbnail_view($app)
    {
        $app->vars('_route.src') == 'module' ? $remote = true : $remote = false;
        $query = $app->vars('_route.query');
        if ($app->vars('_route.http') OR substr($app->vars('_get.src'),0,7) == 'http://') {
            $remote=true;
            $p='http';
        } elseif ($app->vars('_route.https') OR substr($app->vars('_get.src'),0,8) == 'https://') {
            $remote=true;
            $p='https';
        }
        $app->vars('_sett.devmode') == 'on' ? $cache=false : $cache = true;
        if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
            parse_str($_SERVER['HTTP_CACHE_CONTROL'], $cc);
            if (isset($cc['no-cache']) or isset($query['nocache'])) {
                $cache=false;
            }
        }
        $expire = "expires: " . gmdate("D, d M Y H:i:s", time() + (60 * 60 * 24 * 30)) . " GMT";
        header("Cache-Control: public");
        header($expire);
        if ($cache) {
            $this->cache();
        }

        if ($app->vars('_route.params') and isset($app->vars('_route.params')[0])) {
            $tmp=base64_decode($app->vars('_route.params')[0]);
            if (strpos($tmp, 'ttp://') or strpos($tmp, 'ttps://')) {
                $remote = true;
                $url = $tmp;
            }
        } 
        
        if ($app->vars('_route.src') == 'module') {
            $url = $app->vars('_get.src');
        }

        $formats = ['gif','png','jpg','svg','jpeg','webp','webm','mp3','mp4','pdf','doc','docx','xls','xlsx','zip','rar'];
        $danger = ['php','js','c','sh','py'];
        $imgext = ['gif','png','jpg','svg','jpeg','webp'];
        if ($remote) {
            !isset($url) ? $url=$p.substr($app->vars('_route.uri'), strpos($app->vars('_route.uri'), '://')) : null;
            $ext = pathinfo($url, PATHINFO_EXTENSION);
            count($app->vars('_route.query')) ? $url.='?'.http_build_query($app->vars('_route.query')) : null;
            if (!in_array($ext, $formats) or in_array($ext, $danger)) {
                return;
            } // дабы не загрузили на сервак бяку
            $file=$_ENV['path_app'].'/uploads/_remote/'.md5($url).'.'.$ext;
            if (!is_file($file) or !$cache) {
                $image=file_get_contents($url);
                wbPutContents($file, $image);
            }
        } else {
            $file=urldecode($_ENV['path_app'].'/'.$_GET['src']);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
        }

        $ext = strtolower($ext);

        if (!is_file($file) or !in_array($ext, $imgext)) {
            if (!in_array($ext, $imgext) && is_file($app->vars('_env.path_engine').'/lib/fileicons/'.$ext.'.svg')) {
                $file = $app->vars('_env.path_engine').'/lib/fileicons/'.$ext.'.svg';
            } else {
                $file = $app->vars('_env.path_engine').'/uploads/__system/image.svg';
            }
        }
        
        if (is_file($file)) {
            list($width, $height, $type) = $size = getimagesize($file);
            if ($app->vars('_route.w') == '') {
                $app->vars('_route.w', $width);
            }
            if ($app->vars('_route.h') == '') {
                $app->vars('_route.h', $height);
            }

            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $options = [];
            if ($ext == 'svg') {
                $imgdata = file_get_contents($file);
                $imgdata = preg_replace('#\s(width|height)="[^"]+"#', '', $imgdata);
                $image = str_replace('<svg ', '<svg width="'.$app->vars('_route.w').'" height="'.$app->vars('_route.h').'" viewbox="0 0 '.$app->vars('_route.w').' '.$app->vars('_route.h').'" ', $imgdata);
                $destination = $file;
            } else {
                switch ($ext) {
                                    case 'jpg':
                                        $options = ['jpeg_quality'=>90];
                                        break;
                                    case 'png':
                                        $options = ['png_compression_level'=>9];
                                        break;
                                    case 'webp':
                                        $options = ['webp_quality' => 90];
                                        break;
                                }
                $options['resolution-x'] = 300;
                $options['resolution-y'] = 300;
                $cachefile=md5($app->route->uri).'.'.$ext;
                $cachedir=$app->vars('_env.path_app').'/uploads/_cache/'.substr($cachefile, 0, 2);
                $destination = $cachedir.'/'.$cachefile;

                is_dir($cachedir) ? null : mkdir($cachedir, 0777, true);

                if (!is_file($destination) or $cache == false) {

                    // https://imagine.readthedocs.io/en/latest/
                    if (class_exists('Imagick')) {
                        $imagine = new Imagine\Imagick\Imagine();
                    } else {
                        $imagine = new Imagine\Gd\Imagine();
                    }

                    $image = $imagine->open(realpath($file));
                    $size    = new Imagine\Image\Box($app->vars('_route.w'), $app->vars('_route.h'));
                    $palette = new Imagine\Image\Palette\RGB();
                    $color = $image->getColorAt(new Imagine\Image\Point(0, 0)).'';
                    $color = $palette->color($color, 0);
                    $canvas = $imagine->create($size, $color);
                    if ($app->vars('_get.zc') == 0) {
                        $mode    = Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
                    } else {
                        $mode    = Imagine\Image\ImageInterface::THUMBNAIL_FLAG_UPSCALE;
                    }

                    $ih = $image->getSize()->getHeight();
                    $iw = $image->getSize()->getWidth();

                    $r1 = $iw / $ih;
                    $r2 = $app->vars('_route.w') / $app->vars('_route.h');
                    $ratio = $r1 / $r2;


                    if ($app->vars('_get.zc') == 0) {
                        if ($ratio < 1) {
                            $resize    = new Imagine\Image\Box(intval($app->vars('_route.w')), $app->vars('_route.h') / $ratio);
                            $image->resize($resize);
                        } elseif ($ratio > 1) {
                            $resize    = new Imagine\Image\Box(intval($app->vars('_route.w') * $ratio), $app->vars('_route.h'));
                            $image->resize($resize);
                        } else {
                            $image->resize($size);
                        }
                    } else {
                        if ($ratio > 1) {
                            $resize    = new Imagine\Image\Box(intval($app->vars('_route.w')), $app->vars('_route.h') / $ratio);
                            $image->resize($resize);
                        } elseif ($ratio < 1) {
                            $resize    = new Imagine\Image\Box(intval($app->vars('_route.w') * $ratio), $app->vars('_route.h'));
                            $image->resize($resize);
                        } else {
                            $image->resize($size);
                        }
                    }
                    $image->thumbnail($size, $mode);

                    $canvasCenter = new Imagine\Image\Point\Center($canvas->getSize());
                    $imageCenter = new Imagine\Image\Point\Center($image->getSize());

                    if ($image->getSize()->getWidth() > $app->vars('_route.w')) {
                        $offsetX = ($image->getSize()->getWidth() - $app->vars('_route.w')) / 2;
                        $image->crop(new Point($offsetX, 0), $size);
                    }


                    if ($image->getSize()->getHeight() > $app->vars('_route.h')) {
                        $offsetY = ($image->getSize()->getHeight() - $app->vars('_route.h')) / 2;
                        $image->crop(new Point(0, $offsetY), $size);
                    }


                    $offsetX = $canvasCenter->getX() - $imageCenter->getX();
                    $offsetY = $canvasCenter->getY() - $imageCenter->getY();
                    $offsetX < 0 ? $offsetX = 0 : null;
                    $offsetY < 0 ? $offsetY = 0 : null;
                    $canvas->paste($image, new Imagine\Image\Point($offsetX, $offsetY));
                    $canvas->save($destination, $options);
                    /*
                                    if (in_array($ext, ['jpg','jpeg'])) {
                                        $options = [];
                                        WebPConvert::convert($destination, $destination.'.webp', $options);
                                    }
                    */
                }
                $image=file_get_contents($destination);
            }
            $mime = wbMime($destination);
            header('Content-Type: '.$mime);
            echo $image;
            exit;
        }
    }
}
