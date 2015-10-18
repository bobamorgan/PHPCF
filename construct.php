<?
session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/core/debug.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/data.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/db.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/html.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/vk.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/ac.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/core/config.php');

if ($debugPage !== NULL) $debugMode = $debugPage; // Управление режимом отладки страницы

Debug::Checkpoint(); // Запуск таймера отладки
Debug::Addlog('HTML::'.$pageName,$_SERVER['SCRIPT_URI']); // Первая строка в журнале логов

Mysql::userAuth(); // Авторизация пользователя

$accessRights = AccessControl::AccessCheck(); // Контроль доступа к контенту

Html::Header($pageName,$pageVersion);
Html::MenuLeft();
Html::ContentPage($contentPage);
Html::Footer();
?>