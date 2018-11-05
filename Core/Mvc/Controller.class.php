<?php

namespace Core\Mvc;

use Core\Mvc\View;

class Controller
{
  protected $_controller;
  protected $_action;
  protected $_view;

  function __construct($controller, $action)
  {
    $this->_controller = $controller;
    $this->_action = $action;
    $this->_view = new View($controller, $action);
    var_dump($this->_controller);
  }

  function assign($k, $v)
  {
    $this->_view->assign($k, $v);
  }

  function render()
  {
    $this->_view->render();
  }

  function redirect($path)
  {
    header("location: {$path}");
  }

  function _empty($name)
  {
    http_response_code(400);
    exit('调用了空方法: ' . $name);
  }

  // api 方法
  function allowMethods(array $allowMethods, $msg = null)
  {
    $allowMethods = array_map(function ($method) {
      return strtoupper($method);
    }, $allowMethods);
    if (!in_array($_SERVER['REQUEST_METHOD'], $allowMethods)) {
      header('Content-Type: Application/json; charset=utf-8');
      http_response_code(405);
      exit(json_encode([
        'message' => 'FAIL',
        'error' => $msg ? $msg : 'Method Not Allowed'
      ]));
    }
    return $this;
  }

  /**
   * @param $code status code
   * @param $flag true|false message
   * @param null $data 数据
   * @return string 返回值
   */
  function resjson($code, $flag, $data = null)
  {
    $res = [];
    $res = $flag ? ['data' => $data] : ['error' => $data];
    $flag = $flag ? 'SUCCESS' : 'FAIL';
    header('Content-Type: Application/json; charset=utf-8');
    http_response_code($code);
    return json_encode(array_merge(
      ['message' => $flag],
      $res
    ));
  }
}