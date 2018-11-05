<?php

namespace Core;

class Core
{
  static public function init()
  {
    header("X-Powered-By: sfapiphp"); // 这是我为自己的 php 框架定义的名字
    session_start();
    spl_autoload_register('self::autoLoadClass');
    self::setReporting();
    self::loadFile();
    self::route();
  }

  static public function autoLoadClass($className)
  {
//    var_dump($className);
//    顶级命名空间路径映射
    $map = [
      'App' => APP_PATH,
      'Core' => CORE_PATH,
    ];
//    解析类名为文件系统
    $vendor = substr($className, 0, strpos($className, '\\')); // 取出顶级命名空间[App, Core] // App
    $vendor_dir = $map[$vendor]; // 文件基目录 // /var/www/html/App
    $file = str_replace('\\', '/', $className); // App/Controller/IndexController
    $file = $vendor_dir . str_replace($vendor, '', $file) . '.class.php';
    if (file_exists($file)) require_once $file;
  }


  static public function route()
  {
    $url = $_SERVER['REQUEST_URI'];
    define('APP_HTTP_HOST', $_SERVER['HTTP_HOST']);
    define('APP_SERVER_PORT', $_SERVER['SERVER_PORT']);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
      define('APP_PROTOCOL', 'https://');
    } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
      define('APP_PROTOCOL', 'https://');
    } else {
      define('APP_PROTOCOL', 'http://');
    }
// $query = $_SERVER['QUERY_STRING']; url 重写模式下没有这个
    $position = strpos($url, '?');
    $query = '';
    if ($position !== false) {
      $query = substr($_SERVER['REQUEST_URI'], $position + 1); // 取 ? 之后的
      parse_str($query, $output);
      $_GET = (array)$output;
      $_SERVER['QUERY_STRING'] = $query;
    }
    $url = $position === false ? $url : substr($url, 0, $position); // 取问号之前的
    $url = trim($url);
    $urlArr = array_filter(explode('/', $url));
    $controllerName = $action = '';

    // 处理控制器,action,参数
    $controllerName = ucfirst(array_shift($urlArr));
    $action = array_shift($urlArr);
    $param = $urlArr;

    $controller = 'App\\Controller\\' . $controllerName . 'Controller';
    if (!class_exists($controller)) {
      exit('没有找到控制器: ' . $controllerName);
    } else {
      $dispatch = new $controller($controllerName, $action);
    }

    if (method_exists($controller, $action)) {
      call_user_func_array(array($dispatch, $action), $param);
    } else {
      // 调用了空方法
      call_user_func_array(array($dispatch, '_empty'), [$action]);
    }
  }

  static public function setReporting()
  {
    if (APP_DEBUG) {
      error_reporting(E_ALL);
      ini_set('display_errors', 'On');
    } else {
      error_reporting(E_ALL);
      ini_set('display_errors', 'Off');
      ini_set('log_errors', 'On');
      ini_set('error_log', ERRLOG_PATH . 'error.log');
    }
  }

  static public function loadFile()
  {
    // 引入配置文件
    require_once CORE_PATH . '/Config/config.php';
    require_once CORE_PATH . '/Libs/functions.php';
  }
}

Core::init();