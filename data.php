<?
/*
	Определяет путь до корневой директории сервера.
	Его можно использовать для указания пути к ресурсам
	Пример использования:
		path_root().'styles/style.css'
*/
function path_root()
{
	$self = dirname(substr($_SERVER['SCRIPT_NAME'],1));
	if ($self == '.') $count = 0;
	else $count = count(explode('/',$self));
	for ($i=0; $i<$count; $i++)
	{
		$path .= '../';
	}
	return $path;
}

/*
	Формирует меню по папкам из корневого каталога на 2 уровня вниз.
		$menuConfig - название файла конфигурации меню. Название, приоритет, иконка
					  каждое значение с новой строки).
		$systemDirs - эта переменная содержит системные папки, находящиеся
					  в корневом каталоге, которые не учитываются при формировании меню.
	30.10.2014; 02.11.2014
*/
function menuTree($menuConfig,$systemDirs = NULL)
{
	global $defaultIcon;
	
	$docrootDirs = dirList( $_SERVER['DOCUMENT_ROOT'] );
	foreach ($docrootDirs as $docrootDir) 
	{	
		if ( glob($docrootDir.'index.php') )
		{ 
			// Ищем все пути к файлам с именем index.php на сайте
			$indexFile 		= glob($docrootDir.'index.php');
			
			// Вырезаем адрес из массива glob
			$indexValue 	= $indexFile[0];
			$indexFiles[] 	= $indexValue;
			
			// Обрезаем лишние части адреса строк
			$rootValue 		= substr ( $indexValue, strlen ($_SERVER['DOCUMENT_ROOT'])+1 ); 
			$indexDir 		= substr ( $rootValue, 0, strlen ($rootValue) - 9 );
			$indexDir 		= substr ( $indexDir, 0, strlen ($indexDir) - 1 );
			$indexDirs[] 	= $indexDir;
			
			// Удаляем исключения из переменной $systemDirs
			$hiddenDir 		= 0;
			foreach ($systemDirs as $systemDir)
			{
				if ( $systemDir == substr ($indexDir,0,strlen($systemDir)) ) { $hiddenDir = 1; }
			}
			if ($hiddenDir == 0) 
			{ 
				$menuDir	= $indexDir;
				$menuDirs[] = $menuDir; 
				
				// Разбиваем все пути в массивы с названием папок
				$tempoMenuArray[] 	= explode ('/', $menuDir);
			}
		}
	}
	
	//	Разбиваем массив на 3 массива menu, menu_links и submenu
	$menuLink = $tempoArray[0][0];
	$menuArray['menu'][] = $menuLink;
	foreach ($tempoMenuArray as $value)
	{
		if ( $menuLink != $value[0] )
		{
			$menuLink = $value[0];
			$menuArray['menu'][] = $menuLink;
		}
		if ( count($value) > 1 ) { $menuArray['submenu'][] = $value; }
		else { $menuArray['menu_links'][] = $value[0]; }
	}
	
	//return $menuArray;
	
	/*
		Формат нового массива с конфигурацией компонентов подменю:
		$structure Array 
		(
			[menu] => Array
			(
				[0] => Array
				(
					[path]	   => string
					[name]	   => string
					[priority] => int (1 -> 10)
					[icon] 	   => string
				)
			)
			[submenu] => Array
			(
				[0] => Array
				(
					[path1]	   => string
					[path2]	   => string
					[name] 	   => string
					[priority] => int (1 -> 10)
				)
			)
		)
	*/
	
	for ($i=0; $i<count($menuArray['menu']); $i++)
	{
		$sMenu = $menuArray['menu'][$i];
	
		// Переменные по-умолчанию
		$path	  = $sMenu;
		$name 	  = $sMenu;
		$priority = 1;
		$icon	  = $defaultIcon;
		
		// Читаем конфиг файл, если он есть в папке
		$searchName = glob ( $_SERVER['DOCUMENT_ROOT'].'/'.$sMenu.'/'.$menuConfig	);
		
		if ( count ($searchName)>0 )
		{
			$priority = 2;
			$pageConfig	= file_get_contents($searchName[0]);
			$pageConfig	= explode ( "\n",$pageConfig );
			for ($j=0; $j<count($pageConfig); $j++)
			{
				$pageConfig[$j] = trim ($pageConfig[$j]);
			}
			if ( isset($pageConfig[0]) )
			{
				if ( !empty($pageConfig[0]) ) $name = $pageConfig[0];
				
				if ( isset($pageConfig[1]) )
				{
					if ( !empty($pageConfig[1]) ) $priority = $pageConfig[1];
					
					if ( isset($pageConfig[2]) ) 
					{
						if ( !empty($pageConfig[2]) ) $icon = $pageConfig[2];
					}
				}
			}
		}
		$newStructure['menu'][$i] = array(
			'path' 		=> $path,
			'name' 		=> $name,
			'priority' 	=> $priority,
			'icon'	 	=> $icon
		);
		
		unset ($pageConfig);
		unset ($searchName);
	}

	for ($i=0; $i<count($menuArray['submenu']); $i++)
	{
		$sMenu = $menuArray['submenu'][$i];
	
		// Переменные по-умолчанию
		$path1	  = $sMenu[0];
		$path2 	  = $sMenu[1];
		$name 	  = $sMenu[1];
		$priority = 1;
		
		// Читаем конфиг файл, если он есть в папке
		$searchName = glob ( $_SERVER['DOCUMENT_ROOT'].'/'.$sMenu[0].'/'.$sMenu[1].'/'.$menuConfig	);
		
		if ( count ($searchName)>0 )
		{
			$priority = 2;
			$pageConfig	= file_get_contents($searchName[0]);
			$pageConfig	= explode ( "\n",$pageConfig );
			for ($x=0; $x<count($pageConfig); $x++)
			{
				$pageConfig[$x] = trim ($pageConfig[$x]);
			}
			if ( isset($pageConfig[0]) )
			{
				if ( !empty($pageConfig[0]) ) $name = $pageConfig[0];
	
				if ( isset($pageConfig[1]) )
				{
					if ( !empty($pageConfig[1]) ) $priority = $pageConfig[1];
				}
			}
		}
		$newStructure['submenu'][$i] = array(
			'path1' 	=> $path1,
			'path2' 	=> $path2,
			'name' 		=> $name,
			'priority' 	=> $priority
		);
		
		unset ($pageConfig);
		unset ($searchName);
	}
	
	$sortStructure['menu'] 		= array_sort ($newStructure['menu'], 'priority', SORT_DESC);
	$sortStructure['submenu'] 	= array_sort ($newStructure['submenu'], 'priority', SORT_DESC);
	
//		print('<pre>');
//		print_r ($sortStructure);
//		print('</pre>'); die;
	return $sortStructure;
}

