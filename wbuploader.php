<?php

require __DIR__ . '/lib/vendor/autoload.php';
require __DIR__."/functions.php";

use Imagine\Image\Box;
use Imagine\Image\Point;
use WebPConvert\WebPConvert;

class wbuploader
{
    public $app;
    public $root;
    public $imgext = ['gif', 'png', 'jpg', 'jpeg', 'webp'];
    private $res;
    public function __construct()
    {
        $this->app = new wbApp();
        $this->root = $_SERVER['DOCUMENT_ROOT'];
        if ($this->app->vars('_post._method') == "DELETE") {
            $this->delete();
        } else if ($this->app->vars('_post._method') == "PATCH") {
                $this->patch();
        } else {
            $this->upload();
        }
    }
    
    public function delete() {
        header("Content-type:application/json");
        $this->res = [];
        foreach($this->app->vars('_post.files') as $file) {
            $filepath = str_replace('//', '/',"{$this->root}/{$file}");
            unlink($filepath);
        }
        $this->res[$file] = !is_file($filepath);
        echo json_encode($this->res);
        exit();
    }

    public function patch() {
        $imgext = $this->imgext;
        $file = $this->app->vars('_post.file');
        $filepath = realpath(str_replace('//', '/', "{$this->root}/{$file}"));
        $filename = pathinfo($filepath, PATHINFO_BASENAME);
        $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        if (!in_array($ext, $imgext)) {
            $this->res = [
                'error' => true,
                'msg' => "Расширение файла {$ext} не разрешено"
            ];
            echo json_encode($this->res);
            exit();
        }

        $imagine    = class_exists('Imagick') ? new Imagine\Imagick\Imagine() : new Imagine\Gd\Imagine();
        $image      = $imagine->open($filepath);
        $sx = intval($this->app->vars('_post.x'),0);
        $sy = intval($this->app->vars('_post.y'),0);
        $sx < 0 ? $sx = 0 : null;
        $sy < 0 ? $sy = 0 : null;
        $width = intval($this->app->vars('_post.width'),0);
        $height = intval($this->app->vars('_post.height'),0);
        $image->crop(new Point($sx, $sy), new Box($width, $height))
        ->save($filepath);
        $size_raw = filesize($filepath);
        $size_mb = number_format(($size_raw / 1048576), 2);//Convert bytes to Megabytes
        $this->res = [
            'extension' => $ext,
            'width' => $width,
            'height' => $height,
            'name' => $filename,
            'original' => $filename,
            'size' => $size_raw,
            'size_mb' => $size_mb,
            'time' => time(),
            'type' => wbMime($ext),
            'url' => $file
        ];
        echo json_encode($this->res);
        exit();
    }

