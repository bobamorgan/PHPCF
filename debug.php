<?
/* 
	ФУНКЦИИ ДЛЯ ОТЛАДКИ СКРИПТОВ 

	РЕЖИМ ОТЛАДКИ v.1.0. Спецификация:
	Работает посредством глобальных переменных: 
		$debugMode 	- true/false вкл/выкл режима отладки,
		$debugLog	- архив логов для текущей сессии.
	Методы для работы с логами:
		Debug::Addlog()		- Добавление строки в лог,
		Debug::Printlog() 	- Вывод лога.
	Подробней см.соответствующие функциям описания.
*/

class Debug {
	/*
		Функция Checkpoint()
		На экран выводится время в микросекундах, необходимое для загрузки фрагмента 
		страницы, на котором была вызвана функция.
			$return - режим работы функции
				false	- функция выводит на экран данные
				true	- функция возвращает данные вместо вывода на экран
		
		ВЕРСИИ:
			1.0. 2015.01.14.
			1.1. 2015.01.18. Добавил возможность независимого вкл/выкл таймера
	*/
	public function Checkpoint ($return=false) {
		
		global $START_TIME,
			   $debugMode,
			   $debugShowCheckpoints,
			   $accessRights,
			   $developerRights;
	
		if( $accessRights>=$developerRights ) { // Проверка прав разработчика
			if (!$START_TIME) { $START_TIME = microtime(TRUE); }
			else {
				if( $debugMode || $debugShowCheckpoints ) {
					$scriptTime = microtime(TRUE) - $START_TIME;
					$scriptTime = number_format($scriptTime*1000, 3, ',', ' ');
					if( $return == false ) { 
						print ($scriptTime.' ms'); 
					}
					elseif( $return == true ) { 
						return ($scriptTime.' ms'); 
					}
				}
				else return false;
			}
		}
		else return false;
	}
	
	/*
		Функция Vardump(mixed $var[, bool $return = false])
		Отображает техническую информацию.
			$var - основной параметр функции. Его возможные значения:
				Любая переменная - на экран выводится содержимое переменной, отформатированное
									в формате Bootstrap
			$return - режим работы функции
				false	- функция выводит на экран данные
				true	- функция возвращает данные вместо вывода на экран
		Версии:
			1.0. 2015.01.14.
	*/
	public function Vardump ($var = false,$return = false) {
		
		global $accessRights,
			   $developerRights;
		
		if( $accessRights>=$developerRights ) { // Проверка прав разработчика
			if (!$var) {
				return false;
			}
			else {
				if ($return == false) {
					if (!empty($var)) {
						?>
						<div class="panel">
						  <header class="panel-heading font-bold">Содержимое переменной</header>
						  <div class="panel-body">
							<pre class="bg-white no-borders"><?
						print_r($var);
						?>
							</pre>
						  </div>
						</div>
						<?
					}
				}
				elseif ($return == true) {
					return ( var_export($var,true) );
				}
			}
		}
		else return false;
	}
	