/*
	Функция dirList(string $dir[, array $exc])
	возвращает массив со всеми каталогами в каталоге,
	указанном в переменной $dir одним массивом.
	Исключая каталоги с именем в переменной $exc,
	которые находятся в корневой папке и их подкаталоги.
	ДОРАБОТАНА 30.10.2014
	Добавлены исключения. 
*/
function dirList($dir, $exc=NULL)
{
	static $alldirs;
	
	$dirs = glob($dir.'*', GLOB_ONLYDIR);
	foreach ($dirs as $dirname)
	{
		// Проверка на исключения
		$match = false;
		$arrdir = explode ('/',$dirname);
		foreach ($exc as $exception) 
		{
			if ($arrdir[0] == $exception) $match = 1;
		}
		if (!$match) 
		{ 
			$dirname = $dirname.'/';
			$alldirs[] = $dirname;
			dirList($dirname,$exc); 
		}
	} 
	
	return $alldirs;
}

/*
	ДОРАБОТАННАЯ 10.12.2009г.
	Функция filelist(string $dir[, string $ext[, string $exc]])
	выводит все файлы по маске $ext (по умолчанию *, т.е. все файлы) в каталоге,
	указанном в переменной $dir	и во всех его подкаталогах одним массивом.
	Исключая файлы с расширением $exc (если $ext=='*').
*/
function fileList($dir=NULL, $ext='*', $exc=NULL)
{
	static $allfiles;
	$filemask = "*.$ext";
	$files = glob($dir.$filemask);
	if (count($files)>0)
	{
		foreach ($files as $file)
		{
			$ext_file = explode('.',basename($file));
			if ( $ext_file[1] != $exc ) $allfiles[] = $file;
		}
	}
	$dirs = glob($dir.'*', GLOB_ONLYDIR);
	if (count($dirs)>0)
	{
		foreach ($dirs as $dirname)
		{
			fileList($dirname.'/', $ext, $exc);
		} 
	}
	return $allfiles;
}

