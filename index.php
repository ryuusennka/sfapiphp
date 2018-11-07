<?php
const ROOT_PATH = __DIR__;
const CORE_PATH = ROOT_PATH . '/Core';
const APP_PATH = ROOT_PATH . '/App';
const APP_DEBUG = true;
// 引入插件
$composer = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composer)) require_once $composer; // 引入 composer 自动加载

require_once CORE_PATH . '/Core.php';