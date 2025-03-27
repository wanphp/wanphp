<?php

namespace App\Application\Common\Message;
/**
 * 发送通知信息
 */
interface MessageInterface
{
  // 发送信息
  public function send(array $sendUser): array;

  /**
   * 用户绑定
   * @param array $data
   * @return MessageInterface
   */
  public function userBind(array $data): MessageInterface;

  /**
   * 用户解绑
   * @param array $data
   * @return MessageInterface
   */
  public function userUnBind(array $data): MessageInterface;

  /**
   * 用户登录通知
   * @param array $data
   * @return MessageInterface
   */
  public function login(array $data): MessageInterface;

  /**
   * 用户修改密码
   * @param array $data
   * @return MessageInterface
   */
  public function editPassword(array $data): MessageInterface;
}