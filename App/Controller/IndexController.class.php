<?php

namespace App\Controller;
use Core\Mvc\Controller;
use \Firebase\JWT\JWT;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class IndexController extends Controller {
  function index () {
    // var_dump($_SERVER['HTTP_AUTHORIZATION']);
    $token = $_SERVER['HTTP_AUTHORIZATION'];
    $token = explode(' ', $token)[1];
    $decode = JWT::decode($token, JWT_KEY, array('HS256'));
    var_dump($decode);
  }
  function register () {
    $token = array(
      'user'=>'sennka',
      'age'=>27,
      'gender'=> 'male'
    );
    $jwt = JWT::encode($token, JWT_KEY);
    echo $jwt;
  }
  function excel(){
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Hello World !');

    $writer = new Xlsx($spreadsheet);
    $writer->save('hello world.xlsx');
    if (file_exists(ROOT_PATH.'/hello world.xlsx')) {
      header("content-disposition:attachment;filename=" . 'hello world.xlsx');
      header("content-length:" . filesize(ROOT_PATH.'/hello world.xlsx'));
      readfile(ROOT_PATH.'/hello world.xlsx');
      unlink(ROOT_PATH.'/hello world.xlsx');
    }
  }
  function errorlog(){
    // 测试： 输出一个错误到日志文件
    echo 'hi';
    error_log('2018年11月5日17:02:54xxxxx');
  }
  function checkRedis(){
    var_dump(class_exists('redis'));
  }
  function phpinfo(){
    phpinfo();
  }
}