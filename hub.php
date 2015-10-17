<?
require_once($_SERVER['DOCUMENT_ROOT'].'/core/debug.php');
debug();
require_once($_SERVER['DOCUMENT_ROOT'].'/core/data.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/db.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/html.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/vk.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/config.php');

session_start();

// Авторизация пользователя
Mysql::userAuth();
?>