/*
	Функция daterus(int $unix[, int $openmode])
	возвращает дату в русском формате из метки времени Unix,
	указанной в переменной $unix.
	Дополнительная необязательная переменная может принимать значения:
	1 (ЧЧ.ММ.ГГГГг.);
	2 (Ч месяца ГГГГг.);
	3 (ЧЧ.ММ.ГГГГ);
	4 (месяц).
	По умолчанию она равна 1. Если аргумент неправильный, функция не выполняется.
*/

function daterus($unix, $openmode=1)
{
   $unix = (int)$unix;
   if ($unix)
   {
      $date_array = getdate($unix);
      $year = $date_array["year"];
      if ($openmode == 1)
      {
         $mon = $date_array["mon"];
         $day = $date_array["mday"];
         if ($mon < 10) $mon = "0".$mon;
         if ($day < 10) $day = "0".$day; 
         $daterus =  $day.'.'.$mon.'.'.$year.'г.';
		 return $daterus;     
      }
      else if ($openmode == 2)
      {
         $month = $date_array["month"];
	     switch ($month)
	     {
	        case 'January':   $month ='января';	break;
		    case 'February':  $month = 'февраля'; break;
		    case 'March':     $month = 'марта'; break;
		    case 'April':     $month = 'апреля'; break;
		    case 'May':       $month = 'мая'; break;
		    case 'June':      $month = 'июня'; break;
		    case 'July':      $month = 'июля'; break;
		    case 'August':    $month = 'августа'; break;
		    case 'September': $month = 'сентября'; break;
		    case 'October':   $month = 'октября'; break;
		    case 'November':  $month = 'ноября'; break;
		    case 'December':  $month = 'декабря'; break;
	     }
         $day = $date_array["mday"];
	     $daterus = $day.' '.$month.' '.$year.'г.';
		 return $daterus;
      }
      else if ($openmode == 3)
      {
         $mon = $date_array["mon"];
         $day = $date_array["mday"];
         if ($mon < 10) $mon = "0".$mon;
         if ($day < 10) $day = "0".$day; 
         $daterus =  $day.'.'.$mon.'.'.$year;
		 return $daterus;     
      }
	  else if ($openmode == 4)
	  {
	  	 $month = $date_array["month"];
	     switch ($month)
	     {
	        case 'January':   $month ='январь';	break;
		    case 'February':  $month = 'февраль'; break;
		    case 'March':     $month = 'март'; break;
		    case 'April':     $month = 'апрель'; break;
		    case 'May':       $month = 'май'; break;
		    case 'June':      $month = 'июнь'; break;
		    case 'July':      $month = 'июль'; break;
		    case 'August':    $month = 'август'; break;
		    case 'September': $month = 'сентябрь'; break;
		    case 'October':   $month = 'октябрь'; break;
		    case 'November':  $month = 'ноябрь'; break;
		    case 'December':  $month = 'декабрь'; break;
	     }
		 return $month;
	  }
	  else
      {
         echo 'Не выбран формат времени daterus()';     
      }
   }
   else
   {
      echo 'Нет метки времени daterus()';
   }
}

/*
	Simple function to sort an array by a specific key.
	http://ru2.php.net/manual/en/function.sort.php
	02.11.2014
*/
function array_sort($array, $on, $order=SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}
?>