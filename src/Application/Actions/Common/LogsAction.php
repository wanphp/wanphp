<?php

namespace App\Application\Actions\Common;

use App\Domain\Admin\AdminInterface;
use App\Domain\Common\LogsInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Class LogsAction
 * @title 系统操作日志
 * @route /admin/logs
 * @package App\Application\Actions\Common
 */
class LogsAction extends \App\Application\Actions\Action
{
  private LogsInterface $logs;
  private AdminInterface $admin;

  public function __construct(LoggerInterface $logger, LogsInterface $logs, AdminInterface $admin)
  {
    parent::__construct($logger);
    $this->logs = $logs;
    $this->admin = $admin;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
      $params = $this->request->getQueryParams();
      $where = [];

      $recordsTotal = $this->logs->count('log_id', $where);
      if (!empty($params['search']['value'])) {
        $keyword = trim($params['search']['value']);
        $where['log_content[~]'] = addcslashes($keyword, '*%_');
      } else {
        if (isset($params['date'])) $where['ctime[>]'] = strtotime($params['date']);
        else $where['ctime[>]'] = strtotime("-6 day");
      }

      $order = $this->getOrder();
      if ($order) $where['ORDER'] = $order;
      $recordsFiltered = $this->logs->count('log_id', $where);
      $limit = $this->getLimit();
      if ($limit) $where['LIMIT'] = $limit;

      return $this->respondWithData([
        "draw" => $params['draw'],
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $recordsFiltered,
        'data' => $this->logs->select('admin_id,log_content,ctime', $where)
      ]);
    } else {
      $admin = $this->admin->select('id,name', ['status' => 1]);
      $adminArr = array_column($admin, 'name', 'id');
      $data = [
        'title' => '系统操作日志(默认显示7天内)',
        'admin' => json_encode($adminArr)
      ];

      return $this->respondView('admin/common/logs.html', $data);
    }
  }
}
