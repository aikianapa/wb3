<?php
use WebPConvert\WebPConvert;

class ctrlThumbnails
{
    public function __construct($app)
    {
        $this->thumbnails($app);
    }
    public function thumbnails($app)
    {
        $_GET = $app->vars("_get");
        $_POST = $app->vars("_post");

        $params=$app->vars("_route.params");
        if ($app->vars("_get")) {
            $app->vars("_get", []);
            $app->vars("_get.w", $app->vars("_route.w"));
            $app->vars("_get.h", $app->vars("_route.h"));
        }
        if ($app->vars("_route.params.src") > "") {
            $app->vars("_get.src", $params["src"]);
        } else {
            $re = '/[\/thumbc|thumb\/].*\/src\/(.*)/m';
            preg_match($re, $app->vars("_route.uri"), $matches, PREG_OFFSET_CAPTURE, 0);

            $app->vars("_get.src", $matches[1][0]);
        }

        if (strpos($app->vars("_get.src"), "?")) {
            $src = explode("?", $app->vars("_get.src"));
            $app->vars("_get.src", $src[0]);
            $src= $src[0];
        }

        $app->vars("_get.zc", $app->vars("_route.zc"));
        $this->thumbnail_view($app);
        die;
    }

    public function thumbnail_view($app)
    {
        $remote = false;
        $cache = true;
        $query = $app->vars("_route.query");
        if ($app->vars("_route.http")) {
            $remote=true;
            $p="http";
        }
        if ($app->vars("_route.https")) {
            $remote=true;
            $p="https";
        }
        if (isset($query["nocache"])) {
            $cache=false;
        }
        if ($app->vars("_route.params") and isset($app->vars("_route.params")[0])) {
            $tmp=base64_decode($app->vars("_route.params")[0]);
            if (strpos($tmp, "ttp://") or strpos($tmp, "ttps://")) {
                $remote = true;
                $url = $tmp;
            }
        }

        if ($remote) {
            if (!isset($url)) {
                $url=$p.substr($app->vars("_route.uri"), strpos($app->vars("_route.uri"), "://"));
            }
            $ext = pathinfo($url, PATHINFO_EXTENSION);
            $file=$_ENV["path_app"]."/uploads/_remote/".md5($url).".".$ext;
            if (!is_file($file) or !$cache) {
                $image=file_get_contents($url);
                wbPutContents($file, $image);
            }
        } else {
            $file=urldecode($_ENV["path_app"]."/".$_GET["src"]);
        }
        if (is_file($file)) {
            list($width, $height, $type) = $size = getimagesize($file);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $mime=$size["mime"];
            $cachefile=md5($file."_".$app->vars("_route.w")."_".$app->vars("_route.h")).".".$ext;
            $cachedir=$app->vars("_env.path_app")."/uploads/_cache/".substr($cachefile, 0, 2);
            $destination = $cachedir."/".$cachefile;
            if (!is_dir($cachedir)) {
                $u=umask();
                mkdir($cachedir, 0766, true);
                umask($u);
            }
            if (!is_file($destination) or $cache == false) {
                if (class_exists("Imagick")) {
                    $image = new \Imagick(realpath($file));
                    if ($remote) {
                        unlink($file);
                    }
                    if ($app->vars("_get.zc")==0) {
                        $image->cropThumbnailImage($app->vars("_route.w"), $app->vars("_route.h"), true);
                    } else {
                        $image->thumbnailImage($app->vars("_route.w"), $app->vars("_route.h"), true);
                    }
                    $image->writeImage($destination);
                    if (in_array($ext, ["jpg","jpeg"])) {
                        $options = [];
                        WebPConvert::convert($destination, $destination.".webp", $options);
                    }
                } else {
                    $image=$this->thumbnail_view_gd(realpath($file), $app->vars("_route.w"), $app->vars("_route.h"));
                    header("Content-Type: ".$mime);
                    if ($type==3) {
                        imagepng($image, $destination);
                    }
                    if ($type==2) {
                        imagejpeg($image, $destination);
                    }
                    if ($type==1) {
                        imagegif($image, $destination);
                    }
                    $image=file_get_contents($destination);
                }
            } else {
                $image=file_get_contents($destination);
            }
            header("Content-Type: ".$mime);
            echo $image;
        }
    }

    public function thumbnail_view_gd($imgSrc, $thumbnail_width, $thumbnail_height)
    { //$imgSrc is a FILE - Returns an image resource.
        //getting the image dimensions
        list($width_orig, $height_orig, $type) = getimagesize($imgSrc);
        // Image is PNG:
        if ($type == IMAGETYPE_PNG) {
            $myImage = imagecreatefrompng(realpath($imgSrc));
        }

        // Image is JPEG:
        elseif ($type == IMAGETYPE_JPEG) {
            $myImage = imagecreatefromjpeg(realpath($imgSrc));
        }
        // Image is GIF:
        elseif ($type == IMAGETYPE_GIF) {
            $myImage = imagecreatefromgif(realpath($imgSrc));
        }

        $ratio_orig = $width_orig/$height_orig;

        if ($thumbnail_width/$thumbnail_height > $ratio_orig) {
            $new_height = $thumbnail_width/$ratio_orig;
            $new_width = $thumbnail_width;
        } else {
            $new_width = $thumbnail_height*$ratio_orig;
            $new_height = $thumbnail_height;
        }

        $x_mid = $new_width/2;  //horizontal middle
    $y_mid = $new_height/2; //vertical middle

    $process = imagecreatetruecolor(round($new_width), round($new_height));

        imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
        $thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);

        imagedestroy($process);
        imagedestroy($myImage);
        return $thumb;
    }
}
