<?php

namespace Core\Libs;

class Image
{
  /**
   * 从 get 传参中获取图片属性:
   * bg=背景颜色 请使用十六进制
   * fg=字体颜色
   * size=width x height
   * t = 文字描述
   * 用法  ?size=200x400&bg=09f&fg=c00&t=xxx
   */
  function placeholder()
  {
    $size = isset($_GET['size']) ? $_GET['size'] : '200x200';
    $text = isset($_GET['t']) ? $_GET['t'] : $size;
    $size = explode('x', $size);
    $w = $size[0];
    $h = $size[1];
    $img = imagecreatetruecolor($w, $h);
    $bg = isset($_GET['bg']) ? $_GET['bg'] : 'ffffff';
    $fg = isset($_GET['fg']) ? $_GET['fg'] : '333333';
    $bg = $this->hex2rgb($bg);
    $fg = $this->hex2rgb($fg);
    $bg = imagecolorallocate($img, hexdec($bg['r']), hexdec($bg['g']), hexdec($bg['b']));  // 为一副图像分配颜色
    $fg = imagecolorallocate($img, hexdec($fg['r']), hexdec($fg['g']), hexdec($fg['b']));  // 为一副图像分配颜色
    imagefilledrectangle($img, 0, 0, $w, $h, $bg);
// imagettftext($img, mt_rand(16, 18), mt_rand(-15, 15), $i * 20, mt_rand(30, 35), imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)), $fontfile, $verify[$i]);
    imagestring($img, 4, ($size[0] - strlen($text) * 7) / 2, $h / 2, $text, $fg); // 我测量的内置字体4高度是 10 宽度是 6
    header('Content-Type: image/png');
    header('Content-Disposition: inline; filename=' . $size[0] . 'x' . $size[1] . '.png');
    imagepng($img);
    imagedestroy($img);
  }

  /**
   * @param string $sessname session 名字
   * @param int $length 字符串长度
   * @param int $size 图片大写,默认20
   * @param int $type 字符串类型 [1|2|3]
   */
  function generateVerifyImage($sessname = 'verifycode', $length = 4, $size = 20, $type = 3)
  {
    // 验证码字符串
    $verify = $this->generateVerifyString($length);
    $_SESSION[$sessname] = $verify;
    // 使用了等宽字体
    $ttf = dirname(__FILE__) . '/Image/ttf/Anonymous.ttf';
    $w = $size * $length + $size * 2;
    $hy = 2;
    $h = $size * $hy;
    $image = imagecreatetruecolor($w, $h);
    $bgcolor = imagecolorallocate($image, 255, 255, 255);
    imagefilledrectangle($image, 0, 0, $w, $h, $bgcolor); //背景色和展示区域
    // 干扰点 宽高的十分之一,向上取整
    for ($i = 0; $i < ceil($w * $h / 10); $i++) {
      imagesetpixel($image, mt_rand(0, $w), mt_rand(0, $h), imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
    }
    // 干扰线
    for ($i = 0; $i < $length; $i++) {
      imageline($image, mt_rand(0, $w), mt_rand(0, $h), mt_rand(0, $w), mt_rand(0, $h), imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)));
    }
    // 画出验证码字符串
    for ($i = 0; $i < $length; $i++) {
      imagettftext(
        $image,
        $size,
        mt_rand(-30, 30),
        ($size + $size * $i),
        ($size + $size * ($hy - 1) / 2),
        imagecolorallocate($image, mt_rand(0, 99), mt_rand(0, 99), mt_rand(0, 99)),
        $ttf,
        $verify[$i]
      );
    }

    header('Content-Type: image/png');
    imagepng($image);
    imagedestroy($image);
  }

  /**
   * @param $length 几位验证码
   * @param $type [1|2|3] 分别对应[纯数字, 纯字母, 数字+字母]
   * @return bool|string 生成验证码字符串
   */
  function generateVerifyString($length = 4, $type = 3)
  {
    if (!is_numeric($length) || !is_numeric($type)) {
      trigger_error('传入参数有误, 参数为数字类型');
      exit();
    }
    $chars = ''; // 带选取的字符集
    switch ($type) {
      case 1:
        // 纯数字
        $chars = join("", range(0, 9));
        break;
      case 2:
        // 纯字母
        $chars = join("", range('A', 'Z'));
        break;
      default:
        // 数字加大写字母的验证码
        $chars = join("", array_merge(range(0, 9), range("A", "Z")));
        break;
    }
    $chars = str_shuffle($chars);
    return substr($chars, 0, $length);
  }

  /**
   * @param $hex 输入十六进制的颜色,允许三位缩写和六位写全 例如 #fff===#ffffff
   * @return array 得到 rgb 的颜色数组
   */
  function hex2rgb($hex)
  {
    if (strlen($hex) > 3) {
      $r = substr($hex, 0, 2);
      $g = substr($hex, 2, 2);
      $b = substr($hex, 4, 2);
    } else {
      $r = $hex[0] . $hex[0];
      $g = $hex[1] . $hex[1];
      $b = $hex[2] . $hex[2];
    }
    return array(
      'r' => $r,
      'g' => $g,
      'b' => $b
    );
  }

  function waterText()
  {

  }
}
