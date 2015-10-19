<?
/* ФУНКЦИИ ДЛЯ РАБОТЫ С БАЗОЙ ДАННЫХ И СЕССИЯМИ */

class MySQL {
	
	/*
		Функция Connect (string $user=false, string $pass=false, string $database=false[, bool $logging=false]) 
		производит подключение к базе данных MySQL
			$user 		- имя пользователя MySQL
			$pass 		- пароль пользователя MySQL
			$database	- название БД

		ВЕРСИИ:
			1.1. 2014.12.27
			1.2. 2015.10.19 - Добавлена совместимость с режимом отладки (см. debug.php)
	*/
	public function Connect($user=false, $pass=false, $database=false) {
		
		global $debugMode;
		
		// Установка внутреннего режима отладки
		$tempDebugMode = $debugMode; // Сохранение глобального значения во временную переменную
		$debugMode = false; // Внутреннее вкл/выкл режима отладки
		
		static $funcName = 'MySQL::Connect'; // Название функции для логов

		Debug::Addlog($funcName);

		// Проверки идентификаторов MySQL
		
		Debug::Addlog($funcName, 'Проверка идентификаторов MySQL: Начало.');
		
		if(!$user) { // Поиск логина MySQL в конфиг файле
			
			global 	$mysql_user;
			$user = $mysql_user;
			
			if( !$user ) { // Логин MySQL НЕ найден в конфиг файле
				Debug::Addlog($funcName, '!user » Подключение к MySQL не удалось. Логин БД не введен вручную и не указан в config файле. Завершение работы функции.');

				$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
				return false;
			}
			else { // Логин MySQL найден в конфиг файле
				Debug::Addlog($funcName, "Логин БД определен автоматически из config файла как $user.");
			}
		}
		else { // Логин указан в функции
			Debug::Addlog($funcName, "Логин БД введен вручную как $user.");
		}
		
		if( !$pass ) { // Поиск пароля MySQL в конфиг файле
			
			global  $mysql_password;
			$pass = $mysql_password;
			
			if( !$pass ) { // Пароль MySQL НЕ найден в конфиг файле
				Debug::Addlog($funcName, '!pass » Подключение к MySQL не удалось. Пароль БД не введен вручную и не указан в config файле. Завершение работы функции.');
				
				$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
				return false;
			}
			else { // Пароль MySQL найден в конфиг файле
				Debug::Addlog($funcName, "Пароль БД определен автоматически из config файла как $pass.");
			}
		}
		else { // Пароль указан в функции
			Debug::Addlog($funcName, "Пароль БД введен вручную как $pass.");
		}
		
		if( !$database ) { // Поиск базы данных MySQL в конфиг файле
			
			global 		$mysql_database;
			$database = $mysql_database;
			
			if( !$database ) { // База данных MySQL НЕ найдена в конфиг файле
				Debug::Addlog($funcName, '!database » Подключение к MySQL не удалось. База данных MySQL не введена вручную и не указана в config файле. Завершение работы функции.');
				
				$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
				return false;
			}
			else { // База данных MySQL найдена в конфиг файле
				Debug::Addlog($funcName, "База данных MySQL определена автоматически из config файла как $database.");
			}
		}
		else { // База данных MySQL указана в функции
			Debug::Addlog($funcName, "База данных MySQL введена вручную как $database.");
		}

		Debug::Addlog($funcName, 'Проверка идентификаторов MySQL: Конец.');
		Debug::Addlog($funcName, "Начинаем подключение к MySQL.");

		@ $db = mysql_pconnect( 'localhost', $user, $pass );
		if( !$db ) { // Подключение к MySQL НЕ удалось
			Debug::Addlog($funcName, "!db » Подключение к MySQL не удалось. Завершение работы функции.");
			
			$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
			return false;
		}
		else // Подключение успешно
		{	
			mysql_query('SET NAMES utf8');
			Debug::Addlog($funcName, "Подключение к MySQL произведено.");
			
			$dbase = mysql_select_db($database);
			if( !$dbase ) { // Не найдена указанная база данных
				Debug::Addlog($funcName, "!dbase » Не найдена указанная база данных MySQL. Завершение работы функции.");
			
				$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
				return false;
			}
			
			Debug::Addlog($funcName, "Работа фукнции завершилась успешно.");
			
			$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
			return true;
		}
	}
	
