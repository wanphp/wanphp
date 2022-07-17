<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/24
 * Time: 16:36
 */

namespace App\Application\Api\Common;


use App\Application\Api\Api;
use App\Domain\Common\FilesInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;

class FilesApi extends Api
{
  private FilesInterface $files;
  private string $filepath;

  /**
   * @param ContainerInterface $container
   * @param FilesInterface $files
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function __construct(ContainerInterface $container, FilesInterface $files)
  {
    $settings = $container->get('settings');
    $this->files = $files;
    $this->filepath = $settings['uploadFilePath'];
  }

  /**
   * @return Response
   * @throws Exception
   * @OA\Post(
   *  path="/api/file",
   *  tags={"System"},
   *  summary="上传文件",
   *  operationId="uploadFile",
   *  security={{"bearerAuth":{}}},
   *   @OA\RequestBody(
   *     description="上传文件,大文件分块上传，需提供全部数据，小文件上传提供file即可。",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(
   *         title="File",
   *         required={"file"},
   *         @OA\Property(property="file",type="object",description="上传文件"),
   *         @OA\Property(property="chunks",type="integer",description="文件分块数"),
   *         @OA\Property(property="current_chunk",type="integer",description="当前传传分块"),
   *         @OA\Property(property="type",type="string",description="文件类型"),
   *         @OA\Property(property="size",type="integer",description="文件大小"),
   *         @OA\Property(property="md5",type="string",description="文件MD5值")
   *       )
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="201文件上传成功,202文件分块上传成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="datas",
   *          @OA\Property(property="id",type="integer",description="文件ID"),
   *          @OA\Property(property="type",type="string",description="文件类型"),
   *          @OA\Property(property="url",type="string",description="文件地址"),
   *          @OA\Property(property="current_chunk",type="integer",description="当前文件块"),
   *          @OA\Property(property="msg",type="string",description="上传说明"))
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Patch(
   *  path="/api/file/{ID}",
   *  tags={"System"},
   *  summary="修改上传文件名",
   *  operationId="editFile",
   *  security={{"bearerAuth":{}}},
   *   @OA\Parameter(
   *     name="ID",
   *     in="path",
   *     description="路由ID",
   *     required=true,
   *     @OA\Schema(format="int64",type="integer")
   *   ),
   *   @OA\RequestBody(
   *     description="修改上传文件名",
   *     required=true,
   *     @OA\MediaType(
   *       mediaType="application/json",
   *       @OA\Schema(
   *         title="File",
   *         required={"file"},
   *         @OA\Property(property="name",type="string",description="文件名")
   *       )
   *     )
   *   ),
   *  @OA\Response(
   *    response="201",
   *    description="请求成功",
   *    @OA\JsonContent(
   *      allOf={
   *       @OA\Schema(ref="#/components/schemas/Success"),
   *       @OA\Schema(
   *         @OA\Property(property="datas",@OA\Property(property="up_num",type="integer"))
   *       )
   *      }
   *    )
   *  ),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   * @OA\Get(
   *  path="/api/files",
   *  tags={"System"},
   *  summary="上传文件",
   *  operationId="listFile",
   *  security={{"bearerAuth":{}}},
   *  @OA\Response(response="200",description="请求成功",@OA\JsonContent(ref="#/components/schemas/Success")),
   *  @OA\Response(response="400",description="请求失败",@OA\JsonContent(ref="#/components/schemas/Error"))
   * )
   */
  protected function action(): Response
  {
    $uid = $this->request->getAttribute('oauth_user_id');
    switch ($this->request->getMethod()) {
      case 'POST':
        $uploadedFiles = $this->request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];

        $post = $this->request->getParsedBody();
        $data = [
          'name' => $uploadedFile->getClientFilename(),
          'type' => $post['type'] ?? $uploadedFile->getClientMediaType(),
          'md5' => $post['md5'] ?? md5_file($uploadedFile->getFilePath()),
          'size' => $post['size'] ?? $uploadedFile->getSize(),
          'extension' => strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION)),
          'uid' => $uid ?: 0,
          'uptime' => time()
        ];

        //文件已上传过
        $file = $this->files->get('id,url', ['md5' => $data['md5']]);
        $uri = $this->request->getUri();
        $host = $uri->getScheme() . '://' . $uri->getHost();
        if (isset($file['id'])) {
          $file['host'] = $host;
          return $this->respondWithData($file);
        }

        if (!in_array($data['extension'], array('jpg', 'jpeg', 'gif', 'png', 'mp4', 'txt'))) {
          return $this->respondWithError('文件类型错误！');
        }

        if (!in_array($data['type'], ['image/gif', 'image/jpg', 'image/png', 'image/jpeg', 'video/mp4', 'text/plain'])) {
          return $this->respondWithError('文件类型错误！');
        }

        $content = file_get_contents($uploadedFile->getFilePath());
        if (preg_match('/<\?php/i', $content)) {
          return $this->respondWithError('文件类型错误！');
        }

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
          $type = explode('/', $data['type']);
          $filepath = '/' . $type[0] . date('/Ym/');
          if (!is_dir($this->filepath . $filepath)) mkdir($this->filepath . $filepath, 0755, true);

          //大文件分块上传
          if (isset($post['chunks']) && $post['chunks'] > 0) {
            $tmpPath = $this->filepath . '/tmp/' . $data['md5'];//上传文件临时目录

            if ($post['current_chunk'] == 1 && is_dir($tmpPath)) { //断点续传
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

              if ($current_chunk >= $post['current_chunk'] && $current_chunk < $post['chunks']) {//继续上传
                $post['current_chunk'] = $current_chunk;
                return $this->respondWithData(['current_chunk' => $post['current_chunk'], 'msg' => '继续上传第' . ($post['current_chunk'] + 1) . '块文件！'], 202);
              }
            }

            //创建临时目录，上传文件块
            if (!is_dir($tmpPath)) mkdir($tmpPath, 0755, true);
            $this->moveUploadedFile($tmpPath, $uploadedFile, $post['current_chunk'] . '.dat');

            if ($post['current_chunk'] == $post['chunks']) {//最后一块,合成大文件
              $extension = strtolower(pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION));
              $basename = bin2hex(random_bytes(8));
              $filename = sprintf('%s.%0.8s', $basename, $extension);
              $video_path = $filepath . DIRECTORY_SEPARATOR . $filename;
              $fp = fopen($this->filepath . $video_path, "wb");
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

              $data['url'] = $filepath . $filename;
            } else {
              return $this->respondWithData(['current_chunk' => $post['current_chunk'], 'msg' => '第' . $post['current_chunk'] . '块文件上传成功！'], 202);
            }
          } else {
            $filename = $this->moveUploadedFile($this->filepath . $filepath, $uploadedFile);
            $data['url'] = $filepath . $filename;
          }
        }
        $id = $this->files->insert($data);

        return $this->respondWithData(['id' => $id, 'type' => $data['type'], 'host' => $host, 'url' => $data['url']], 201);
      case 'PATCH':
        $data = $this->request->getParsedBody();
        $id = (int)$this->args['id'];
        if ($id > 0) {
          $num = $this->files->update($data, ['id' => $id]);
          return $this->respondWithData(['up_num' => $num], 201);
        } else {
          return $this->respondWithError('缺少ID', 422);
        }
      case 'DELETE':
        $id = (int)($this->args['id'] ?? 0);
        if ($id > 0) {
          $filepath = $this->files->get('url', ['id' => $id]);
          $num = $this->files->delete(['id' => $id]);
          if ($num) unlink($this->filepath . $filepath); //删除文件
          return $this->respondWithData(['del_num' => $num]);
        } else {
          return $this->respondWithError('缺少ID');
        }
      default:
        $id = $this->args['id'] ?? 0;
        if ($id > 0) {
          $file = $this->files->get('*', ['id' => $id]);
          return $this->respondWithData($file);
        }
        $get = $this->request->getQueryParams();
        if (!empty($get['keyword'])) {
          $keyword = trim($get['keyword']);
          $where['name[~]'] = $keyword;
        }
        $files = $this->files->select('id,cover,name,ctime', $where ?? []);
        //格式化数据
        $data = [];
        foreach ($files as $file) {
          $file['ctime'] = date('Y-m-d H:i:s', $file['ctime']);
          $data[] = $file;
        }
        return $this->respondWithData($data);
    }
  }

  /**
   * @param string $directory
   * @param UploadedFileInterface $uploadedFile
   * @param string $filename
   * @return string
   * @throws Exception
   */
  private function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile, string $filename = ''): string
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
}
