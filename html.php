<?
/*
	Класс для вывода HTML кода сайта Абажур
*/
class Html {
	/*
		Определяет тип браузера
		Версии:
			1.0. 2015.01.14
	*/
	public function getBrowserType() {
    
		$browser = '';
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') ) {
			$browser = 'firefox';
		}
		elseif ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'op') ) {
			$browser = 'opera';
		}
		elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') || 
				 strpos($_SERVER['HTTP_USER_AGENT'], 'CriOS') ) {
			$browser = 'chrome';
		}
		elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') ) {
			$browser = 'safari';
		}
		elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0') ) {
			$browser = 'ie6';
		}
		elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0') ) {
			$browser = 'ie7';
		}
		elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0') ) {
			$browser = 'ie8';
		}
		elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0') ) {
			$browser = 'ie9';
		}
		elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 10.0') ) {
			$browser = 'ie10';
		}
		elseif ( strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7') ) {
			$browser = 'ie11';
		}
		return $browser;
	}
	
	/*
		Выводит хэдер html кода
			$pageName 		- переменная, содержащая название текущей страницы.
			$vkApi_id 		- Переменная, производящая инициализацию Open API Вконтакте
							  по ID приложения.
			$pageVersion	- Версия страницы
			Версии:
				1.0. 2015.10.17
				1.1. 2015.10.18 - Добавлена версия страницы
	*/
	public function Header( $pageName,$pageVersion=FALSE,$vkApi_id=FALSE ) {
		
		global 	$siteName,
				$imagePath,
				$accessRights,
				$debugMode;
	
		//	Debug::Checkpoint();
	
		// Присваиваем имя заголовку сайта
		$siteHeader = $siteName;
		if (isset($pageName)) {
			$siteHeader = $siteName.' | '.$pageName;
		}
		if ($pageVersion && $debugMode && $accessRights>=2) {
			$siteHeader = $siteHeader.' v.'.$pageVersion;
		}
		?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <title><?=$siteHeader?></title>
    <meta name="description" content="smm, реклама, вконтакте, реклама вконтакте, биржа рекламы" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link rel="shortcut icon" href="<?=$imagePath?>favicon.ico" type="image/png">
    <link rel="stylesheet" href="<?=path_root()?>css/bootstrap.css" type="text/css" />
    <link rel="stylesheet" href="<?=path_root()?>css/animate.css" type="text/css" />
    <link rel="stylesheet" href="<?=path_root()?>css/font-awesome.min.css" type="text/css" />
    <link rel="stylesheet" href="<?=path_root()?>css/font.css" type="text/css" cache="false" />
    <link rel="stylesheet" href="<?=path_root()?>css/plugin.css" type="text/css" />
    <link rel="stylesheet" href="<?=path_root()?>css/app.css" type="text/css" />
    <link rel="stylesheet" href="<?=path_root()?>css/extra.css" type="text/css" />
    <!--[if lt IE 9]>
        <script src="js/ie/respond.min.js" cache="false"></script>
        <script src="js/ie/html5.js" cache="false"></script>
        <script src="js/ie/fix.js" cache="false"></script>
    <![endif]-->
		<?
        if ($vkApi_id) {
            ?>
            <!-- Инициализация Open API Вконтакте -->
            <script src="//vk.com/js/api/openapi.js" type="text/javascript"></script>
            <script type="text/javascript">
                VK.init({
                apiId: <?=$vkApi_id?>});
            </script>
            <?
        }
		?>
</head>
<body>
	<section class="hbox stretch">
	<?
	}

	/*
		Выводит футер html кода
			string $javaScript - содержит массив с названиями JavaScript библиотек, которые необходимо подключить,
			в формате текстовой строки, содержащей названия библиотек, разделенных запятыми без пробелов.
			Пример: parsley,fckeditor,libmenu и т.п.
		Версии:
			1.0. 2015.10.17
	*/
	public function Footer( $javaScript ) {
		
		$javaLibs = explode(',',$javaScript);
		?>
    </section>
    <script src="<?=path_root()?>js/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="<?=path_root()?>js/bootstrap.js"></script>
    <!-- App -->
    <script src="<?=path_root()?>js/app.js"></script>
    <script src="<?=path_root()?>js/app.plugin.js"></script>
    <script src="<?=path_root()?>js/app.data.js"></script>
		<?
        foreach ($javaLibs as $jsLib) {
			if ($jsLib == 'parsley') {
				?>
				<!-- parsley -->
				<script src="<?=path_root()?>js/parsley/parsley.min.js" cache="false"></script>
				<script src="<?=path_root()?>js/parsley/parsley.extend.js" cache="false"></script>
				<?
			}
			if ($jsLib == 'tasks') {
				?>
				<!-- tasks -->
				<script src="<?=path_root()?>js/apps/tasks.js" cache="false"></script> 
				<?
			}
        }
  		?>
</body>
</html>
		<?
		die;		
	}
	
	/*
		Выводит боковое меню (слева) html кода
		Версии:
			1.0. 2015.10.17
	*/
	public function MenuLeft() {
		
		global 	$systemDirs,
				$siteName,
				$smmUser,
				$accessRights;
		
		// Иконка в меню по-умолчанию, если она не указана в файле конфигурации
		$defaultIcon = 'fa-question-circle';
		
		/*  
			Название файла с конфигурацией для каждого пункта меню. Он должен лежать в каждой папке,
			которая является пунктом меню. Он должен содержать несколько строк информации:
				1 строка - Название пункта меню.
				2 строка - Приоритет пункта меню (от 1 до 10). Он определяет место пункта в меню
						   чем выше приоритет, тем выше в меню этот пункт.
				3 строка - Иконка.
		*/
		$menuConfig	 = 'index.nam';
		
		$structure = menuTree( $menuConfig, $systemDirs );
		
		foreach ($structure as $name=>$value) {	$$name = $value; }
		
		?>
		<!-- .aside -->
		<aside class="bg-dark aside-sm" id="nav">
		  <section class="vbox">
			<!-- header -->
			<header class="dker nav-bar">
			  <a class="btn btn-link visible-xs" data-toggle="class:nav-off-screen" data-target="#nav">
				<i class="fa fa-bars"></i>
			  </a>
			  <a href="<?=$_SERVER["PHP_SELF"]?>" class="nav-brand"><?=$siteName?></a>
			  <a class="btn btn-link visible-xs" data-toggle="class:show" data-target=".nav-user">
				<i class="fa fa-comment-o"></i>
			  </a>
			</header>
			<!-- /header -->
			<section>
			<!-- user -->
			<div class="lter nav-user hidden-xs pos-rlt">            
				<div class="nav-avatar pos-rlt">
				  <a href="#" class="thumb-sm avatar animated rollIn" data-toggle="dropdown">
                    <img src="<?=$_SESSION['photo_rec']?>" alt="" class="">
					<span class="caret caret-white"></span>
				  </a>
				  <ul class="dropdown-menu m-t-sm animated fadeInLeft">
					<span class="arrow top"></span>
					<li>
					  <a href="#">Настройки</a>
					</li>
					<li>
					  <a href="<?=path_root()?>users/profile.php">Профиль</a>
					</li>
					<li>
					  <a href="#">
						<span class="badge bg-danger pull-right">3</span>
						Уведомления
					  </a>
					</li>
					<li class="divider"></li>
					<li>
					  <a href="<?=path_root()?>todo/docs.html">Помощь</a>
					</li>
					<li>
					  <a href="<?=path_root()?>?logout">Выйти</a>
					</li>
				  </ul>
				  <div class="visible-xs m-t m-b">
					<a href="#" class="h3"><?=$smmUser['first_name'].' '.$smmUser['last_name']?></a>
					<p><i class="fa fa-dot-circle-o"></i> $rights</p>
				  </div>
				</div>
				<div class="nav-msg">
				  <a href="#" class="dropdown-toggle" data-toggle="dropdown">
					<b class="badge badge-white count-n">2</b>
				  </a>
				  <section class="dropdown-menu m-l-sm pull-left animated fadeInRight">
					<div class="arrow left"></div>
					<section class="panel bg-white">
					  <header class="panel-heading">
						<strong>You have <span class="count-n">2</span> notifications</strong>
					  </header>
					  <div class="list-group">
						<a href="#" class="media list-group-item">
						  <span class="pull-left thumb-sm">
							<img src="<?=path_root()?>images/avatar.jpg" alt="John said" class="img-circle">
						  </span>
						  <span class="media-body block m-b-none">
							Use awesome animate.css<br>
							<small class="text-muted"><?=daterus(time(),3)?></small>
						  </span>
						</a>
						<a href="#" class="media list-group-item">
						  <span class="media-body block m-b-none">
							1.0 initial released<br>
							<small class="text-muted"><?=daterus(time(),3)?></small>
						  </span>
						</a>
					  </div>
					  <footer class="panel-footer text-sm">
						<a href="#" class="pull-right"><i class="fa fa-cog"></i></a>
						<a href="#">See all the notifications</a>
					  </footer>
					</section>
				  </section>
				</div>
			  </div>
			<!-- /user -->
			<!-- menu -->
			  <nav class="nav-primary hidden-xs">
				<ul class="nav"><?
		
		// ЦИКЛ МЕНЮ
		foreach ($menu as $men)
		{
			if ($i==0) { $menuString = ''; }
			else { $menuString = $men['path'].'/'; }
		
			// Маркер active
			$scriptDir = explode ('/', substr (dirname ($_SERVER['PHP_SELF']), 1) );
			if ($scriptDir[0] == $men['path'])
			{
				$markerActive = 'active';
		
			}
			
			// Маркер submenu
			foreach($submenu as $sub) if($men['path']==$sub['path1']) $submarker = 1;
			if ($submarker)
			{
				$markerSubmenu = 'dropdown-submenu';
				$openLink = '<a href="#" class="dropdown-toggle" data-toggle="dropdown">';
			}
			else
			{
				$openLink = '<a href="'.path_root().$men['path'].'">';
			}
			
			// Маркер Class
			if ( $markerActive || $markerSubmenu )
			{
					$markerClass = ' class="';
					if  ($markerSubmenu)
					{
						$markerClass .= $markerSubmenu;
						if ($markerActive)
						{
							$markerClass .= ' '.$markerActive;
						}
					}
					elseif ($markerActive)
					{
						$markerClass .= $markerActive;
					}
					$markerClass .= '"';
			}
			
		// HTML ОТКРЫТИЕ ПУНКТА МЕНЮ
			?>
					  <li<?=$markerClass?>>
						<?=$openLink?>  <i class="fa <?=$men['icon']?>"></i>
						  <span><?=$men['name']?></span></a><?
		
		// HTML ВЫВОД ПОДМЕНЮ
			if ($submarker)
			{
				?>
							<ul class="dropdown-menu"><?
				foreach ($submenu as $sub)
				{
					if ($sub['path1'] == $men['path'])
					{
						$submenuPath = path_root().$sub['path1'].'/'.$sub['path2'];
						?>
							  <li>
								<a href="<?=$submenuPath?>"><?=$sub['name']?></a>                    
							  </li><?
					}
				}
				?>
							</ul><?
			}
		// HTML ЗАКРЫТИЕ ПУНКТА МЕНЮ
			?>
					  </li><?
		
			unset($submarker);
			unset($markerClass);
			unset($markerSubmenu);
			unset($markerActive);
		}
		?>
				  <li>
					<a href="<?=path_root()?>todo/index.html">
					  <i class="fa fa-eye"></i>
					  <span>todo</span>
					</a>
				  </li>
				</ul>
			  </nav>
			  <?=Html::Checkpoint('note')?>
			</section>
			<!-- /menu -->
			<!-- footer -->
			<footer class="footer bg-gradient hidden-xs">
			  <a href="<?=$_SERVER['PHP_SELF']?>?logout" class="btn btn-sm btn-link m-r-n-xs pull-right">
				<i class="fa fa-power-off"></i>
			  </a>
			  <a href="#nav" data-toggle="class:nav-vertical" class="btn btn-sm btn-link m-l-n-sm">
				<i class="fa fa-bars"></i>
			  </a>
			</footer>
			<!-- /footer -->
		  </section>
		</aside>
		<!-- /.aside -->
		<?
	}

	/*
		Выводит стикер TODO с важным сообщением string $msg, который можно удалить крестиком в правом верхнем углу.
		Типы стикера, передаваемые в переменной:
			string $type принимает следующие значения:
				'warning' 	- желтый для привлечения внимания;
				'success' 	- зеленый для сообщений об успешном действии;
				'danger'	- красный для сообщений об ошибках;
				'info'		- синий для вывода окна с информацией, например help или faq
			string $headerText - если указана, выведет текстовый заголовок к стикеру.
			string $changeIcon - название иконки Font-Awesome, если указана, заменит иконку по-умолчанию.

		ВЕРСИИ:
			1.0. 2015.01.12
			1.1. 2015.10.18 - Добавлена возможность установки произвольной иконки
	*/	
	public function Alert($type='warning',$msg,$headerText=false,$changeIcon=false) {
		// Проверка на корректный тип
		if( $type == 'warning') {
			if ($changeIcon) { $headerIcon = $changeIcon; } 
			else { $headerIcon = 'fa-exclamation-triangle'; }
			//$headerText = 'Внимание!';
		}
		elseif( $type == 'success') {
			if ($changeIcon) { $headerIcon = $changeIcon; } 
			else { $headerIcon = 'fa-check-circle'; }
			//$headerText = 'Успех!';
		}
		elseif( $type == 'danger') {
			if ($changeIcon) { $headerIcon = $changeIcon; } 
			else { $headerIcon = 'fa-exclamation-circle'; }
			//$headerText = 'Ошибка!';
		}
		elseif( $type == 'info') {
			if ($changeIcon) { $headerIcon = $changeIcon; } 
			else { $headerIcon = 'fa-info-circle'; }
			//$headerText = 'Для информации.';
		}
		elseif( $type == 'note') {
			if ($changeIcon) { $headerIcon = $changeIcon; } 
			else { $headerIcon = 'fa-exclamation-circle'; }
			//$headerText = 'Не забыть!';
		}
		else return false;
		// Проверка на наличие сообщения
		if( !$msg ) {
			if( !$headerText ) {
				return false;
			}
		}
		// Вывод HTML
		if ($type == 'note') {
			?>
			<!-- note -->
			<div class="bg-danger wrapper hidden-vertical animated fadeInUp text-sm">            
				<a href="#" data-dismiss="alert" class="pull-right m-r-n-sm m-t-n-sm"><i class="fa fa-times"></i></a>
				<i class="fa <?=$headerIcon?>"></i> <?=$msg?>
			</div>
			<!-- / note -->
			<?
		}
		else {
			?>
			<div class="alert alert-<?=$type?> text-left animated fadeInDown">
				<button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
				<? if ($headerText) {
					?>
					<h4><i class="fa <?=$headerIcon?>"></i> <?=$headerText?></h4>
					<? echo $msg;
				} else {
					?><i class="fa <?=$headerIcon?>"></i> <? echo $msg;
				}
				?>
			</div>
			<?
		}
	}
	
	/*
		Выводит стикер TODO в формате ошибки с важным сообщением string $msg, который можно удалить крестиком в правом верхнем углу.
		
		ВЕРСИИ:
			1.0. 2015.01.12.
	*/	
	public function Error($msg) {
		// Проверка на наличие сообщения
		if (!$msg) return false;
		// Вывод HTML
		?>
        <div class="alert alert-danger text-left animated fadeInDown">
        	<button type="button" class="close" data-dismiss="alert"><i class="fa fa-times"></i></button>
            	<h4><i class="fa fa-exclamation-circle"></i> Ошибка!</h4>
        		<?=$msg?>
        </div>
		<?
	}
	
	/*
		Отображает страницу с ошибкой 404
		
		ВЕРСИИ:
			1.0. 2015.01.25.
	*/
	public function ShowError404() {
		?>
		<div class="panel wrapper">
			<div class="row m-n">
				<div class="text-center">
					<div class="text-center m-b-lg">
						<h1 class="h1-404 text-white animated bounceInDown">404</h1>
						<h3 class="m-t-sm h3-404">Страница не существует</h3>
					</div>
					<div class="list-group bg-white m-404">
						<a href="<?=path_root()?>index.php" class="list-group-item">
							<i class="fa fa-fw fa-home text-muted"></i> На главную
						</a>
						<a href="javascript:history.go(-1)" mce_href="javascript:history.go(-1)" class="list-group-item">
							<i class="fa fa-fw fa-reply text-muted"></i> Назад
						</a>
					</div>
				</div>
			</div>
		</div>
		<?
		// Вывод данных отладки
		Debug::Printlog();
		Debug::Showglobals();
		Html::Footer();
		exit;	
	}
	
	/*
		Выводит информацию о времени загрузки компонента в виде стикера TODO
		Типы стикера соответствуют описанным в функции Html::Alert
		
		ВЕРСИИ:
			1.0. 2015.10.18
	*/
	public function Checkpoint( $type='warning' ) {
		
		global 	$debugMode,
				$debugShowCheckpoints;

		if ($debugMode && $debugShowCheckpoints) Html::Alert($type,Debug::Checkpoint(true),false,'fa-clock-o');
	}
	
	/*
		Выводит страницу с контентом
		
		ВЕРСИИ:
			1.0. 2015.10.18
	*/
	public function ContentPage( $contentPage = NULL ) {
		
		global 	$pageName,
				$pageVersion,
				$debugMode,
				$debugShowCheckpoints,
				$accessRights,
				$varDump,
				$vkUser,
				$vkApi_id,
				$mysql_user,
				$mysql_password,
				$mysql_database;
	
		?>
		<!-- .vbox -->
		<section id="content">
      		<section class="vbox">
		<?
		if ($debugMode && $accessRights>=2) { require_once($_SERVER['DOCUMENT_ROOT'].'/debug/index.php'); }
		else { require_once($contentPage); }
		?>
			</section>
    	</section><!--// .vbox -->
		<?
	}
}
?>