	/*
		Функция Query(string $query)
		
		Посылает запрос $query в базу данных MySQL.
		Возвращает несколько вариантов:
			false	 - в случае, если запрос не был выполнен
			true	 - в случае, если запрос типа INSERT, UPDATE, DELETE, DROP выполнен успешно
			NULL	 - в случае, если запрос типа SELECT, SHOW, DESCRIBE, EXPLAIN удачен, но данные не найдены
			Array	 - в случае, если запрос типа SELECT, SHOW, DESCRIBE, EXPLAIN удачен и найдены данные
		
		ВЕРСИИ:
			1.1. 2015.10.19
	*/
	public function Query( $query ) {
		
		global $debugMode;
		
		// Установка внутреннего режима отладки
		$tempDebugMode = $debugMode; // Сохранение глобального значения во временную переменную
		$debugMode = true; // Внутреннее вкл/выкл режима отладки
		
		static $funcName = 'MySQL::Query'; // Название функции для логов

		Debug::Addlog($funcName,$query);
		
		if( self::Connect() ) { // Есть подключение к MySQL
			$result = mysql_query($query);
			
			if( $result ) { // Запрос успешен
				$numRows = mysql_num_rows($result);
				
				if( $numRows>0 ) { // Успешный результат поискового запроса. Данные найдены
					for( $i=0;$i<$numRows;$i++ ) {
						$queryResult[] = mysql_fetch_array($result);
					}
					Debug::Addlog($funcName,'MySQL query successful. Strings found: '.$numRows.'.');
					mysql_free_result($result);
					
					$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
					return $queryResult;
				}
				elseif ($result === true) { // Успешный результат. Запрос типа INSERT, UPDATE, DELETE, DROP выполнен
					Debug::Addlog($funcName,'MySQL query successful. Value: TRUE.');
					
					$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
					return true;
				}
				else { // Успешный результат поискового запроса. Данные НЕ найдены
					Debug::Addlog($funcName,'MySQL query successful. Strings found: '.$numRows.'.');
					
					$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
					return NULL;
				}
			}
			else { // Запрос не был выполнен, проверить корректность построения запроса
				Debug::Addlog($funcName,'MySQL query failure. Correct query string.');
				
				$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
				return false;
			}
		}
		else { // Подключение к MySQL не удалось (см. MySQL::Connect)
			Debug::Addlog($funcName,'MySQL connection failure.');
			
			$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
			return false;
		}
	}
	
