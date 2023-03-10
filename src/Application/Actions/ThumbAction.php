<?php

namespace App\Application\Actions;

use App\Domain\Common\SettingInterface;
use Intervention\Image\ImageManagerStatic;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Wanphp\Libray\Slim\Setting;

class ThumbAction extends Action
{
  private SettingInterface $config;
  private string $filepath;

  public function __construct(LoggerInterface $logger, Setting $setting, SettingInterface $config)
  {
    parent::__construct($logger);
    $this->filepath = $setting->get('uploadFilePath');
    $this->config = $config;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $path = $this->resolveArg('path');
    $name = $this->resolveArg('name');
    $thumbName = $this->resolveArg('thumbName');
    $extension = pathinfo($thumbName, PATHINFO_EXTENSION);
    // w120x120_watermark-br-4
    $filename = pathinfo($thumbName, PATHINFO_FILENAME);

    $file = $this->filepath . "/image/{$path}/{$name}.{$extension}";
    $image = ImageManagerStatic::make($file);
    $thumb = $this->filepath . "/image/thumb/{$path}/{$name}/{$thumbName}";

    if (!file_exists($file)) {
      return $this->respondWithError('Image Not Found');
    }
    //创建缩略图路径
    if (!is_dir($this->filepath . "/image/thumb/{$path}/{$name}")) mkdir($this->filepath . "/image/thumb/{$path}/{$name}", 0755, true);

    $resize = substr($filename, 0, 1);
    if (is_numeric($filename)) {
      $width = $height = $filename;
    } else {
      if (in_array($resize, ['w', 'h'])) $arr = explode('_', substr($filename, 1));
      else $arr = explode('_', $filename);

      $width = 0;
      $height = 0;
      if ($arr[0] != '') {
        if (is_numeric($arr[0])) {
          $width = $height = $arr[0];
        } else {
          $args = explode('x', $arr[0]);
          if (is_numeric($args['0'])) $width = intval($args['0']);
          if (isset($args['1']) && is_numeric($args['1'])) $height = intval($args['1']);
        }
      }

      if (isset($arr['1'])) {//水印
        $watermark = $this->config->get('value', ['key' => $arr['1']]);
        if ($watermark) {
          $args = explode('-', $arr[1]);
          if (isset($args['0'])) {
            // 取水印图片
            $watermark_path = $this->filepath . '/image' . $watermark;
            if (file_exists($watermark_path)) {
              $position = $args['1'] ?? 'br';//水印位置,tl,tr,bl,br
              $proportion = $args['2'] ?? 4;//水印比例1/4
            }
          }
        }
      }
    }

    if ($width > 0 && $height > 0) {
      switch ($resize) {
        case 'w'://宽度固定，高度自动
          if ($image->width() > $width) $image->resize($width, null, function ($constraint) {
            $constraint->aspectRatio();
          });
          break;
        case  'h':
          if ($image->height() > $height) $image->resize(null, $height, function ($constraint) {
            $constraint->aspectRatio();
          });
          break;
        default:
          if ($image->width() > $width && $width == 640 && $width == $height) $image->resize($width, null, function ($constraint) {
            $constraint->aspectRatio();
          });
          else if ($image->width() > $width) $image->fit($width, $height, function ($constraint) {//剪切并调整为给定尺寸
            $constraint->upsize();
          });
      }
    }
    if (isset($watermark_path) && file_exists($watermark_path) && isset($position)) {
      $watermark = ImageManagerStatic::make($watermark_path);
      if (isset($proportion)) {
        //水印占图片宽度比例
        $scale = ($image->width() / $proportion) / $watermark->width();
        if ($scale < 1) $watermark->resize(intval($watermark->width() * $scale), intval($watermark->height() * $scale));
      }
      switch ($position) {
        case 'tl':
          $image->insert($watermark);
          break;
        case 'tr':
          $image->insert($watermark, 'top-right');
          break;
        case 'bl':
          $image->insert($watermark, 'bottom-left');
          break;
        case 'br':
          $image->insert($watermark, 'bottom-right');
          break;
        default:
          $image->insert($watermark, 'center');
      }
    }
    $image->save($thumb);
    $this->response = $this->response->withHeader('Content-Type', 'image/png');
    $this->response->getBody()->write((string)$image->encode('png'));
    return $this->response;
  }
}
