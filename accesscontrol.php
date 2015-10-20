<?
/*
	ФУНКЦИИ УПРАВЛЕНИЯ ДОСТУПОМ К КОНТЕНТУ
*/
class AccessControl {
	/*
		Функция AccessCheck()
		Проверяет права доступа к контенту сайта
	*/
	public function AccessCheck() {
		
		global 	$accessControl,
				$visitorRights,
				$adminRights,
				$developerRights;
		
		$visitorRights	 = 0; // Посетитель сайта
		$adminRights 	 = 1; // Администратор контента
		$developerRights = 2; // Разработчик
		
		//print($_SESSION['user_id']); die;
		
		if( $accessControl ) {
			if( $_SESSION['uid'] == 2251846 ) {		// я
				return 2;
			}
			else return 0;
		} else return $developerRights;
	}
}
?>