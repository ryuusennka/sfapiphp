<?php

namespace Core\Mvc;

//use Core\Libs\PDODriver;
use \PDO;
use \PDOStatement;
use \PDOException;

class Model
{
  private $pdo = null;
  private $statement = null;
  private $tableName = '';
  private $sql = [];
  private $sqlBinds = [];
  private $sqlBug = '';
  private $fields = '*';
  private $columns = [];
  private $lastInsertId = 0;

  function __construct()
  {
    $table = explode('\\', get_class($this));
    $table = str_replace('Model', '', array_pop($table));
    $dsn = sprintf('mysql:host=%s;dbname=%s;', DB_HOST, DB_NAME);
    $opt = array(
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_PERSISTENT => true,                           // 请求一个持久连接，而非创建一个新连接。
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,            // 如果发生错误，则抛出一个 PDOException 异常
//      PDO::ATTR_CASE => PDO::CASE_NATURAL,                    // 保留数据库驱动返回的列名
//      PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,            // 获取数据时将空字符串转换成 SQL 中的 NULL
//      PDO::ATTR_STRINGIFY_FETCHES => FALSE,                   // 禁止将数值转换为字符串
//      PDO::ATTR_EMULATE_PREPARES => FALSE,                    // 禁用预处理语句的模拟
    );
    $this->pdo = new PDO($dsn, DB_USER, DB_PWD, $opt);
    $this->tableName = $this->tableFormat($table);
    $this->fetchColumns();
  }

  /**
   * @param $name 表名 eg: UserCategoryCopy -->  user_category_copy 数据库表名小写
   * @return string 表名
   */
  function tableFormat($name)
  {
    $temp_array = array();
    for ($i = 0; $i < strlen($name); $i++) {
      $ascii_code = ord($name[$i]);
      if ($ascii_code >= 65 && $ascii_code <= 90) {
        if ($i == 0) {
          $temp_array[] = chr($ascii_code + 32);
        } else {
          $temp_array[] = '_' . chr($ascii_code + 32);
        }
      } else {
        $temp_array[] = $name[$i];
      }
    }
    return implode('', $temp_array);
  }

  function fetchColumns()
  {
    $this->statement = $this->pdo->prepare(sprintf('DESC `%s`', $this->tableName));
    $this->statement->execute();
    $this->columns = $this->statement->fetchAll(PDO::FETCH_COLUMN);
    return $this->columns;
  }

  // 查询相关
  function query()
  {
    $args = func_get_args();
    array_push($this->sql, array_shift($args));
    call_user_func_array(array($this, 'bindQuery'), $args);
    return $this;
  }

  function bindQuery()
  {
    $args = func_get_args();
    foreach ($args as $item) {
      array_push($this->sqlBinds, $item);
    }
  }

  function field()
  {
    $args = func_get_args();
    $this->fields = implode(' ', $args);
    return $this;
  }

  // 返回所有匹配的额数据
  function getList()
  {
    $filter = implode(' ', $this->sql);
    $sql = sprintf('SELECT %s FROM `%s` %s', $this->fields, $this->tableName, $filter);
    $this->sqlBug = $sql;
    $this->statement = $this->pdo->prepare($sql);
    if ($this->statement && $this->statement->execute($this->sqlBinds)) {
      return $this->statement->fetchAll();
    } else {
      trigger_error(json_encode($this->statement->errorInfo()));
    }
  }

  // 得到匹配的第一条数据,或者根据 id 获取
  function get()
  {
    $result = $this->getList();
    if ($result) return $result[0];
    return array();
  }

  // 限制数据返回
  function page($currentPage, $preSize)
  {
    $preSize = $preSize * 1;
    $page = $preSize * ($currentPage - 1);
    $this->query('LIMIT ' . $page . ', ' . $preSize);
    return $this;
  }

  function pageList($currentPage = 1, $preSize = 10, $sort = ['id', 'asc'])
  {
    $preSize = $preSize * 1;
    $page = $preSize * ($currentPage - 1);
    $this->query(sprintf('ORDER BY %s %s', $sort[0], $sort[1]));
    $this->page($currentPage, $preSize);
    return $this;
  }

