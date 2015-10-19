<?
/*
	Класс для работы с API Вконтакте. 
*/ 

class VkApi {
    
/*
	Функция api(string $method, string $param)
	Посылает запрос к API Вконтакте
	Дата создания: 2015.01.04
*/
	public function api( $method, $param ) {
		global $access_token;
		
		$res = self::curlPost("https://api.vk.com/method/$method?$param&access_token=$access_token");
		
		return json_decode($res);
	}
	
/*
	!!! НУЖНО ДОРАБОТАТЬ !!!

	Функция  imgUploadAlbum(int $gid, int $aid, string $imageURL[, string $imageText])
	Загружает изображение в альбом группы или пользователя Вконтакте. Возвращает внутренний адрес изображения.
	Дата создания: 2015.01.04
*/
	public function imgUploadAlbum( $gid, $aid, $imageURL, $imageText ) {
		$imageText = str_replace(' ', '%20', $imageText);
		$data = array("file1"=>"@".$this->imgSave($imageURL));
		$server = api("photos.getUploadServer","album_id=$aid&group_id=$gid");
		$res = curlPost($server->response->upload_url, $data);
		$upload = json_decode($res);    
		$save = api("photos.save", "group_id=$gid&caption=$imageText&album_id={$upload->aid}&server={$upload->server}&photos_list={$upload->photos_list}&hash={$upload->hash}");
		
		return $save;
		//return $save->response[0]->id;
	}

/*
	Функция  imgUploadWall(int $gid, string $imageURL)
	Загружает изображение на стену группы или пользователя Вконтакте. Возвращает внутренний адрес изображения
	Дата создания: 2015.01.04.
*/
	public function imgUploadWall( $gid, $imageURL ) {
		$data = array("file1"=>"@".self::imgSave($imageURL));
		$server = self::api("photos.getWallUploadServer","group_id=$gid");
		$res = self::curlPost($server->response->upload_url, $data);
		$upload = json_decode($res);
		$save = self::api("photos.saveWallPhoto", "group_id=$gid&server={$upload->server}&hash={$upload->hash}&photo={$upload->photo}");
		
		return $save;
		//return $save->response[0]->id;
	}

/*
	Функция saveImg(string $url)
	Сохраняет изображение , находящееся по адресу $imageURL, на сервер для временного использования.
	Возвращает относительный путь к сохраненному файлу (относительно исполняемого скрипта).
	Дата создания: 2015.01.04
*/
	public function imgSave( $imageURL ) {
		global $imagePath;
		global $User;
		
		$name = "image$User.jpg";
		$file = file_get_contents($imageURL);  
		$openedfile = fopen($imagePath.$name, "w");
		fwrite($openedfile, $file);
		fclose($openedfile);
		
		return $imagePath.$name;
	}

	public function wallPost( $msgText, $attach ) {
		global $group_id;
		
		if (is_int($msgText[0]))
		  $msgText .= '%20'.$msgText;
		
		$msgText = str_replace(' ', '%20', $msgText);
		$post = self::api("wall.post","owner_id=-$group_id&message=$msgText&attachments=$attach");
		
		return $post;
	}

/*
	Функция groupsGet(int $user[,string $filter[, int $extended[, string $fields]]])
	Возвращает список групп пользователя.
		$user 		- идентификатор пользователя, информацию о сообществах которого требуется получить (положительное число, по умолчанию идентификатор текущего пользователя);
		$filter 	- список фильтров сообществ, которые необходимо вернуть, перечисленные через запятую. Доступны значения admin, editor, moder, groups, publics, events. 
				  	  По умолчанию возвращаются все сообщества пользователя.
		$extended	- если указать в качестве этого параметра 1, то будет возвращена полная информация о группах пользователя. По умолчанию 0.
		$fields		- список дополнительных полей, которые необходимо вернуть, разделенных через запятую/
					  Возможные значения: city, country, place, description, wiki_page, members_count, counters, start_date, finish_date, can_post, can_see_all_posts,
					  activity, status, contacts, links, fixed_post, verified, site, can_create_topic. 
					  Обратите внимание, этот параметр учитывается только при extended=1.
	php обработчик метода вк https://vk.com/dev/groups.get 
	Дата создания: 2015.01.04
*/	
	public function groupsGet( $user, $filter=false, $extended=0, $fields=false ) {
		global $user_id;
		
		if (!$user) {
			$param = "user_id=$user_id";
		}
		if ($filter) {
			$param .= "&filter=$filter";
		}
		if ($extended) {
			$param .= "&extended=$extended";
		}
		if ($fields) {
			$param .= "&fields=$fields";
		}
		$post = self::api("groups.get",$param);
		
		return $post;
	}
	