    public function upload()
    {
        header("Content-type:application/json");
        $imgext = $this->imgext;
        $allow = ['gif', 'png', 'jpg', 'jpeg', 'svg','webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        $error = false;

        if (!$_FILES["files"]) {//No file chosen
            $this->res=[
                'error'=>true,
                'msg'=>"ERROR: Please browse for a file before clicking the upload button."
            ];
            echo json_encode($this->res);
            exit();
        } else {
            $original = $_FILES["files"]["name"][0];
            if ($this->app->vars('_post.upload_url') == '_auto_') {
                $this->app->vars('_post.upload_url', '/uploads/' . substr(md5($original), 0, 2));
            } 
            $path = $_POST['upload_url'] ? str_replace('//', '/', $_POST['upload_url']) : '/uploads';

            if ($this->app->vars('_post.upload_ext')) {
                $allow = wbAttrToArray($this->app->vars('_post.upload_ext'));
            }

            $folderPath = str_replace('//', '/', "{$this->root}/{$path}");

            $ext = strtolower(pathinfo($_FILES['files']['name'][0], PATHINFO_EXTENSION));
            //Directory to put file into
            is_dir($folderPath) ? null : mkdir($folderPath, 0777, true);
            //File name
            $folderPath = str_replace('//', '/', "{$this->root}/{$path}");
            $filename = $original;
            if ($this->app->vars('_post.name') == 'original') {
                $filename = $original;
            } elseif ($this->app->vars('_post.name') == 'random') {
                $filename = 'fn'.uniqid().'.'.$ext;
            } elseif ($this->app->vars('_post.original') == 'false') {
                $filename = 'fn' . uniqid() . '.' . $ext;
            } elseif (isset($_POST['name'])) {
                $filename = $_POST['name'];
            }
            $size_max = $this->app->vars("_sett.max_upload_size");
            $webp       = (string)$this->app->vars('_post.webp');
            $size_raw = $_FILES["files"]["size"][0];//File size in bytes
            $size_mb = number_format(($size_raw / 1048576), 2);//Convert bytes to Megabytes
            $mime = wbMime($ext);
            $filepath = str_replace('//', '/',"{$folderPath}/{$filename}");
            $this->res = [
                'extension'=>$ext,
                'size'=>$size_raw,
                'size_mb'=>$size_mb,
                'time'=>time(),
                'type'=>$mime,
                'name'=>$filename,
                'original'=>$original,
                'url' => str_replace('//', '/', $path.'/'.$filename),
                'width' => '',
                'height' =>''
            ];
            if ($allow !== ['*'] && !in_array($ext, $allow)) {
                $error = $this->res['error'] = "Расширение файла {$ext} не разрешено";
            } else  if ($size_raw == 0 || $size_raw > $size_max) {
                $error = $this->res['error'] = "Превышен размер файла {$size_max}";
            } else if ($size_raw == 0) {
                $error = $this->res['error'] = "Не удалось загрузить файл {$filename}";
                unlink($filepath);
            }
            if (!$error && move_uploaded_file($_FILES["files"]["tmp_name"][0], $filepath)) {
                if (in_array($ext, $imgext)) {
                    $this->prepImg($filepath);
                }
                if ($size_raw > $size_max) {
                    $error = $this->res['error'] = "Превышен размер файла {$size_max}";
                    unlink($filepath);
                }
            }
            echo json_encode([$this->res]);
        }
    }

    public function prepImg($file)
    {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $imagine    = class_exists('Imagick') ? new Imagine\Imagick\Imagine() : new Imagine\Gd\Imagine();
        $image      = $imagine->open(realpath($file));
        $height     = $image->getSize()->getHeight();
        $width      = $image->getSize()->getWidth();

        $maxw       = $this->app->vars('_sett.modules.filepicker.resizeto');
        $maxq       = $this->app->vars('_sett.modules.filepicker.quality');
        $maxw       = $maxw == '' ? $width : $maxw*1;
        $maxw       = $maxw >= $width ? $width : $maxw;

        $maxq       = $maxq == '' ? 80 : $maxq*1;
        $maxh       = intval($height * ($maxw/$width));

        $size       = new Imagine\Image\Box($maxw, $maxh);
        $palette    = new Imagine\Image\Palette\RGB();
        $color      = $image->getColorAt(new Imagine\Image\Point(0, 0)).'';
        $color      = $palette->color($color, 0);
        $canvas     = $imagine->create($size, $color);
        $mode       = Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
        $r1         = $width / $height;
        $r2         = $maxw / $maxh;
        $ratio      = $r1 / $r2;
        $webp       = (string)$this->app->vars('_post.webp');
        if ($ratio < 1) {
            $resize    = new Imagine\Image\Box(intval($maxw), $maxh / $ratio);
            $image->resize($resize);
        } elseif ($ratio > 1) {
            $resize    = new Imagine\Image\Box(intval($maxw * $ratio), $maxh);
            $image->resize($resize);
        } else {
            $image->resize($size);
        }

        $image->thumbnail($size, $mode);

        $canvasCenter = new Imagine\Image\Point\Center($canvas->getSize());
        $imageCenter = new Imagine\Image\Point\Center($image->getSize());

        if ($image->getSize()->getWidth() > $maxw) {
            $offsetX = ($image->getSize()->getWidth() - $maxw) / 2;
            $image->crop(new Point($offsetX, 0), $size);
        }

        if ($image->getSize()->getHeight() > $maxh) {
            $offsetY = ($image->getSize()->getHeight() - $maxh) / 2;
            $image->crop(new Point(0, $offsetY), $size);
        }

        $offsetX = $canvasCenter->getX() - $imageCenter->getX();
        $offsetY = $canvasCenter->getY() - $imageCenter->getY();
        $offsetX < 0 ? $offsetX = 0 : null;
        $offsetY < 0 ? $offsetY = 0 : null;
        $canvas->paste($image, new Imagine\Image\Point($offsetX, $offsetY));

        $options = [
            'resolution-x' => 300,
            'resolution-y' => 300
        ];

        switch ($ext) {
            case 'jpg':
                $options = ['jpeg_quality'=>$maxq];
                break;
            case 'png':
                $options = ['png_compression_level'=>$maxq/10];
                break;
            case 'webp':
                $options = ['webp_quality' => $maxq];
                break;
        }
        $canvas->save($file, $options);
        if ($webp == 'on' && $ext !== 'webp' && in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $options = [];
            $prev = $file;
            $file = substr($prev, 0, - (strlen($ext) + 1)) . '.webp';
            WebPConvert::convert($prev, $file, $options);

            $this->res['name'] = substr($this->res['name'], 0, - (strlen($ext) + 1)) . '.webp';
            $this->res['url'] = substr($this->res['url'], 0, - (strlen($ext) + 1)) . '.webp';
            $this->res['extension'] = 'webp';
            $this->res['type'] = wbMime($file);
        }
        $this->res['width'] = $maxw;
        $this->res['height'] = $maxh;
        $this->res['size'] = filesize($file);
        $this->res['size_mb'] = number_format(($this->res['size'] / 1048576), 2);
    }
}
new wbuploader();