  // 写入相关
  function add($data)
  {
    $sql = sprintf('INSERT `%s` %s', $this->tableName, $this->formatInsert($data));
    $this->sqlBug = $sql;
    $this->statement = $this->pdo->prepare($sql);
    if ($this->statement && $this->statement->execute($this->sqlBinds)) {
      $this->lastInsertId = $this->pdo->lastInsertId();
      return $this->lastInsertId;
//      return $this->statement->rowCount();
    } else {
      trigger_error(json_encode($this->statement->errorInfo()));
    }
  }

  function formatInsert($data)
  {
    $fields = array();
    $values = array();
    foreach ($data as $key => $value) {
      $fields[] = sprintf("`%s`", $key);
      $values[] = sprintf("'%s'", $value);
    }

    if (in_array('create_time', $this->columns)) {
      $fields[] = sprintf("`%s`", 'create_time');
      $values[] = sprintf("'%s'", currentDate());
    }
    if (in_array('update_time', $this->columns)) {
      $fields[] = sprintf("`%s`", 'update_time');
      $values[] = sprintf("'%s'", currentDate());
    }
    if (in_array('uuid', $this->columns)) {
      $fields[] = sprintf("`%s`", 'uuid');
      $values[] = sprintf("'%s'", createUuid());
    }

    $fields = implode(', ', $fields);
    $values = implode(', ', $values);

    return sprintf("(%s) VALUES(%s)", $fields, $values);
  }

  function update($data)
  {
    $filter = implode(' ', $this->sql);
    $sql = sprintf('UPDATE `%s` SET %s %s', $this->tableName, $this->formatUpdate($data), $filter);
    $this->sqlBug = $sql;
    $this->statement = $this->pdo->prepare($sql);
    if ($this->statement && $this->statement->execute($this->sqlBinds)) {
      return $this->statement->rowCount();
    } else {
      trigger_error(json_encode($this->statement->errorInfo()));
    }
  }

  function formatUpdate($data)
  {
    $fields = [];
    foreach ($data as $key => $val) {
      $fields[] = sprintf("`%s` = '%s'", $key, $val);
    }
    if (in_array('update_time', $this->columns)) {
      $fields[] = sprintf("`%s` = '%s'", 'update_time', currentDate());
    }
    return implode(', ', $fields);
  }

  function delete($id = null)
  {
    // delete from `imooc`.`user` where `id`='15'
    if ($id) {
      $sql = sprintf("DELETE FROM `%s` WHERE `id` = '%s'", $this->tableName, $id);
    } else {
      $filter = implode(' ', $this->sql);
      $sql = sprintf("DELETE FROM `%s` %s", $this->tableName, $filter);
    }
    $this->sqlBug = $sql;
    $this->statement = $this->pdo->prepare($sql);
    if ($this->statement && $this->statement->execute($this->sqlBinds)) {
      return $this->statement->rowCount();
    } else {
      trigger_error(json_encode($this->statement->errorInfo()));
    }
  }

  function clearQuery()
  {
    $this->sql = [];
    $this->sqlBinds = [];
    return $this;
  }

  function begin()
  {
    $this->pdo->beginTransaction();
    return $this;
  }

  function commit()
  {
//    eg:
//    try {
//      $model->begin();
//      $res = $model->add();
//        if (!$res) {
//          new \PDOException('创建表单失败');
//        }
//     语句1 True 下一个语句 False 回滚
//     语句2 True 下一个语句 False 回滚
//     语句3 True 下一个语句 False 回滚
//      只要有错误就回滚
//    } catch (\PDOException $e) {
//      $model->rollback();
//    }
    $this->pdo->commit();
    return $this;
  }

  function rollback()
  {
    $this->pdo->rollBack();
    return $this;
  }

  function getLastInsertId()
  {
    return $this->lastInsertId;
  }
}
