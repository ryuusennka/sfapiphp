<?php

namespace App\Controller;

use Core\Mvc\Controller;
use \Firebase\JWT\JWT;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Core\Libs\Image;

class IndexController extends Controller
{
  function index()
  {
    var_dump(PHP_OS);
    exit;
    echo dirname(__FILE__);
    exit;
    $this->assign('uuid', createUuid());
    var_dump(!!$_FILES, $_FILES);
    // if(I('get.issubmit')){
    //   var_dump($_FILES['file']);
    // }
    $this->render();
  }

  /**
   * 解码头部jwt
   *
   * @return void
   */
  function jwt()
  {
    $token = $_SERVER['HTTP_AUTHORIZATION'];
    $token = explode(' ', $token)[1];
    $decode = JWT::decode($token, JWT_KEY, array('HS256'));
    var_dump($decode);
  }

  /**
   * 输出 jwt
   *
   * @return void
   */
  function register()
  {
    $token = array(
      'user' => 'sennka',
      'age' => 27,
      'gender' => 'male'
    );
    $jwt = JWT::encode($token, JWT_KEY);
    echo $jwt;
  }

  function excel()
  {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Hello World !');

    $writer = new Xlsx($spreadsheet);
    $writer->save('hello world.xlsx');
    if (file_exists(ROOT_PATH . '/hello world.xlsx')) {
      header("content-disposition:attachment;filename=" . 'hello world.xlsx');
      header("content-length:" . filesize(ROOT_PATH . '/hello world.xlsx'));
      readfile(ROOT_PATH . '/hello world.xlsx');
      unlink(ROOT_PATH . '/hello world.xlsx');
    }
  }

  function errorlog()
  {
    // 测试： 输出一个错误到日志文件
    echo 'hi';
    error_log('2018年11月5日17:02:54xxxxx');
  }

  function showPage()
  {
    $this->render();
  }

  function phpinfo()
  {
    phpinfo();
  }

  function phpinfo2()
  {
    $gd = new Image();
    $gd->generateVerifyImage();
  }
}