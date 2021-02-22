<?php
require __DIR__ . '/../lib/vendor/autoload.php';

use WebPConvert\WebPConvert;
use Imagine\Image\Box;
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
								in_array($ext,['jpg','jpeg','png']) ? $ext = 'webp' : null;
								switch($ext) {
									case 'jpg':
										$options = ['jpeg_quality'=>80];
										break;
									case 'png':
										$options = ['png_compression_level'=>8];
										break;
									case 'webp':
										$options = ['webp_quality' => 90];
										break;
								}
                                $options['resolution-x'] = 300;
                                $options['resolution-y'] = 300;
                $cachefile=md5($file.'_'.$app->vars('_route.w').'_'.$app->vars('_route.h').'_'.$app->vars('_get.zc').'_'.json_encode($options)).'.'.$ext;
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

                    $size    = new Imagine\Image\Box($app->vars('_route.w'), $app->vars('_route.h'));
                    $palette = new Imagine\Image\Palette\RGB();
                    $color = $palette->color('#000', 0);
                    $canvas = $imagine->create($size, $color);
                    if ($app->vars('_get.zc') == 0) {
                        $mode    = Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
                    } else { $mode    = Imagine\Image\ImageInterface::THUMBNAIL_INSET;}
                    $image = $imagine->open(realpath($file));
                    $r1 = $image->getSize()->getWidth() / $image->getSize()->getHeight();
                    $r2 = $app->vars('_route.w') / $app->vars('_route.h');
                    $ratio = $r1 / $r2;
                    
                    if ($ratio > 1) {
                        $resize    = new Imagine\Image\Box(intval($app->vars('_route.w')) * $ratio, $app->vars('_route.h') );
                        $image->resize($resize);
                    } else if ($ratio < 1) {
                        $resize    = new Imagine\Image\Box(intval($app->vars('_route.w') * $ratio), $app->vars('_route.h'));
                        $image->resize($resize);
                    } else {
                        $image->resize($size);
                    }
                    $image->thumbnail($size, $mode);
                    
                    $canvasCenter = new Imagine\Image\Point\Center($canvas->getSize());
                    $imageCenter = new Imagine\Image\Point\Center($image->getSize());
                    $offsetX = $canvasCenter->getX() - $imageCenter->getX();
                    $offsetY = $canvasCenter->getY() - $imageCenter->getY();
                    $offsetX < 0 ? $offsetX = 0 : null;
                    $offsetY < 0 ? $offsetY = 0 : null;
                    $canvas->paste($image, new Imagine\Image\Point($offsetX, $offsetY));
                    $canvas->save($destination,$options);
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
