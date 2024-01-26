<?php

namespace App\Application\Handlers;

use App\Domain\Common\LogsInterface;
use Monolog\Logger;

class LogHandler extends \Monolog\Handler\AbstractProcessingHandler
{
  private LogsInterface $logs;

  public function __construct(LogsInterface $logs, $level = Logger::DEBUG, bool $bubble = true)
  {
    parent::__construct($level, $bubble);
    $this->logs = $logs;
  }

  /**
   * @inheritDoc
   */
  protected function write(array $record): void
  {
    $data = ['admin_id' => $_SESSION['login_id'], 'log_content' => $record['message'], 'ctime' => time()];
    if (isset($record['context']['basic_id'])) $data['basic_id'] = $record['context']['basic_id'];
    $this->logs->insertLog($data);
  }
}
