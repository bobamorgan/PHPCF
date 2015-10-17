<?
/* ФУНКЦИИ ДЛЯ РАБОТЫ С БАЗОЙ ДАННЫХ И СЕССИЯМИ */

class Mysql {
	//
	//	Список функций
	//
	//	connect()			- производит подключение к базе данных MySQ
	//	userLoginVK()		- осуществляет авторизацию OAuth Вконтакте
	//	userLogout()		- удаляет информацию о зарегистрированном пользователе из сессии
	//	function userAuth()	- выполняет авторизацию пользователя на сайте (логин/логаут)
	//			
	
	/*
		Функция connect(string $user=false, string $pass=false, string $database=false[, bool $logging=false]) производит подключение к базе данных MySQL
			$user 		- имя пользователя MySQL
			$pass 		- пароль пользователя MySQL
			$database	- название БД
			$logging	- Включение вывода на экран лога функции (указать значение true)
		Модифицирована: 27.12.2014.
	*/
	public function connect($user=false, $pass=false, $database=false, $logging=false) {
		global $START_TIME;
		if ($logging) { $log[] = debug('logtime',true).". MySQL Connect: Начало работы функции.<br>"; }

		if(!$user) {
			global $mysql_user;
			$user = $mysql_user;
			if(!$user) {
				if ($logging) {
					$log[] = debug('logtime',true).". MySQL Connect: ОШИБКА. Пользователь БД не введен вручную и не указан в config файле.<br>";
					$log[] = debug('logtime',true).". MySQL Connect: Работа функции завершена с ошибкой.<br>";
					foreach ($log as $logstring) { print ($logstring); } 
				}
				return false;
			}
			else { if ($logging) { $log[] = debug('logtime',true).". MySQL Connect: Пользователь определен автоматически из config файла как '$user'.<br>"; } }
		}
		else {
			if ($logging) { $log[] = debug('logtime',true).". MySQL Connect: Пользователь введен вручную как $user.<br>"; }
		}
		
		if(!$pass) {
			global $mysql_password;
			$pass = $mysql_password;
			if(!$pass) {
				if ($logging) {
					$log[] = debug('logtime',true).". MySQL Connect: ОШИБКА. Пароль к БД не введен вручную и не указан в config файле.<br>";
					$log[] = debug('logtime',true).". MySQL Connect: Работа функции завершена с ошибкой.<br>";
					foreach ($log as $logstring) { print ($logstring); } 
				}
				return false;
			}
			else {
				if ($logging) { $log[] = debug('logtime',true).". MySQL Connect: Пароль к БД определен автоматически из config файла.<br>"; }
			}		
		}
		else {
			if ($logging) { $log[] = debug('logtime',true).". MySQL Connect: Пароль к БД введен вручную.<br>"; }
		}
		
		if(!$database) {
			global $mysql_database;
			$database = $mysql_database;
			if(!$database) {
				if ($logging) {
					$log[] = debug('logtime',true).". MySQL Connect: ОШИБКА. Имя БД не введено вручную и не указано в config файле.<br>";
					$log[] = debug('logtime',true).". MySQL Connect: Работа функции завершена с ошибкой.<br>";
					foreach ($log as $logstring) { print ($logstring); }
				}
				return false;
			}
			else {
				if ($logging) { $log[] = debug('logtime',true).". MySQL Connect: Имя БД определено автоматически из config файла как '$database'.<br>"; }
			}
		}
		else {
			if ($logging) { $log[] = debug('logtime',true).". MySQL Connect: Имя БД введено вручную как $database.<br>"; }
		}
		if ($logging) { $log[] = debug('logtime',true).". MySQL Connect: Начинаем подключение к БД.<br>"; }
		@ $db = mysql_pconnect('localhost',$user,$pass);
		if (!$db)
		{
			 if ($logging) {
				 $log[] = debug('logtime',true).'. MySQL Connect: ОШИБКА. Невозможно подключиться к БД. Пожалуйста попробуйте позже.<br>';
				 $log[] = debug('logtime',true).". MySQL Connect: Работа функции завершена с ошибкой.<br>";
				 foreach ($log as $logstring) { print ($logstring); }
			 }
			 return false;
		}
		else
		{	
			mysql_query('SET NAMES utf8');
			$dbase = mysql_select_db($database);
			if (!$dbase)
			{
				if ($logging) {
					$log[] = debug('logtime',true).'. MySQL Connect: ОШИБКА. Указанной базы данных не существует.<br>';
					$log[] = debug('logtime',true).". MySQL Connect: Работа функции завершена с ошибкой.<br>";
					foreach ($log as $logstring) { print ($logstring); }
				}
				return false;
			}
			if ($logging) {
				$log[] = debug('logtime',true).'. MySQL Connect: УСПЕХ. Подключение к базе данных произведено.<br>';
				foreach ($log as $logstring) { print ($logstring); }
			}
			return true;
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