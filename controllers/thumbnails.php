<?php
require __DIR__ . '/../lib/vendor/autoload.php';

use WebPConvert\WebPConvert;

class ctrlThumbnails
{
    public function __construct($app)
    {
        $this->thumbnails($app);
    }
    public function thumbnails($app)
    {
        $_GET = $app->vars('_get');
        $_POST = $app->vars('_post');

        $params=$app->vars('_route.params');

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
        }

        if (strpos($app->vars('_get.src'), '?')) {
            $src = explode('?', $app->vars('_get.src'));
            $app->vars('_get.src', $src[0]);
            $src= $src[0];
        }

        $app->vars('_get.zc', $app->vars('_route.zc'));
        $this->thumbnail_view($app);
        die;
    }

    public function thumbnail_view($app)
    {
        $remote = false;
        $cache = true;
        $query = $app->vars('_route.query');
        if ($app->vars('_route.http')) {
            $remote=true;
            $p='http';
        } else if ($app->vars('_route.https')) {
            $remote=true;
            $p='https';
        }

        if (isset($_SERVER['HTTP_CACHE_CONTROL'])) {
            parse_str($_SERVER['HTTP_CACHE_CONTROL'], $cc);
            if (isset($cc['no-cache']) OR isset($query['nocache'])) {
                $cache=false;
            }
        }

        if ($app->vars('_route.params') and isset($app->vars('_route.params')[0])) {
            $tmp=base64_decode($app->vars('_route.params')[0]);
            if (strpos($tmp, 'ttp://') or strpos($tmp, 'ttps://')) {
                $remote = true;
                $url = $tmp;
            }
        }

        if ($remote) {
            if (!isset($url)) {
                $url=$p.substr($app->vars('_route.uri'), strpos($app->vars('_route.uri'), '://'));
            }
            $ext = pathinfo($url, PATHINFO_EXTENSION);
            $file=$_ENV['path_app'].'/uploads/_remote/'.md5($url).'.'.$ext;
            if (!is_file($file) or !$cache) {
                $image=file_get_contents($url);
                wbPutContents($file, $image);
            }
        } else {
            $file=urldecode($_ENV['path_app'].'/'.$_GET['src']);
        }

        if (!is_file($file)) {
            $file = $app->vars('_env.path_engine').'/uploads/__system/image.svg';
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
                $image = file_get_contents($file);
                $image = str_replace('<svg ','<svg width="'.$app->vars('_route.w').'" height="'.$app->vars('_route.h').'" ',$image);
								$destination = $file;
            } else {
								if ($ext == 'jpg') $ext = 'webp';
								switch($ext) {
									case 'jpg':
										$options = ['jpeg_quality'=>85];
										break;
									case 'png':
										$options = ['png_compression_level'=>8];
										break;
									case 'webp':
										$options = ['webp_quality' => 85];
										break;
								}
                $cachefile=md5($file.'_'.$app->vars('_route.w').'_'.$app->vars('_route.h').'_'.$app->vars('_get.zc').'_'.json_encode($options)).'.'.$ext;
                $cachedir=$app->vars('_env.path_app').'/uploads/_cache/'.substr($cachefile, 0, 2);
                $destination = $cachedir.'/'.$cachefile;
                if (!is_dir($cachedir)) {
                    mkdir($cachedir, 0666, true);
                }
                if (!is_file($destination) or $cache == false) {

                    // https://imagine.readthedocs.io/en/latest/
                    if (class_exists('Imagick')) {
                        $imagine = new Imagine\Imagick\Imagine();
                    } else {
                        $imagine = new Imagine\Gd\Imagine();
                    }

                    $size    = new Imagine\Image\Box($app->vars('_route.w'), $app->vars('_route.h'));
                    if ($app->vars('_get.zc') == 0) {
                        $mode    = Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
                    } else { $mode    = Imagine\Image\ImageInterface::THUMBNAIL_INSET;}

                    $image = $imagine->open(realpath($file))->thumbnail($size, $mode);
                    file_put_contents($destination,$image);
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
        }
    }
}
