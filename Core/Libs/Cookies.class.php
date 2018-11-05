<?php

namespace Core\Libs;

class Cookies
{
  static private $_instance = null;
  private $expire = 0;
  private $path = '';
  private $domain = '';
  private $secure = false;
  private $httponly = false;

  /**
   * Cookies constructor. 构造函数完成 Cookie 初始化工作
   * @param array $options Cookie 相关选项
   */
  private function __construct(array $options = [])
  {
    $this->setOptions($options);
  }

  /**
   * 设置相关选项
   * @param array $options Cookie 相关选项
   * @return $this
   */
  private function setOptions(array $options = [])
  {
    if (isset($options['expire'])) $this->expire = (int)$options['expire'];
    if (isset($options['path'])) $this->path = $options['path'];
    if (isset($options['domain'])) $this->domain = $options['domain'];
    if (isset($options['secure'])) $this->secure = (bool)$options['secure'];
    if (isset($options['httponly'])) $this->httponly = (bool)$options['httponly'];
    return $this;
  }

  /**
   * 单例模式
   * @param array $options Cookie 相关选项
   * @return Object 对象实例
   */
  static public function getInstance(array $options = [])
  {
    if (is_null(self::$_instance)) {
      $class = __CLASS__;
      self::$_instance = new $class($options);
    }
    return self::$_instance;
  }

  /**
   * 设置 cookie
   * @param $name
   * @param $value 如果value是对象会被自动转json
   * @param array $options Cookie 相关选项
   */
  function set($name, $value, array $options = [])
  {
    if ($options) $this->setOptions($options);
    if (is_array($value) || is_object($value)) {
      $value = json_encode($value);
    }
    setCookie(
      $name,
      $value,
      $this->expire,
      $this->path,
      $this->domain,
      $this->secure,
      $this->httponly
    );
  }

  /**
   * 获取 cookie
   * @param $name
   * @return mixed|null
   */
  function get(string $name)
  {
    if (isset($_COOKIE[$name])) {
      return
        (substr($_COOKIE[$name], 0, 1) === '['
          ||
          substr($_COOKIE[$name], 0, 1) === '{')
          ?
          json_decode($_COOKIE[$name])
          :
          $_COOKIE[$name];
    } else {
      return null;
    }
  }

  /**
   * 删除指定cookie
   * @param string $name
   * @param array $options
   */
  function del(string $name, array $options = [])
  {
    if ($options) $this->setOptions($options);
    if (isset($_COOKIE[$name])) {
      setCookie(
        $name,
        '',
        $this->expire,
        $this->path,
        $this->domain,
        $this->secure,
        $this->httponly
      );
      unset($_COOKIE[$name]);
    }
  }

  /**
   * 删除所有 cookie
   * @param array $options
   */
  function deleteAll(array $options = [])
  {
    if ($options) $this->setOptions($options);
    if (!empty($_COOKIE)) {
      foreach ($_COOKIE as $name => $value) {
        setCookie(
          $name,
          '',
          $this->expire,
          $this->path,
          $this->domain,
          $this->secure,
          $this->httponly
        );
        unset($_COOKIE[$name]);
      }
    }
  }
}

