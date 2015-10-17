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
	public function api($method, $param) {
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
	public function imgUploadAlbum($gid, $aid, $imageURL, $imageText) {
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
	public function imgUploadWall($gid, $imageURL) {
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
	public function imgSave($imageURL) {
		global $imagePath;
		global $User;
		
		$name = "image$User.jpg";
		$file = file_get_contents($imageURL);  
		$openedfile = fopen($imagePath.$name, "w");
		fwrite($openedfile, $file);
		fclose($openedfile);
		
		return $imagePath.$name;
	}

	public function wallPost($msgText, $attach) {
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
	public function groupsGet($user,$filter=false,$extended=0,$fields=false) {
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
	public function curlPost($url, $data=array()) {
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
}
?>