	/* 
		Функция userLoginVK(int $userid, string $hash[, bool $logging = false])
		осуществляет авторизацию OAuth Вконтакте
			$logging - выводит лог
	*/
	public function userLoginVK($uid=NULL, $hash=NULL, $logging = false) {
		
		global $START_TIME, $imagePath;
		
		if ($logging) { $log[] = debug('logtime',true).". MySQL userLoginVK: Начало работы функции.<br>"; }
		
		global $vkOauth_id, $vkApi_id, $siteName;
		global $uid, $first_name, $last_name, $photo, $photo_rec, $hash, $smmUser, $User, $access_token, $user_id;
		//global $valid_user;
		
		if (self::connect()) {
			if ($logging) { $log[] = debug('logtime',true).". MySQL userLoginVK: MySQL работает<br>"; }
			if ($uid && $hash) {
				if ($logging) {
					$log[] = debug('logtime',true).". MySQL userLoginVK: Есть рег.данные<br>";
					$log[] = "<br>".debug('logtime',true).". MySQL userLoginVK: Проверка данных 1:<br>$uid<br>$first_name<br>$last_name<br>$photo<br>$photo_rec<br>$hash<br><br>";
				}
	
				$findUser = mysql_query("SELECT * FROM users WHERE uid='$uid'");
				if (!$findUser) {
					if ($logging) {
						$log[] = debug('logtime',true).". MySQL userLoginVK: ОШИБКА. Невозможно подключиться к базе. Пожалуйста попробуйте позже.<br>";
						$log[] = debug('logtime',true).". MySQL userLoginVK: Работа функции завершена с ошибкой.<br>";
						foreach ($log as $logstring) { print ($logstring); } 
					}
					return false;
				}
				else {
					if (mysql_num_rows($findUser)==0) {
						if ($logging) {
							$log[] = debug('logtime',true).". MySQL userLoginVK: Пользователь не зарегистрирован в базе. Добавляем пользователя<br>";
							$log[] = "<br>".debug('logtime',true).". MySQL userLoginVK: Проверка данных 2:<br>$uid<br>$first_name<br>$last_name<br>$photo<br>$photo_rec<br>$hash<br>";
						}
	
						$addUser = mysql_query("INSERT INTO
									 users (uid, first_name, last_name, photo, photo_rec, hash, create_time) 
									 values ('$uid', '$first_name', '$last_name', '$photo', '$photo_rec', '$hash',  '".time()."')");
	
						if ($logging) {
							$log[] = var_export($addUser,TRUE);
						}
	
						if (!$addUser) {
							if ($logging) { 
								$log[] = debug('logtime',true).". MySQL userLoginVK: ОШИБКА. Невозможно подключиться к базе. Пожалуйста попробуйте позже.<br>";
								$log[] = debug('logtime',true).". MySQL userLoginVK: Работа функции завершена с ошибкой.<br>";
								foreach ($log as $logstring) { print ($logstring); } 
							}
							return false;
						}
						else
						{
							if ($logging) { $log[] = debug('logtime',true).". MySQL userLoginVK: Пользователь добавлен. Делаем повторный запрос<br>"; }
	
							$findUser = mysql_query("SELECT * FROM users WHERE uid='$uid' and hash='$hash'");
							$smmUser  = mysql_fetch_array($findUser);
	
							if ($logging) { 
								$log[] = var_export($findUser,TRUE);
								$log[] = debug('logtime',true).". MySQL userLoginVK: Данные пользователя:<br>";
								$log[] = var_export($smmUser,TRUE);
								$log[] = "<br><br>".debug('logtime',true).". MySQL userLoginVK: Регистрируем пользователя в сессии.<br>";
							}
							
							$User = $smmUser['id'];
							$user_id = $smmUser['uid'];
							$access_token = $smmUser['access_token'];
							session_unregister('User');
							session_unregister('user_id');
							session_unregister('access_token');
							session_unregister('smmUser');
							session_register('User');
							session_register('user_id');
							session_register('access_token');
							session_register('smmUser');
							
							if ($logging) { foreach ($log as $logstring) { print ($logstring); } }
							return true;
						}
					}
					elseif (mysql_num_rows($findUser)>0) {
						if ($logging) { $log[] = debug('logtime',true).". MySQL userLoginVK: Пользователь найден в базе<br>"; }
						
						$smmUser = mysql_fetch_array($findUser);
						
						if ($logging) { 
							$log[] = debug('logtime',true).". MySQL userLoginVK: Данные пользователя:<br>";
							$log[] = var_export($smmUser,TRUE);
							$log[] = "<br><br>".debug('logtime',true).". MySQL userLoginVK: Регистрируем пользователя в сессии.<br>";
						}
						
						$User = $smmUser['id'];
						$user_id = $smmUser['uid'];
						$access_token = $smmUser['access_token'];
						session_unregister('User');
						session_unregister('user_id');
						session_unregister('access_token');
						session_unregister('smmUser');
						session_register('User');
						session_register('user_id');
						session_register('access_token');
						session_register('smmUser');

						if ($logging) { 
							$log[] = '──────────────────────────────<br>'.
									  debug('logtime',true).'MySQL userLoginVK: Данные на выходе:<br>$User: '.$User.'<br>$smmUser: ';
							$log[] = var_export($smmUser,TRUE);
							foreach ($log as $logstring) { print ($logstring); }
						}
						return true;
					}
				}
			}

			if ($logging) { $log[] = debug('logtime',true).". MySQL userLoginVK: Рег.данные не введены.<br>"; }

			if (!session_is_registered('User'))	{
				require_once(path_root().'vkapi/vkauth/login.php');
				if ($logging) { 
					$log[] = debug('logtime',true).". MySQL userLoginVK: Пользователь не авторизован. Включаем форму авторизации.<br>";
					foreach ($log as $logstring) { print ($logstring); } 
				}
				return false;
			}
			else {
				if ($logging) { 
					$log[] = debug('logtime',true).". MySQL userLoginVK: Пользователь авторизован.<br>";
					foreach ($log as $logstring) { print ($logstring); } 
				}
				return true;
			}
		}
		else {
			if ($logging) { 
				$log[] = debug('logtime',true).". MySQL userLoginVK: ОШИБКА. Невозможно подключиться к базе. Пожалуйста попробуйте позже.<br>";
				$log[] = debug('logtime',true).". MySQL userLoginVK: Работа функции завершена с ошибкой.<br>";
				foreach ($log as $logstring) { print ($logstring); } 
			}
			return false;
		}
	}
	
	/* 
		Функция userLogout()
		удаляет информацию о зарегистрированном пользователе из сессии
		$logging - Включение вывода на экран лога функции (указать значение true)
	*/
	public function userLogout($logging=false) {
		if ($logging) { 
			$log[] = debug('logtime',true).". MySQL userLogout: Начало работы функции.<br>";
		}
		
		global $User, $smmUser, $user_id, $access_token;
		
		session_unregister('User');
		session_unregister('user_id');
		session_unregister('access_token');
		session_unregister('smmUser');
		unset($User);
		unset($user_id);
		unset($access_token);
		unset($smmUser);
		if ($logging) { foreach ($log as $logstring) { print ($logstring); } }
	}
	
	/* 
		Функция userAuth()
		выполняет авторизацию пользователя на сайте (логин/логаут)
	*/
	public function userAuth() {
		global $logout;
		
		if (isset($logout)) {
			self::userLogout(); 
		}
		self::userLoginVK() or die();
	}
}


?>