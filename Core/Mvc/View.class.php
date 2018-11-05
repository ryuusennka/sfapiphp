<?php

namespace Core\Mvc;
class View
{
  protected $variables = array();
  protected $_controller;
  protected $_action;

  function __construct($controller, $action)
  {
    $this->_controller = $controller;
    $this->_action = $action;
  }

  // 分配变量
  function assign($k, $v)
  {
    $this->variables[$k] = $v;
  }

  function render()
  {
    extract($this->variables);
    $controllerLayout = APP_PATH . '/View/' . $this->_controller . '/' . $this->_action . '.php';
    if (is_file($controllerLayout)) {
      require $controllerLayout;
    } else {
      exit('<h1>无法找到视图文件</h1>');
    }
  }
}