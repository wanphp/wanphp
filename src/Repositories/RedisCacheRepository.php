<?php

namespace App\Repositories;

use Exception;
use Predis\ClientInterface;
use Wanphp\Libray\Slim\Setting;

class RedisCacheRepository implements \Wanphp\Libray\Slim\CacheInterface
{
  private ClientInterface $client;
  private string $prefix;

  public function __construct(ClientInterface $client, Setting $setting)
  {
    $this->client = $client;
    $this->client->select($setting->get('redisCache') ?? 1);
    $this->prefix = $setting->get('redis')['options']['prefix'] ?? '';
  }

  /**
   * @inheritDoc
   */
  public function get(string $key, mixed $default = null): mixed
  {
    $value = $this->client->get($key);
    if ($value) {
      $json = json_decode($value, true);
      if (json_last_error() === JSON_ERROR_NONE) return $json;
      return $value;
    } else {
      return $default;
    }
  }

  /**
   * @inheritDoc
   */
  public function set(string $key, mixed $value, ?int $ttl = null): bool
  {
    if (!is_string($value)) $value = json_encode($value);
    if ($ttl > 0) $status = $this->client->setex($key, $ttl, $value);
    else $status = $this->client->set($key, $value);
    return $status->getPayload() === 'OK';
  }

  /**
   * @inheritDoc
   */
  public function delete(string $key): bool
  {
    return $this->client->del($key) > 0;
  }

  /**
   * @inheritDoc
   */
  public function clear(): bool
  {
    $keys = $this->client->keys('*');
    $count = 0;
    if ($keys) {
      $keys = str_replace($this->prefix, '', $keys);
      $count = $this->client->del($keys);
    }
    return $count > 0;
  }

  /**
   * @inheritDoc
   * @throws Exception
   */
  public function getMultiple(array $keys, mixed $default = null): iterable
  {
    $data = [];
    foreach ($keys as $key) {
      $data[$key] = $this->get($key);
    }
    if (!empty($data)) {
      return $data;
    } else {
      return $default;
    }
  }

  /**
   * @inheritDoc
   */
  public function setMultiple(array $values, ?int $ttl = null): bool
  {
    $num = 0;
    foreach ($values as $key => $value) {
      if (!is_string($value)) $value = json_encode($value);
      if ($ttl > 0) $status = $this->client->setex($key, $ttl, $value);
      else $status = $this->client->set($key, $value);
      if ($status->getPayload() === 'OK') $num++;
    }
    return count($values) === $num;
  }

  /**
   * @inheritDoc
   */
  public function deleteMultiple(iterable $keys): bool
  {
    $count = 0;
    if ($keys) $count = $this->client->del($keys);
    return $count > 0;
  }
}
