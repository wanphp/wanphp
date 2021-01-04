<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/11/5
 * Time: 9:44
 */

namespace App\Infrastructure\Weixin;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

trait HttpTrait
{
  /**
   * @param Client $client
   * @param string $method
   * @param string $uri
   * @param array $options
   * @return array|mixed
   * @throws \Exception
   */
  private function request(Client $client, string $method, $uri = '', array $options = [])
  {
    try {
      $resp = $client->request($method, $uri, $options);

      if ($resp->getStatusCode() == 200) {
        $json = json_decode($resp->getBody()->getContents(), true);
        if (json_last_error() === JSON_ERROR_NONE) {
          if (isset($json['errcode']) && $json['errcode'] != 0) {
            throw new \Exception($json['errcode'] . ' - ' . $json['errmsg'], 400);
          } else {
            return $json;
          }
        } else {
          return ['content_type' => $resp->getHeaderLine('Content-Type'), 'body' => $resp->getBody()->getContents()];
        }
      } else {
        throw new \Exception($resp->getReasonPhrase(), $resp->getStatusCode());
      }
    } catch (RequestException $e) {
      $message = $e->getMessage();
      if ($e->hasResponse()) {
        $message .= "\n" . $e->getResponse()->getStatusCode() . ' ' . $e->getResponse()->getReasonPhrase();
        $message .= "\n" . $e->getResponse()->getBody();
      }
      throw new \Exception($message);
    } catch (GuzzleException $e) {
      throw new \Exception($e->getMessage(), $e->getCode());
    }
  }
}
