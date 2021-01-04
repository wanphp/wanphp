<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/9/2
 * Time: 9:20
 */

namespace App\Infrastructure\Database;


class Redis
{
  private $redis = null;
  private $db;
  private $prefix;

  public function __construct($config)
  {
    $this->db = $config['database'];
    $this->prefix = $config['prefix'];
    $this->redis = new \Redis();
    $this->redis->connect($config['host'], $config['port']);
    $this->redis->auth($config['password']);
    $this->redis->select($this->db);
  }

  /**
   * @param int $db
   */
  public function select(int $db): void
  {
    $this->redis->select($db);
  }

  /**
   * @param mixed $prefix
   */
  public function setPrefix($prefix): void
  {
    $this->prefix = $prefix;
  }

  /**
   * @param string $key
   * @return mixed
   */
  public function get(string $key)
  {
    return json_decode($this->redis->get($this->prefix . $key), true);
  }

  /**
   * @param string $key
   * @param $value
   * @param int $expire
   */
  public function set(string $key, $value, $expire = 0)
  {
    if ($expire > 0) $this->redis->setex($this->prefix . $key, $expire, json_encode($value));
    else $this->redis->set($this->prefix . $key, json_encode($value));
  }

  /**
   * @param string $key
   */
  public function delete(string $key)
  {
    if (strpos($key, '*') !== false) {
      $this->redis->del($this->redis->keys($this->prefix . $key));
    } else {
      $this->redis->del($this->prefix . $key);
    }
  }

}
