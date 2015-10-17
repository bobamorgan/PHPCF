<?
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/core/debug.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/data.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/db.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/html.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/vk.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/ac.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/config.php');

Debug::Checkpoint(); // Запуск таймера отладки
Debug::Addlog('HTML::'.$pageName,$_SERVER['SCRIPT_URI']); // Первая строка в журнале логов

// Авторизация пользователя
Mysql::userAuth();

// Контроль доступа к контенту
$accessRights = AccessControl::AccessCheck();
?>