	/*
		Выводит содержимое системных переменных в формате Bootstrap
		Версии:
			1.0. 2014.10.29.
			1.1. 2015.01.14. функция внесена в класс Debug
			1.2. 2015.01.17. Добавлена возможность вывода отдельных переменных
	*/
	public function Showglobals ($globalsList) {
		
		global  $debugMode,
				$accessRights,
			   	$developerRights;
		
		if( $accessRights>=$developerRights ) { // Проверка прав разработчика;
			if (!empty($_POST)) {
				if ( $debugMode || stristr($globalsList, 'post') ) {
					?>
					<div class="panel wrapper">
					  <header class="panel-heading font-bold">Содержимое $_POST</header>
					  <div class="panel-body">
						<pre class="bg-white no-borders"><? print_r($_POST); ?>
						</pre>
					  </div>
					</div>
					<?
				}
			}
			if (!empty($_GET)) {
				if ( $debugMode || stristr($globalsList, 'get') ) {
					?>
					<div class="panel wrapper">
					  <header class="panel-heading font-bold">Содержимое $_GET</header>
					  <div class="panel-body">
						<pre class="bg-white no-borders"><? print_r($_GET); ?>
						</pre>
					  </div>
					</div>
					<?
				}
			}
			if (!empty($_FILES)) {
				if ( $debugMode || stristr($globalsList, 'files') ) {
					?>
					<div class="panel wrapper">
					  <header class="panel-heading font-bold">Содержимое $_FILES</header>
					  <div class="panel-body">
						<pre class="bg-white no-borders"><? print_r($_FILES); ?>
						</pre>
					  </div>
					</div>
					<?
				}
			}
			if (!empty($_SESSION)) {
				if ( $debugMode || stristr($globalsList, 'session') ) {
					?>
					<div class="panel wrapper">
					  <header class="panel-heading font-bold">Содержимое $_SESSION</header>
					  <div class="panel-body">
						<pre class="bg-white no-borders"><? print_r($_SESSION); ?>
						</pre>
					  </div>
					</div>
					<?
				}
			}
			if (!empty($_COOKIE)) {
				if ( $debugMode || stristr($globalsList, 'cookie') ) {
					?>
					<div class="panel wrapper">
					  <header class="panel-heading font-bold">Содержимое $_COOKIE</header>
					  <div class="panel-body">
						<pre class="bg-white no-borders"><? print_r($_COOKIE); ?>
						</pre>
					  </div>
					</div>
					<?
				}
			}
		}
		else return false;
	}
	
	/*
		Выводит имя переменной
		Взял тут http://rsdn.ru/forum/web/3560073.all<br>
		Дата создания: 29.10.2014
	*/
	public function Varname(&$var) {	
		
		$old = $var;	
		$var = md5(mt_rand(0, 999999))."_".$var; // Временно изменяем значение на случай, если есть несколько переменных с одинаковым значением
		$name= array_search($var, $GLOBALS);	
		$var = $old;
		return $name;
	}
	
	/*
		Функция Printlog() выводит Лог всех функций, отформатированный в формате Bootstrap
	*/
	public function Printlog() {
		
		global 	$debugMode,
				$debugLog,
				$accessRights,
			   	$developerRights;
		
		if( $accessRights>=$developerRights ) { // Проверка прав разработчика;
			if( $debugMode ) {
				if( !empty($_SESSION['debugLog']) ) {
					?>
					<div class="panel wrapper">
					  <header class="panel-heading font-bold">Логи выполнения функций</header>
					  <div class="panel-body">
						<pre class="bg-white no-borders"><? 
							foreach ($_SESSION['debugLog'] as $logString) {
								print_r($logString);
							}
							$_SESSION['debugLog'] = NULL;
							?>
						</pre>
					  </div>
					</div>
					<?
				}
			}
		}
		else return false;
	}
	
	/*
		Addlog(string $funcName, string $message)
		Добавляет строку в лог выполнения функций.
		Переменные:
			$funcName 	= название функции для отображения в логах;
			$message	= сообщение
		
		ВЕРСИИ:
			1.0. 2015.01.14.
	*/
	public function Addlog ($funcName,$message) {
		
		global 	$debugMode,
				$accessRights,
			   	$developerRights;
		
		if( $accessRights>=$developerRights ) { // Проверка прав разработчика;
			if ($debugMode!==false) { $_SESSION['debugLog'][] = Debug::Checkpoint(true).".	".$funcName." » ".$message."<br>"; }
		}
	}
	
	/*
		CheckCode()
		Функция служит для технической отладки кода.
		Выполняет проверку в любом месте кода, выводит лог и содержимое переменной $var, после чего завершает сценарий.
		
		ВЕРСИИ:
			1.0. 2015.10.19.
	*/
	public function CheckCode( $var=false ) {
		?><pre><?
		self::Printlog();
		var_dump($var);
		?><pre><?
		die;
	}
}
?>