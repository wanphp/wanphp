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
use Psr\Http\Message\ResponseInterface as Response;
use Wanphp\Libray\Slim\Setting;
use Wanphp\Libray\Slim\UploaderInterface;

class FilesApi extends Api
{
  private FilesInterface $files;
  private UploaderInterface $uploader;

  /**
   * @param FilesInterface $files
   * @param UploaderInterface $uploader
   */
  public function __construct(FilesInterface $files, UploaderInterface $uploader)
  {
    $this->files = $files;
    $this->uploader = $uploader;
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
   *         @OA\Property(property="datas",@OA\Property(property="upNum",type="integer"))
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

        // 文件已上传过
        $fileMd5 = $post['md5'] ?? md5_file($uploadedFile->getFilePath());
        $file = $this->files->get('id,type,url', ['md5' => $fileMd5]);
        $uri = $this->request->getUri();
        if (isset($file['id'])) {
          $file['host'] = $uri->getScheme() . '://' . $uri->getHost();
          return $this->respondWithData($file);
        }

        // 上传文件
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
          $formData = $this->getFormData();
          $formData['uid'] = $uid;
          $formData['host'] = $uri->getScheme() . '://' . $uri->getHost();
          return $this->respondWithData($this->uploader->uploadFile($formData, $uploadedFile));
        } else {
          return $this->respondWithError('文件上传失败');
        }
      case 'PATCH':
        $data = $this->request->getParsedBody();
        $id = intval($this->resolveArg('id'));
        if ($id > 0 && isset($data['name'])) {
          $num = $this->uploader->setName($id, $data['name']);
          return $this->respondWithData(['upNum' => $num], 201);
        } else {
          return $this->respondWithError('缺少参数', 422);
        }
      case 'DELETE':
        $id = intval($this->resolveArg('id'));
        if ($id > 0) {
          return $this->respondWithData(['delNum' => $this->uploader->delFile($id)]);
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
        $files = $this->files->select('id,url,name,type,size,uptime', $where ?? []);
        //格式化数据
        $data = [];
        foreach ($files as $file) {
          $file['ctime'] = date('Y-m-d H:i:s', $file['ctime']);
          $data[] = $file;
        }
        return $this->respondWithData($data);
    }
  }
}
