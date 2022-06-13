<?php

namespace App\Application\Api\Common;

use App\Domain\Common\SettingInterface;
use Psr\Http\Message\ResponseInterface as Response;

class ConfigApi extends \App\Application\Api\Api
{
  private SettingInterface $setting;

  public function __construct(SettingInterface $setting)
  {
    $this->setting = $setting;
  }

  /**
   * @inheritDoc
   */
  protected function action(): Response
  {
    $userid = $this->getUid();
    if ($userid < 1) return $this->respondWithError('未知用户', 422);

    return $this->respondWithData(['value' => $this->setting->get('value', ['key' => $this->args['key']])]);
  }
}