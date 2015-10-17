<?
/*
	Выводит хэдер html кода
		$pageName - переменная, содержащая название текущей страницы.
		$vkApi_id - Переменная, производящая инициализацию Open API Вконтакте
					по ID приложения.
*/
function htmlHeader($pageName,$vkApi_id=FALSE)
{
	global $siteName,$imagePath;
	
//	debug();
	
// Присваиваем имя заголовку сайта
	$siteHeader = $siteName;
	if (isset($pageName))
		$siteHeader = $siteName.' | '.$pageName;
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
  <!--[if lt IE 9]>
    <script src="js/ie/respond.min.js" cache="false"></script>
    <script src="js/ie/html5.js" cache="false"></script>
    <script src="js/ie/fix.js" cache="false"></script>
  <![endif]-->
<?
if ($vkApi_id)
{
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
*/
function htmlFooter($javaScript)
{
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
}

/*
	Выводит боковое меню (слева) html кода
*/
function menuLeft () {
	global $systemDirs,$siteName,$smmUser;
	
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
	
	$structure = menuTree ($menuConfig,$systemDirs);
	
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
                <img src="<?=$smmUser['photo']?>" alt="" class="">
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
          <!-- note -->
          <div class="bg-danger wrapper hidden-vertical animated fadeInUp text-sm">            
              <a href="#" data-dismiss="alert" class="pull-right m-r-n-sm m-t-n-sm"><i class="fa fa-times"></i></a>
              <i class="fa fa-clock-o"></i> <?=debug()?>
          </div>
          <!-- / note -->
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
?>