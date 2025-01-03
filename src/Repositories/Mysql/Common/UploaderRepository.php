<?php

namespace App\Repositories\Mysql\Common;

use App\Domain\Common\FilesInterface;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Slim\Psr7\UploadedFile;
use Wanphp\Libray\Mysql\Database;
use Wanphp\Libray\Slim\Setting;

class UploaderRepository implements \Wanphp\Libray\Slim\UploaderInterface
{
  private Database $database;
  private string $filepath;
  private array $extension = ['jpg', 'jpeg', 'gif', 'png', 'pdf', 'mp3', 'mp4', 'txt'];
  private array $fileType = ['image/gif', 'image/jpg', 'image/png', 'application/pdf', 'image/jpeg', 'audio/mpeg', 'video/mp4', 'text/plain', 'application/octet-stream'];

  public function __construct(Database $database, Setting $setting)
  {
    $this->database = $database;
    $this->filepath = $setting->get('uploadFilePath');
    if ($setting->get('allowFileExtension')) $this->extension = $setting->get('allowFileExtension');
    if ($setting->get('allowFileType')) $this->fileType = $setting->get('allowFileType');
  }

  /**
   * @inheritDoc
   * @throws Exception
   */
  public function uploadFile(array $formData, UploadedFile $uploadedFile): array
  {
    // 检查上传文件
    $extension = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
    $fileType = $uploadedFile->getClientMediaType();
    $content = file_get_contents($uploadedFile->getFilePath());
    if (!in_array($extension, $this->extension) || !in_array($fileType, $this->fileType) || preg_match('/<\?php/i', $content)) {
      throw new Exception('文件类型错误！');
    }

    $data = [
      'name' => $uploadedFile->getClientFilename(),
      'type' => $formData['type'] ?? $uploadedFile->getClientMediaType(),
      'md5' => $formData['md5'] ?? md5_file($uploadedFile->getFilePath()),
      'size' => $uploadedFile->getSize(),
      'extension' => strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION)),
      'uid' => $formData['uid'] ?: 0,
      'uptime' => time()
    ];

    $type = explode('/', $data['type']);
    if ($type[0] == 'application') $filepath = '/' . $data['extension'] . date('/Ym');
    else $filepath = '/' . $type[0] . date('/Ym');
    if (!is_dir($this->filepath . $filepath)) mkdir($this->filepath . $filepath, 0755, true);

    //大文件分块上传
    if (isset($formData['chunks']) && $formData['chunks'] > 0) {
      $tmpPath = $this->filepath . '/tmp/' . $data['md5'];//上传文件临时目录

      if ($formData['current_chunk'] == 1 && is_dir($tmpPath)) { //断点续传
        //已上传数量
        $current_chunk = 1;
        $num = 1;
        while ($num > 0) {
          $cacheFile = $tmpPath . '/' . $num . '.dat';
          if (!file_exists($cacheFile)) {
            if ($num == 1) {
              $this->moveUploadedFile($tmpPath, $uploadedFile, '1.dat');
              $current_chunk = 1;
            } else {
              $current_chunk = $num - 1;//最后上传文件块
            }
            $num = -1;
          } else {
            $num++;
          }
        }

        if ($current_chunk >= $formData['current_chunk'] && $current_chunk < $formData['chunks']) {//继续上传
          $formData['current_chunk'] = $current_chunk;
          return ['current_chunk' => $formData['current_chunk'], 'msg' => '继续上传第' . ($formData['current_chunk'] + 1) . '块文件！'];
        }
      }

      //创建临时目录，上传文件块
      if (!is_dir($tmpPath)) mkdir($tmpPath, 0755, true);
      $this->moveUploadedFile($tmpPath, $uploadedFile, $formData['current_chunk'] . '.dat');

      if ($formData['current_chunk'] == $formData['chunks']) {//最后一块,合成大文件
        $extension = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $filePath = $filepath . DIRECTORY_SEPARATOR . $filename;
        $fp = fopen($this->filepath . $filePath, "wb");
        $num = 1;
        while ($num > 0) {
          $cacheFile = $tmpPath . '/' . $num++ . '.dat';
          if (file_exists($cacheFile)) {
            $handle = fopen($cacheFile, 'rb');
            $content = fread($handle, filesize($cacheFile));
            fwrite($fp, $content);
            fclose($handle);
            unset($handle);
            //删除临时文件
            unlink($cacheFile);
          } else {
            $num = -1;
            //删除目录
            rmdir($tmpPath);
          }
        }
        fclose($fp);
        unset($fp);

        $data['size'] = filesize($this->filepath . $filePath);
        $data['url'] = $filePath;
      } else {
        return ['current_chunk' => $formData['current_chunk'], 'msg' => '第' . $formData['current_chunk'] . '块文件上传成功！'];
      }
    } else {
      $filename = $this->moveUploadedFile($this->filepath . $filepath, $uploadedFile);
      $data['url'] = $filepath . DIRECTORY_SEPARATOR . $filename;
    }
    $this->database->insert(FilesInterface::TABLE_NAME, $data);

    return ['id' => $this->database->id(), 'type' => $data['type'], 'host' => $formData['host'], 'url' => $data['url']];
  }

  /**
   * @throws Exception
   * @throws GuzzleException
   */
  public function downloadFile(string $url): array
  {
    if (str_starts_with($url, '//')) $url = 'https' . $url;
    $test = parse_url($url);
    $client = new \GuzzleHttp\Client([
      'headers' => ['Referer' => $test['host'] ?? '']
    ]);
    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $basename, 'dat');
    if (!is_dir($this->filepath . '/temp/')) mkdir($this->filepath . '/temp/', 0755, true);
    $downloadFile = $this->filepath . '/temp/' . $filename;
    $resp = $client->request('GET', $url, ['sink' => $downloadFile]);

    // 检查上传文件
    $fileType = $resp->getHeaderLine('Content-Type');
    $extension = match ($fileType) {
      'video/mp4' => 'mp4',
      'video/quicktime' => 'mov',
      'audio/mpeg' => 'mp3',
      'image/jpeg', 'image/jpg' => 'jpg',
      'image/png' => 'png',
      'image/gif' => 'gif',
      default => ''
    };

    $fileMD5 = md5_file($downloadFile);
    //文件已上传过
    $file = $this->database->get(FilesInterface::TABLE_NAME,['id','url'], ['md5' => $fileMD5]);
    if (isset($file['id'])) {
      unlink($downloadFile);
      return ['id' => $file['id'], 'type' => $fileType, 'url' => $file['url']];
    }
    $content = file_get_contents($downloadFile);
    if (!in_array($extension, $this->extension) || !in_array($fileType, $this->fileType) || preg_match('/<\?php/i', $content)) {
      throw new Exception('文件类型错误！');
    }

    $type = explode('/', $fileType);
    if ($type[0] == 'application') $filepath = '/' . $extension . date('/Ym');
    else $filepath = '/' . $type[0] . date('/Ym');
    if (!is_dir($this->filepath . $filepath)) mkdir($this->filepath . $filepath, 0755, true);

    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $basename, $extension);
    if (rename($downloadFile, $this->filepath . $filepath . DIRECTORY_SEPARATOR . $filename)) {
      $data = [
        'name' => '来源' . $test['host'],
        'type' => $fileType,
        'md5' => $fileMD5,
        'size' => filesize($this->filepath . $filepath . DIRECTORY_SEPARATOR . $filename),
        'url' => $filepath . DIRECTORY_SEPARATOR . $filename,
        'extension' => $extension,
        'uid' =>  0,
        'uptime' => time()
      ];
      $this->database->insert(FilesInterface::TABLE_NAME, $data);
      return ['id' => $this->database->id(), 'type' => $data['type'], 'url' => $data['url']];
    }
    return ['errMsg' => '下载失败'];
  }

  /**
   * @inheritDoc
   */
  public function setName(int $id, string $name): int
  {
    $res = $this->database->update(FilesInterface::TABLE_NAME, ['name' => $name], ['id' => $id]);
    if ($res) return $res->rowCount();
    else return 0;
  }

  /**
   * @inheritDoc
   */
  public function delFile(int|string $file, bool $deleteFile = true): int
  {
    if (is_numeric($file)) {
      $id = intval($file);
      if ($deleteFile) $filepath = $this->database->get(FilesInterface::TABLE_NAME, 'url', ['id' => $id]);
      $res = $this->database->delete(FilesInterface::TABLE_NAME, ['id' => $id]);
      if ($res) {
        if (!empty($filepath) && is_file($this->filepath . $filepath)) unlink($this->filepath . $filepath); //删除文件
        return $res->rowCount();
      } else {
        return 0;
      }
    } else {
      $id = $this->database->get(FilesInterface::TABLE_NAME, 'id', ['url' => $file]);
      if ($id) {
        $res = $this->database->delete(FilesInterface::TABLE_NAME, ['id' => $id]);
        if ($res) {
          if ($deleteFile && is_file($this->filepath . $file)) unlink($this->filepath . $file); //删除文件
          return $res->rowCount();
        } else {
          return 0;
        }
      } else {
        return 0;
      }
    }
  }

  /**
   * @param string $directory
   * @param UploadedFile $uploadedFile
   * @param string $filename
   * @return string
   * @throws Exception
   */
  private function moveUploadedFile(string $directory, UploadedFile $uploadedFile, string $filename = ''): string
  {
    $extension = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));

    if ($filename == '') {
      // see http://php.net/manual/en/function.random-bytes.php
      $basename = bin2hex(random_bytes(8));
      $filename = sprintf('%s.%0.8s', $basename, $extension);
    }

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
  }

  /**
   * 允许上传文件扩展名
   * @param array $extension
   */
  public function allowExtension(array $extension): void
  {
    $this->extension = $extension;
  }

  /**
   * 允许上传方的类型
   * @param array $fileType
   */
  public function allowFileType(array $fileType): void
  {
    $this->fileType = $fileType;
  }

}
