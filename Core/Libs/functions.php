<?php
///**
// * @param null $configArr 用户新的配置项
// * @return array|mixed 配置项信息
// * @throws Exception 报错提示
// */
//function C($configArr = null)
//{
//  $config = require_once CORE_PATH . '/Config/config.php';
//  if (is_file(APP_PATH . '/Config/config.php')) {
//    $custom = require_once APP_PATH . '/Config/config.php';
//    $config = array_merge($config, $custom);
//  }
//  if ($configArr) {
//    if (is_array($configArr)) {
//      $config = array_merge($config, $configArr);
//    } else {
//      throw new Exception(__FUNCTION__ . ' 第一个参数为数组.');
//    }
//  }
//  return $config;
//}

/**
 * @param $model model类名,开头字母大写
 * @return mixed 放回 model 类
 */
function M($model)
{
  // 找类
  $modelName = ucfirst($model);
  $model = 'App\\Model\\' . $modelName . 'Model';
  // 找表名
  return new $model($modelName);
}

/**
 * @return false|string 返回当前时间
 */
function currentDate()
{
  return date('Y-m-d H:i:s');
}

/**
 * 获取传参
 * @param $name
 * @param string $default
 * @param null $filter
 * @return mixed
 */
function I($name, $default = '')
{
  $defaultMethod = $_SERVER['REQUEST_METHOD'];
  if (strpos($name, '.') !== false) {
    $namearr = explode('.', $name);
    $defaultMethod = strtoupper($namearr[0]);
    $name = $namearr[1];
  }
  if ($defaultMethod === 'GET') {
    if (isset($_GET[$name]) && $_GET[$name]) {
      return $_GET[$name];
    } else {
      return $default;
    }
  } else if ($defaultMethod === 'POST') {
    if (isset($_POST[$name]) && $_POST[$name]) {
      return $_POST[$name];
    } else {
      return $default;
    }
  } else if ($defaultMethod === 'PUT') {
    parse_str(file_get_contents('php://input'), $data);
//    var_dump($data);
//    var_dump(in_array($name, $data));
    if (array_key_exists($name, $data) && $data[$name]) {
      return $data[$name];
    } else {
      return $default;
    }
  } else {
    return null;
  }
}

/**
 * @param string $url
 * @return string 返回拼接好的 url 地址
 */
function U(string $url)
{
  $position = strpos($url, '?');
  $query = '';
  if ($position !== false) {
    $query = substr($url, $position); // 取 ? 之后的
  }
  $url = $position === false ? $url : substr($url, 0, $position); // 取问号之前的
  $url = trim($url);
  $urlArr = array_filter(explode('/', $url));
  $controller = $action = '';

  // 处理控制器,action,参数
  $controller = ucfirst(array_shift($urlArr));
  $action = array_shift($urlArr);
  $param = implode('/', $urlArr);
  if ($param) $param = '/' . $param;
  $delimiter = ':';
  return APP_PROTOCOL . APP_HTTP_HOST . $delimiter . APP_SERVER_PORT . '/' . $controller . '/' . $action . $param . $query;
}

function md5pwd($pwd)
{
  return md5(sha1(SALT . $pwd));
}

/**
 * 生成一个 uuid
 *
 * @return string 生成一个 uuid
 */
function createUuid()
{
  $str = md5(uniqid(mt_rand(), true));
  return substr($str, 0, 8)
    . '-' .
    substr($str, 8, 4)
    . '-' .
    substr($str, 12, 4)
    . '-' .
    substr($str, 16, 4)
    . '-' .
    substr($str, 20, 12);
}