	/*
		Функция curlPost()
		Открывает, обрабатывает и закрывает сессию cURL.
		Дата создания: 2015.01.04
	*/
	public function curlPost( $url, $data=array() ) {
		if ( ! isset($url)) {
			return false;
		}
								  
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_URL, $url);     

		if (count($data) > 0) {
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
	
		$response = curl_exec($ch);
	
		curl_close($ch);
		
		return $response;
	}
	
	/* 
		Функция userLoginWidget()
		Производит авторизацию Вконтакте по ссылке по схеме виджета.
		Метод подробно описан здесь: https://vk.com/dev/widget_auth
		
		Совместима с РЕЖИМОМ ОТЛАДКИ v.1.0. (см. debug.php)
		
		ВЕРСИИ:
			1.0. 2015.10.19 - добавляю совместимость с режимом отладки
	*/
	public function userLoginWidget() {
		
		global 	$vkOauth_id,
				$vkOauth_key,
				$siteName,
				$uid, 
				$first_name, 
				$last_name, 
				$photo, 
				$photo_rec, 
				$hash, 
				$vkUser, 
				$debugLog,
				$debugMode;
		
		//global $imagePath, $valid_user;
		
		$tempDebugMode = $debugMode; // Сохранение глобального значения во временную переменную
		$debugMode = true; // Внутренний вкл/выкл. режима отладки
				
		static $funcName = 'VkApi::userLoginWidget'; // Название функции для логов
		Debug::Addlog($funcName);
		
		
		if( $_SESSION['vkUser'] ) { // Пользователь авторизован в сессии
			
			Debug::Addlog($funcName,'User is registered and authorized.');

			$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
			return true;
		}
		
		if( $_COOKIE['uid'] && $_COOKIE['hash'] ) { // Данные пользователя есть в куках
			Debug::Addlog($funcName,'User is registered in COOKIES. Checking VK reg data.');
			
			$uid  = $_COOKIE['uid'];
			$hash = $_COOKIE['hash'];
			
			$userGet = self::api('users.get',"user_ids=$uid&fields=photo_50,photo_200");
				
			$first_name	  = $userGet->response[0]->first_name;
			$last_name	  = $userGet->response[0]->last_name;
			$photo		  = $userGet->response[0]->photo_200;
			$photo_rec	  = $userGet->response[0]->photo_50;
			
			//Debug::CheckCode($photo);
		}
		
		if( $uid && $first_name && $last_name && $photo && $photo_rec && $hash ) { // Получены регистрационные данные Вконтакте
			Debug::Addlog($funcName,'VK reg data recieved.');
			Debug::Addlog($funcName,"Data check:");
			Debug::Addlog($funcName,"uid: $uid");
			Debug::Addlog($funcName,"first_name: $first_name");
			Debug::Addlog($funcName,"last_name: $last_name");
			Debug::Addlog($funcName,"photo: $photo");
			Debug::Addlog($funcName,"photo_rec: $photo_rec");
			Debug::Addlog($funcName,"hash: $hash");
			
			//$findUser = mysql_query("SELECT * FROM users WHERE uid='$uid'"); // Ищем пользователя в базе
			$findUser = MySQL::Query( "SELECT * FROM users WHERE uid='$uid'" );
			
			if( $findUser === false ) { // Не удалось выполнить запрос к MySQL
				Debug::Addlog($funcName,'!findUser » MySQL connection failure. Stopping function.');
				
				$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
				return false;
			}
			else { // Запрос к MySQL выполнен успешно
				if( $findUser === NULL ) { // Регистрация нового пользователя.
					Debug::Addlog($funcName,'Registering new user.');

					$addUser = MySQL::Query( "INSERT INTO
								 			  users (uid, first_name, last_name, photo, photo_rec, hash, create_time) 
											  values ('$uid', '$first_name', '$last_name', '$photo', '$photo_rec', '$hash', '".time()."')" );
					
					if( !$addUser ) { // Не удалось выполнить запрос к MySQL
						Debug::Addlog($funcName,'!addUser. MySQL connection failure. Stopping function.');

						$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
						return false;
					}
					else { // Пользователь добавлен в MySQL
						Debug::Addlog($funcName,'User has been added into MySQL. Second query.');

						$findUser = MySQL::Query( "SELECT * FROM users WHERE uid='$uid' and hash='$hash'" ); // Повторный поиск в MySQL
						$vkUser	  = $findUser[0];
						//$findUser = mysql_query("SELECT * FROM users WHERE uid='$uid' and hash='$hash'"); 
						//$vkUser  = mysql_fetch_object($findUser);
						//Debug::Addlog($funcName,var_export($findUser,TRUE));
						
						Debug::Addlog($funcName,'User data:');
						Debug::Addlog($funcName,var_export($vkUser,TRUE));

						$userRegister = true; // Маркер для блока регистрации пользователя (см. ниже)
					}
				}
				elseif( count($findUser) >0 ) { // Пользователь найден в базе
					Debug::Addlog($funcName,'User is already registered in MySQL. Updating data.');
		
					// Обновляем данные пользователя в базе
					$updateUser = MySQL::Query( "UPDATE users 
												 SET 	first_name='$first_name', 
												 	 	last_name='$last_name', 
													 	photo='$photo', 
													 	photo_rec='$photo_rec', 
													 	hash='$hash' 
												 WHERE 	uid='$uid'" );
					
					if( !$updateUser ) { // Обновление данных не удалось
						Debug::Addlog($funcName,'Failed updating user data in MySQL. Stopping function.');
						
						$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
						return false;
					}
					else { // Данные пользователя обновились успешно, продолжение аутентификации
						Debug::Addlog($funcName,'User data has been successfully updated in MySQL.');
						
						$vkUser = $findUser[0];
						$userRegister = true; // Маркер для блока регистрации пользователя (см. ниже)
					}
				}
			}
		}
		
		if( $userRegister ) { // Блок регистрации пользователя
			
			// Регистрируем пользователя в сессии
			Debug::Addlog($funcName,'Registering user in SESSION.');
			unset( $_SESSION['vkUser'] );
			unset( $_SESSION['uid'] );
			unset( $_SESSION['first_name'] );
			unset( $_SESSION['last_name'] );
			unset( $_SESSION['photo'] );
			unset( $_SESSION['photo_rec'] );
			
			$_SESSION['vkUser']   	= $vkUser;
			$_SESSION['uid'] 		= $uid;
			$_SESSION['first_name'] = $first_name;
			$_SESSION['last_name']	= $last_name;
			$_SESSION['photo'] 	  	= $photo;
			$_SESSION['photo_rec'] 	= $photo_rec;
			
			// Регистрируем пользователя в куках на 10 лет
			Debug::Addlog($funcName,'Registering user in COOKIES.');
			setcookie("uid", $uid, time()+315360000, '/', $_SERVER['HTTP_HOST']);
			setcookie("hash", $hash, time()+315360000, '/', $_SERVER['HTTP_HOST']);

			Debug::Addlog($funcName,'User is registered and authorized.');
			Debug::Addlog($funcName,'Refreshing page...');
			
			$debugMode = $tempDebugMode; // Возвращение переменной глобального значения
			http_redirect($_SERVER['SCRIPT_URI']);
		}
		
		if( !$_SESSION['vkUser'] ) { // Пользователь НЕ авторизован в сессии. Загружаем страницу авторизации.
			Debug::Addlog($funcName,'Unregistered user. Loading login page.');
			
			$debugMode = $tempDebugMode; // Возвращение значения глобальной переменной
			require_once(path_root().'vkapi/vkauth/login.php');
			return false;
		}
	}
	
	/*
		!!! АБАЖУР - НУЖНА КОРРЕКТИРОВКА
		
		Функция UserLoginCode()
		Производит авторизацию Вконтакте по ссылке по схеме сайта.
		Ссылка должна быть такого вида: https://oauth.vk.com/authorize?client_id=ИД_ПРИЛОЖЕНИЯ&scope=ПРАВА&redirect_uri=АДРЕС_ПЕРЕАДРЕСАЦИИ&response_type=code&v=5.27
		Метод подробно описан здесь: https://vk.com/dev/auth_sites
		
		Совместима с РЕЖИМОМ ОТЛАДКИ v.1.0. (см. debug.php)
		
		ВЕРСИИ:
			1.0. 2015.01.14.
	*/
	public function UserLoginCode() {
		
		global 	$code,
				$access_token,
				$debugLog,
				$debugMode,
				$vkApi_id,
				$vkApi_key,
				$valid_user,
				$uid,
				$first_name,
				$last_name,
				$screen_name,
				$photo_50,
				$photo_200;
				
		$tempDebugMode = $debugMode; // Сохранение глобального значения во временную переменную
		$debugMode = false; // Внутренний вкл/выкл. режима отладки
				
		static $funcName = 'VkApi::LoginCode'; // Название функции для логов
		
			Debug::Addlog($funcName,'Начало работы функции.');
		//if (!empty($code)) { // Получен код авторизации
		if (!empty($_GET['code'])) { // Получен код авторизации
			$code = $_GET['code'];
				Debug::Addlog($funcName,'code » Получен код авторизации.');
				Debug::Addlog($funcName,'code » Выполняем запрос к серверу ВК.');
			$json = file_get_contents("https://oauth.vk.com/access_token?client_id=$vkApi_id&client_secret=$vkApi_key&code=$code&redirect_uri=".$_SERVER['SCRIPT_URI']); 
			if ($json) { // Получен корректный ответ от сервера ВК
					Debug::Addlog($funcName,'code » Код авторизации корректный.');
					Debug::Addlog($funcName,'code » Получен ответ от сервера ВК.');
				$obj = json_decode($json);
		
				// Получаем токен
				$access_token = $obj->{'access_token'};
					Debug::Addlog($funcName,'code » Получен токен доступа.');
	
				$apiResponse  = VkApi::api("users.get","fields=photo_200,photo_50,screen_name");
					Debug::Addlog($funcName,'code » Получены данные пользователя.');
				
				$valid_user	  = $apiResponse->response[0];
				$uid 		  = $apiResponse->response[0]->uid;
				$first_name	  = $apiResponse->response[0]->first_name;
				$last_name	  = $apiResponse->response[0]->last_name;
				$screen_name  = $apiResponse->response[0]->screen_name;
				$photo_50	  = $apiResponse->response[0]->photo_50;
				$photo_200	  = $apiResponse->response[0]->photo_200;
				
				// Регистрируем пользователя в сессии	
					Debug::Addlog($funcName,'code » Регистрируем пользователя в сессии.');
				unset( $_SESSION['valid_user'] );
				unset( $_SESSION['uid'] );
				unset( $_SESSION['first_name'] );
				unset( $_SESSION['last_name'] );
				unset( $_SESSION['screen_name'] );
				unset( $_SESSION['photo_50'] );
				unset( $_SESSION['photo_200'] );
				unset( $_SESSION['access_token'] );
				
				$_SESSION['valid_user']   = $valid_user;
				$_SESSION['uid'] 		  = $uid;
				$_SESSION['first_name']   = $first_name;
				$_SESSION['last_name']	  = $last_name;
				$_SESSION['screen_name']  = $screen_name;
				$_SESSION['photo_50'] 	  = $photo_50;
				$_SESSION['photo_200'] 	  = $photo_200;
				$_SESSION['access_token'] = $access_token;
				
				// Регистрируем пользователя в куках на 10 лет
					Debug::Addlog($funcName,'code » Регистрируем пользователя в куках.');
				setcookie("uid", $uid, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("first_name", $first_name, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("last_name", $last_name, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("screen_name", $screen_name, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("photo_50", $photo_200, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("photo_200", $photo_200, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("access_token", $access_token, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				
					Debug::Addlog($funcName,'code » Обновление страницы...<br>');
					$debugMode = $tempDebugMode; // Возвращение переменной глобального значения
				http_redirect($_SERVER['SCRIPT_URI']);
			}
			else { // Некорректный код авторизации
					Debug::Addlog($funcName,'!code » Ошибка! » Некорректный код авторизации. Завершение работы.<br>');
					$debugMode = $tempDebugMode; // Возвращение переменной глобального значения
				return false;
			}
		}
		elseif( !empty($_COOKIE['access_token']) && !empty($_COOKIE['uid']) ) { // Получены регистрационные данные из куков
			$access_token = $_COOKIE['access_token'];
				Debug::Addlog($funcName,'cookie » Получены регистрационные данные из куков.');
				Debug::Addlog($funcName,'cookie » Посылаем запрос на сервер ВК для проверки актуальности рег.данных.');
			$apiResponse  = VkApi::api("users.get","fields=photo_200,photo_50,screen_name");
			//$apiResponse = VkApi::curlPost("https://api.vk.com/method/users.get?fields=photo_200,screen_name&access_token=$access_token");
			//$cookiecheck = json_decode($apiResponse);
			if (isset($apiResponse->error)) { // Куки устарели
					Debug::Addlog($funcName,'!cookie » Ошибка! » Регистрационные данные устарели. Завершение работы.<br>');
					$debugMode = $tempDebugMode; // Возвращение переменной глобального значения
				http_redirect("https://oauth.vk.com/authorize?client_id=".$vkApi_id."&scope=photos,groups,offline,stats,audio,video,wall,docs&redirect_uri=".$_SERVER['SCRIPT_URI']."&response_type=code&v=5.27");
				//return $apiResponse;
				
			}
			else { // Куки актуальны
					Debug::Addlog($funcName,'cookie » Регистрационные данные актуальны.');
				$valid_user	  = $apiResponse->response[0];
				$uid 		  = $apiResponse->response[0]->uid;
				$first_name	  = $apiResponse->response[0]->first_name;
				$last_name	  = $apiResponse->response[0]->last_name;
				$screen_name  = $apiResponse->response[0]->screen_name;
				$photo_50	  = $apiResponse->response[0]->photo_50;
				$photo_200	  = $apiResponse->response[0]->photo_200;
				
				// Регистрируем пользователя в сессии	
					Debug::Addlog($funcName,'cookie » Регистрируем пользователя в сессии.');
				unset( $_SESSION['valid_user'] );
				unset( $_SESSION['uid'] );
				unset( $_SESSION['first_name'] );
				unset( $_SESSION['last_name'] );
				unset( $_SESSION['screen_name'] );
				unset( $_SESSION['photo_50'] );
				unset( $_SESSION['photo_200'] );
				unset( $_SESSION['access_token'] );
				
				$_SESSION['valid_user']   = $valid_user;
				$_SESSION['uid'] 		  = $uid;
				$_SESSION['first_name']   = $first_name;
				$_SESSION['last_name']	  = $last_name;
				$_SESSION['screen_name']  = $screen_name;
				$_SESSION['photo_50'] 	  = $photo_50;
				$_SESSION['photo_200'] 	  = $photo_200;
				$_SESSION['access_token'] = $access_token;
				
				// Регистрируем пользователя в куках на 10 лет
					Debug::Addlog($funcName,'cookie » Обновляем рег. данные в куках.');
				setcookie("uid", $uid, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("first_name", $first_name, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("last_name", $last_name, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("screen_name", $screen_name, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("photo_50", $photo_200, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("photo_200", $photo_200, time()+315360000, '/', $_SERVER['HTTP_HOST']);
				setcookie("access_token", $access_token, time()+315360000, '/', $_SERVER['HTTP_HOST']);
					Debug::Addlog($funcName,'cookie » Завершение работы функции.<br>');
					$debugMode = $tempDebugMode; // Возвращение переменной глобального значения
				return true;
			}
		}
		else { // Просмотр страницы без регистрации
				//Debug::Addlog($funcName,'!code & !cookie » Отсутствует код авторизации.');
				Debug::Addlog($funcName,'!code & !cookie » Работа в неавторизованном режиме. Завершение работы.<br>');
				$debugMode = $tempDebugMode; // Возвращение переменной глобального значения
			return false;
		}
	}
	
	/* 
		АБАЖУР
		
		Функция userLogout()
		Удаляет информацию о зарегистрированном пользователе Вконтакте из сессии и куков.
		
		Совместима с РЕЖИМОМ ОТЛАДКИ v.1.0. (см. debug.php)
		
		ВЕРСИИ:
			1.0. 2015.01.14.
	*/
	public function UserLogout() {
		
		global 	$vkUser, 
				$uid, 
				$first_name, 
				$last_name,
				$photo, 
				$photo_rec,
				$hash, 
				$debugMode,
				$debugLog;
	
		static 	$funcName = 'VkApi::UserLogout'; // Название функции для логов
		
		$tempDebugMode = $debugMode; // Сохранение глобального значения во временную переменную
		//$debugMode = false; // Внутренний вкл/выкл. режима отладки
		
		Debug::Addlog($funcName);
		Debug::Addlog($funcName,'Unregistering user from SESSION.');
		
		unset( $_SESSION['vkUser'] );
		unset( $_SESSION['uid'] );
		unset( $_SESSION['first_name'] );
		unset( $_SESSION['last_name'] );
		unset( $_SESSION['photo'] );
		unset( $_SESSION['photo_rec'] );
		
		Debug::Addlog($funcName,'Removing COOKIES.');
		setcookie("uid", '', time()+315360000, '/', $_SERVER['HTTP_HOST']);
		setcookie("hash", '', time()+315360000, '/', $_SERVER['HTTP_HOST']);
		
		Debug::Addlog($funcName,'Unsetting variables.');
		unset($vkUser);
		unset($uid);
		unset($first_name);
		unset($last_name);
		unset($photo);
		unset($photo_rec);
		unset($access_hash);
		
		Debug::Addlog($funcName,'Page refreshing...<br>');
		$debugMode = $tempDebugMode; // Возвращение переменной глобального значения
		//Debug::Printlog(); die;
		http_redirect($_SERVER['SCRIPT_URI']);
	}

	/* 
		АБАЖУР
		
		Функция UserAuth()
		выполняет авторизацию пользователя Вконтакте на сайте (логин/логаут)
	*/
	public function UserAuthWidget() {
		global $logout;
		
		if (isset($_GET['logout'])) {
			return self::UserLogout(); 
		}
		else {
			return self::UserLoginWidget();
		}
	}
	
}
?>