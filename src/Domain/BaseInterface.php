<?php
/**
 * Created by PhpStorm.
 * User: 火子 QQ：284503866.
 * Date: 2020/8/27
 * Time: 9:46
 */

namespace App\Domain;


use App\Domain\DomainException\MedooException;

interface BaseInterface
{
  /**
   * 插入自定义数据
   * @param array $data
   * @return int 最全插入ID
   * @throws MedooException
   */
  public function insert(array $data): int;

  /**
   * 更新指定数据
   * @param array $data
   * @param array $where
   * @return int 更新数量
   * @throws MedooException
   */
  public function update(array $data, array $where): int;

  /**
   * 自定义查询
   * @param string $columns
   * @param array $where
   * @return array
   * @throws MedooException
   */
  public function select(string $columns = '*', array $where = null): array;

  /**
   * 获取一条数据
   * @param string $columns
   * @param array $where
   * @return mixed
   * @throws MedooException
   */
  public function get(string $columns = '*', array $where = null);

  /**
   * @param string $columns
   * @param array|null $where
   * @return int
   */
  public function count(string $columns = '*', array $where = null):int;

  /**
   * 自定义删除
   * @param array $where
   * @return int 删除数量
   * @throws MedooException
   */
  public function delete(array $where): int;

  /**
   * 返回日志
   * @throws MedooException
   */
  public function log();
}
