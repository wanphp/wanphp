<?php

namespace App\Repositories;

use App\Entities\CacheEntity;
use Exception;
use Wanphp\Libray\Mysql\Database;

class MysqlCacheRepository implements \Wanphp\Libray\Slim\CacheInterface
{
  private Database $database;
  private string $tableName = 'cache';

  public function __construct(Database $database)
  {
    $this->database = $database;
  }

  /**
   * @inheritDoc
   */
  public function get(string $key, mixed $default = null): mixed
  {
    $value = $this->database->get($this->tableName, ['value', 'expires_at'], ['key' => $key]);
    if ($value) {
      if ($value['expires_at'] > 0 && $value['expires_at'] < time()) {
        $this->database->delete($this->tableName, ['key' => $key]);
        return null;
      } else {
        $json = json_decode($value['value'], true);
        if (json_last_error() === JSON_ERROR_NONE) return $this->returnResult($json);
        return $this->returnResult($value['value']);
      }
    } else {
      return $default;
    }
  }

  /**
   * @inheritDoc
   */
  public function set(string $key, mixed $value, ?int $ttl = null): bool
  {
    $data = [
      'key' => $key,
      'value' => $value,
      'expires_at' => $ttl > 0 ? time() + $ttl : 0
    ];
    if (!is_string($value)) $data['value'] = json_encode($value);
    $this->database->insert($this->tableName, $data);
    return $this->returnResult($this->database->id() > 0);
  }

  /**
   * @inheritDoc
   */
  public function delete(string $key): bool
  {
    $res = $this->database->delete($this->tableName, ['key' => $key]);
    if ($res) return $this->returnResult($res->rowCount() > 0);
    else return $this->returnResult(false);
  }

  /**
   * @inheritDoc
   */
  public function clear(): bool
  {
    $count = $this->database->delete($this->tableName, ['key[!]' => '']);
    return $count > 0;
  }

  /**
   * @inheritDoc
   * @throws Exception
   */
  public function getMultiple(array $keys, mixed $default = null): iterable
  {
    $data = [];
    foreach ($this->database->select($this->tableName, ['key', 'value', 'expires_at'], ['key' => $keys]) as $item) {
      if ($item['expires_at'] > 0 && $item['expires_at'] < time()) {
        $this->database->delete($this->tableName, ['key' => $item['key']]); //删除过期的
      } else {
        $json = json_decode($item['value'], true);
        if (json_last_error() === JSON_ERROR_NONE) $data[$item['key']] = $json;
        $data[$item['key']] = $item['value'];
      }
    }
    if (!empty($data)) return $data;
    else return $default;
  }

  /**
   * @inheritDoc
   */
  public function setMultiple(array $values, ?int $ttl = null): bool
  {
    $data = [];
    foreach ($values as $key => $value) {
      if (!is_string($value)) $value = json_encode($value);
      $data[] = [
        'key' => $key,
        'value' => $value,
        'expires_at' => $ttl > 0 ? time() + $ttl : 0
      ];
    }
    $this->database->insert($this->tableName, $data);
    return $this->returnResult($this->database->id() > 0);
  }

  /**
   * @inheritDoc
   */
  public function deleteMultiple(iterable $keys): bool
  {
    if ($keys) {
      $res = $this->database->delete($this->tableName, ['key' => $keys]);
      if ($res) return $this->returnResult($res->rowCount() > 0);
      else return $this->returnResult(false);
    } else {
      return false;
    }
  }

  /**
   * @param $result
   * @throws Exception
   */
  private function returnResult($result)
  {
    $error = $this->database->errorInfo;
    if (is_null($error)) return $result;
    //数据表不存在，或字段不存在，主键冲突,创建或更新表
    if (is_array($error) && in_array($error[1], [1146, 1054, 1062])) {
      $this->database->initTable($this->tableName, CacheEntity::class);
    }

    throw new Exception($error[2], $error[1]);
  }
}
