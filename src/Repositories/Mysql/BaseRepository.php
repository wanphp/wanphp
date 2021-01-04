<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/29
 * Time: 16:19
 */

namespace App\Repositories\Mysql;

use App\Infrastructure\Database\Database;
use App\Domain\BaseInterface as Repository;
use App\Domain\DomainException\MedooException;

abstract class BaseRepository implements Repository
{
  protected $db;
  protected $tableName;
  private $entityClass;

  public function __construct(Database $database, $tableName, $entityClass)
  {
    $this->db = $database;
    $this->tableName = $tableName;
    $this->entityClass = $entityClass;
  }

  /**
   * {@inheritdoc}
   */
  public function insert(array $datas): int
  {
    $required = [];//必须项
    try {
      $class = new \ReflectionClass($this->entityClass); //建立实体类的反射类
      $docblock = $class->getDocComment();
      if (preg_match('/required=\{(.*?)\}/', $docblock, $primary)) {
        $required = explode(',', str_replace(['"', '\''], '', $primary[1]));
      }
    } catch (\ReflectionException $exception) {
      throw new MedooException($exception->getMessage(), $exception->getCode());
    }

    if (!isset($datas[0])) $datas = [$datas];
    foreach ($datas as &$data) {
      $data = array_filter((new $this->entityClass($data))->jsonSerialize(), function ($value, $key) use ($required) {
        if (in_array($key, $required) && ($value == '' || is_null($value))) {
          throw new MedooException($key . ' - 不能为空');
        }
        return !is_null($value);
      }, ARRAY_FILTER_USE_BOTH);
    }

    $this->db->insert($this->tableName, $datas);
    return $this->returnResult($this->db->id() ?? 0);
  }

  /**
   * {@inheritdoc}
   */
  public function update(array $data, array $where): int
  {
    $data = array_filter((new $this->entityClass($data))->jsonSerialize(), function ($value) {
      return !is_null($value);
    });
    $counts = $this->db->update($this->tableName, $data, $where)->rowCount();
    return $this->returnResult($counts);
  }

  /**
   * {@inheritdoc}
   */
  public function select(string $columns = '*', array $where = null): array
  {
    if ($columns != '*') $columns = explode(',', $columns);
    $datas = $this->db->select($this->tableName, $columns, $where);
    return $this->returnResult($datas);
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $columns = '*', array $where = null)
  {
    if ($columns != '*' && strpos($columns, ',') > 0) $columns = explode(',', $columns);
    $data = $this->db->get($this->tableName, $columns, $where);
    return $this->returnResult($data);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $where): int
  {
    $counts = $this->db->delete($this->tableName, $where)->rowCount();
    return $this->returnResult($counts);
  }

  /**
   * {@inheritdoc}
   */
  public function log()
  {
    $logs = implode(PHP_EOL, $this->db->log());
    throw new MedooException($logs ?: '无数据库操作！');
  }

  /**
   * @param $result
   * @return mixed
   * @throws MedooException
   */
  private function returnResult($result)
  {
    $error = $this->db->error();
    if (in_array($error[1], [1146, 1054, 1062])) {//数据表不存在，或字段不存在，主键冲突,创建或更新表
      $this->db->initTable($this->tableName, $this->entityClass);
    }
    if (is_null($error[1])) return $result;
    else throw new MedooException($error[1] . ' - ' . $this->tableName . ' ' . $error[2], $error[1]);
  }
}
