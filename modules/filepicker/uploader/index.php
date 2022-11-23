<?php
// Include composer autoload
ini_set('display_errors', 0);
require __DIR__.'/../vendor/autoload.php';
require $_SERVER["DOCUMENT_ROOT"]."/engine/functions.php";


use Hazzard\Filepicker\Handler;
use Hazzard\Filepicker\Uploader;
use Intervention\Image\ImageManager;
use Hazzard\Config\Repository as Config;

$app = new wbApp();

//if (!$app->vars('_sess.user_role') == 'admin') return;

if (class_exists('Imagick')) {
    $uploader = new Uploader($config = new Config, new ImageManager(array('driver' => 'imagick')));
} else {
    $uploader = new Uploader($config = new Config, new ImageManager(array('driver' => 'gd')));
}

$handler = new Handler($uploader);

// Configuration

$config['debug'] = false;


//$app->vars('_sess.user_role') == 'admin' ? $config['accept_file_types_regex'] = '/.*/' : null;

isset($_POST["file"]) ? $file = $_POST["file"] : $file = wbNewId("img");

if ($app->vars("_req.upload_ext") > "") {
    $tmp = $app->vars("_req.upload_ext");
    $tmp = implode('|',$app->attrToArray($tmp));
    $config['accept_file_types'] = $tmp;
}
if ($app->vars('_req.upload_url') == '_auto_') {
    $config['upload_url'] = '/uploads/'.substr(md5($file),0,2);
} else {
    $config['upload_url'] = $app->vars("_req.upload_url");
}

$config['script_url'] = $app->vars("_route.host").'/engine/modules/filepicker/uploader/index.php';
$config['overwrite'] = true;
$config['max_file_size'] = $app->vars("_sett.max_upload_size");
$config['auto_orient'] = true;
$config['image_file_types'] = 'gif|jpg|jpeg|png';
$config['inline_file_types'] = 'webp|svg|pdf|doc|docx|xls|xlsx|zip|rar|gzip|css|scss|less|txt|log';

if (isset($_POST["_method"])) {
    isset($_POST["file"]) ? $file = $_POST["file"] : null;
    isset($_POST["files"]) ? $file = $_POST["files"][0] : null;
    $dir = explode("/",$file);
    $_POST["file"] = array_pop($dir);
    $config['upload_url'] = implode("/",$dir);
} 
$config['upload_dir'] = wbNormalizePath($_SERVER["DOCUMENT_ROOT"].$config['upload_url']);

if (!is_dir($config['upload_dir'])) mkdir($config['upload_dir'],0755);

//$_ENV['upload_url'] = $config['upload_url'];
/*
$config['image_versions.thumb'] = array(
    'width' => 200,
    'height' => 200
);
*/
// Events

/**
 * Fired before the file upload starts.
 *
 * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
 */
$handler->on('upload.before', function ($file) {
    $_ENV['original'] = $file->getClientOriginalName();
    $rename = true;
    if (isset($_POST['original']) && ($_POST['original'] == '1' OR $_POST['original'] == 'true') ) $rename = false;
    if (isset($_POST["rename"]) && $_POST["rename"] == "false") $rename = false;
    if (!$rename) {
        $name = $file->getClientOriginalName();
        $name = explode('.', $name);
        count($name) > 1 ? array_pop($name) : null;
        $file->save = implode('.', $name);
    } else {
        $file->save = wbNewId("img");
    }
    // throw new \Hazzard\Filepicker\Exception\AbortException('Error message!');
});

/**
 * Fired on upload success.
 *
 * @param \Symfony\Component\HttpFoundation\File\File $file
 */
$handler->on('upload.success', function ($file) {
  $app = $_ENV["app"];
  $ext = $file->getExtension();
  if (!in_array($ext,['jpeg','jpg','png','gif','webp'])) return;
  $resize = intval($app->vars("_sett.modules.filepicker.resizeto"));
  $quality = intval($app->vars("_sett.modules.filepicker.quality"));
  if ($resize > 0) {
      $manager = new ImageManager(array('driver' => 'imagick'));
      $pathname = $file->getRealPath();
      $image = $manager->make($pathname);
      if ($image->width() > $resize) {
          $image->resize($resize, null, function ($constraint) {
              $constraint->aspectRatio();
          });
          if ($quality > 0) {
              $image->save($pathname,$quality);
          } else {
              $image->save();
          }
      }
  }
});
/**
 * Fired on upload error.
 *
 * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
 */
$handler->on('upload.error', function ($file) {

});

/**
 * Fired when fetching files.
 *
 * @param array &$files
 */
$handler->on('files.fetch', function (&$files) {
    // Set the array of files to be returned.
    // $files = array('file1', 'file2', 'file3');
});

/**
 * Fired on file filtering.
 *
 * @param array &$files
 * @param int   &$total
 */
$handler->on('files.filter', function ($files, &$total) {
    if (!isset($_REQUEST["upload_ext"])) return;
    $exts = explode("|",$_REQUEST["upload_ext"]);
    foreach ($files as $i => $file) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (!in_array($ext,$exts)) {
            unset($files[$i]);
            $total--;
        }
    }
});

/**
 * Fired on file download.
 *
 * @param \Symfony\Component\HttpFoundation\File\File $file
 * @param string $version
 */
$handler->on('file.download', function ($file, $version) {

});

/**
 * Fired on file deletion.
 *
 * @param \Symfony\Component\HttpFoundation\File\File $file
 */
$handler->on('file.delete', function ($file) {

});

/**
 * Fired before cropping.
 *
 * @param \Symfony\Component\HttpFoundation\File\File $file
 * @param \Intervention\Image\Image $image
 */
$handler->on('crop.before', function ($file, $image) {

});

/**
 * Fired after cropping.
 *
 * @param \Symfony\Component\HttpFoundation\File\File $file
 * @param \Intervention\Image\Image $image
 */
$handler->on('crop.after', function ($file, $image) {

});

// Handle the request.
$handler->handle()->send();