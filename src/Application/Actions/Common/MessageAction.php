<?php

namespace App\Application\Actions\Common;

use App\Domain\Common\MessageInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

/**
 * Class MessageAction
 * @title 内部通知消息
 * @route /admin/message
 * @package App\Application\Actions\Common
 */
class MessageAction extends \App\Application\Actions\Action
{
  private MessageInterface $message;

  public function __construct(LoggerInterface $logger, MessageInterface $message)
  {
    parent::__construct($logger);
    $this->message = $message;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    switch ($this->request->getMethod()) {
      case  'POST';
        $data = $this->request->getParsedBody();
        $data['id'] = $this->message->insert($data);
        return $this->respondWithData($data, 201);
      case  'PUT';
        $data = $this->request->getParsedBody();
        $num = $this->message->update($data, ['id' => $this->args['id']]);
        return $this->respondWithData(['upNum' => $num], 201);
      case  'DELETE';
        $delNum = $this->message->delete(['id' => $this->args['id']]);
        return $this->respondWithData(['del_num' => $delNum], 200);
      case 'GET';
        if ($this->request->getHeaderLine("X-Requested-With") == "XMLHttpRequest") {
          $where = [];
          $params = $this->request->getQueryParams();
          if (!empty($params['search']['value'])) {
            $keyword = trim($params['search']['value']);
            $where['content[~]'] = $keyword;
          }

          $where['LIMIT'] = [$params['start'], $params['length']];
          if (isset($params['order'])) foreach ($params['order'] as $param) {
            $where['ORDER'][$params['columns'][$param['column']]['data']] = strtoupper($param['dir']);
          }

          $category = $this->message->select('*', $where);

          unset($where['LIMIT']);
          unset($where['ORDER']);
          $data = [
            "draw" => $params['draw'],
            "recordsTotal" => $this->message->count('id'),
            "recordsFiltered" => $this->message->count('id', $where),
            'data' => $category
          ];
          $json = json_encode($data, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
          $this->response->getBody()->write($json);
          return $this->respond(200);
        } else {
          $data = [
            'title' => '内部通知消息'
          ];

          return $this->respondView('admin/common/message.html', $data);
        }
      default:
        return $this->respondWithError('禁止访问', 403);
    }
  }
}