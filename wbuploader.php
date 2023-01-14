<?php

require __DIR__ . '/lib/vendor/autoload.php';
require __DIR__."/functions.php";

use Imagine\Image\Box;
use Imagine\Image\Point;

class wbuploader
{
    public $app;
    public $root;
    
    public function __construct()
    {
        $this->app = new wbApp();
        $this->root = $_SERVER['DOCUMENT_ROOT'];
        if ($this->app->vars('_post._method') == "DELETE") {
            $this->delete();
        } else {
            $this->upload();
        }
    }
    
    public function delete() {
        header("Content-type:application/json");
        $res = [];
        foreach($this->app->vars('_post.files') as $file) {
            $filepath = str_replace('//', '/',"{$this->root}/{$file}");
            $res[$file] = unlink($filepath);
        }
        echo json_encode($res);
        exit();
    }

    public function upload()
    {
        header("Content-type:application/json");
        $imgext = ['gif', 'png', 'jpg', 'jpeg', 'webp'];
        $allow = ['gif', 'png', 'jpg', 'jpeg', 'svg','webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        $error = false;

        if (isset($_POST['deletefile'])) {
        $path = $_POST['upload_url'] ? str_replace('//', '/', $_POST['upload_url']) : '/uploads';
        $folderPath = str_replace('//', '/', "{$this->root}/{$path}");
            $files = explode(',', $_POST['deletefile']);
            foreach ($files as $file) {
                @unlink($folderPath.$file);
            }
            if (is_file($folderPath.$_POST['deletefile'])) {
                $res=[
                    'error'=>true,
                    'msg'=>"Файл не удалось удалить"
                ];
            } else {
                $res=[
                    'error'=>false,
                    'msg'=>"Файл удалён"
                ];
            }
            echo json_encode($res);
            exit();
        }

        if (!$_FILES["files"]) {//No file chosen
            $res=[
                'error'=>true,
                'msg'=>"ERROR: Please browse for a file before clicking the upload button."
            ];
            echo json_encode($res);
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
            $size_raw = $_FILES["files"]["size"][0];//File size in bytes
            $size_mb = number_format(($size_raw / 1048576), 2);//Convert bytes to Megabytes
            $mime = wbMime($ext);
            $filepath = str_replace('//', '/',"{$folderPath}/{$filename}");
            $res = [
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
            if ($size_raw > $size_max) {
                $error = $res['error'] = "Превышен размер файла {$size_max}";
            }

            if (!in_array($ext, $allow)) {
                $error = $res['error'] = "Расширение файла {$ext} не разрешено";
            }

            if (!$error && move_uploaded_file($_FILES["files"]["tmp_name"][0], $filepath)) {
                if (in_array($ext, $imgext)) {
                    list($width, $height, $size_raw) = $this->prepImg($filepath);
                    $res['width'] = $width;
                    $res['height'] = $height;
                    $res['size'] = $size_raw;
                    $res['size_mb'] = number_format(($size_raw / 1048576), 2);
                }
                if ($size_raw > $size_max) {
                    $error = $res['error'] = "Превышен размер файла {$size_max}";
                    unlink($filepath);
                }
            }
            echo json_encode([$res]);
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
        return [$maxw, $maxh, filesize($file)];
    }
}
new wbuploader();
