<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/12/11
 * Time: 10:23
 */

namespace App\Application\Actions\Common;


use App\Application\Actions\Action;
use App\Domain\Common\SettingInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Class SettingAction
 * @title 自定义系统配置
 * @route /admin/setting
 * @package App\Application\Actions\Common
 */
class SettingAction extends Action
{
  private SettingInterface $setting;

  public function __construct(LoggerInterface $logger, SettingInterface $setting)
  {
    parent::__construct($logger);
    $this->setting = $setting;
  }

  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case 'POST':
        $data = $this->request->getParsedBody();
        $id = $this->setting->insert($data);
        return $this->respondWithData(['id' => $id]);
      case 'PUT':
        $data = $this->request->getParsedBody();
        $id = (int)$this->args['id'];
        if ($id > 0) {
          $num = $this->setting->update($data, ['id' => $id]);
          return $this->respondWithData(['upNum' => $num]);
        } else {
          return $this->respondWithError('缺少ID');
        }
      case 'DELETE':
        $id = (int)($this->args['id'] ?? 0);
        if ($id > 0) {
          $num = $this->setting->delete(['id' => $id]);
          return $this->respondWithData(['delNum' => $num]);
        } else {
          return $this->respondWithError('缺少ID');
        }
      default:
        if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          $id = $this->args['id'] ?? 0;
          if ($id > 0) return $this->respondWithData($this->setting->get('*', ['id' => $id]));
          return $this->respondWithData(['data' => $this->setting->select()]);
        } else {
          $data = [
            'title' => '自定义系统配置'
          ];

          return $this->respondView('admin/common/setting.html', $data);
        }
    }
  }
}
