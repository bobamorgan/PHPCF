<?
/*
	Функция debug(mixed $var[, bool $mode = false])
	Отображает техническую информацию.
		$var - основной параметр функции. Его возможные значения:
			'logtime' или пусто	- на экран выводится время в микросекундах, необходимое 
								  для загрузки фрагмента страницы, на котором была вызвана функция.
			Любая переменная	- на экран выводится содержимое переменной
		$return - режим работы функции
			false	- функция выводит на экран данные
			true	- функция возвращает данные вместо вывода на экран
	Дата создания: 27.10.2014
	Дата модификации ($return): 27.12.2014
*/
function debug($var = 'logtime',$return = false) {
	if ($var == 'logtime') {
		global $START_TIME;
	
		if (!$START_TIME) { $START_TIME = microtime(TRUE); }
		else {
			$scriptTime = microtime(TRUE) - $START_TIME;
			$scriptTime = number_format($scriptTime*1000, 3, ',', ' ');
			if ($return == false) { print ($scriptTime.' ms'); }
			elseif ($return == true) { return ($scriptTime.' ms'); }
		}
	}
	else {
		if ($return == false) {
			if (!empty($var)) {
				?>
				<section class="panel">
				  <header class="panel-heading font-bold">Содержимое переменной</header>
				  <div class="panel-body">
					<pre><?
				print_r($var);
				?>
					</pre>
				  </div>
				</section>
				<?
			}
		}
		elseif ($return == true) {
			return ( var_export($var,true) );
		}
	}
}

/*
	Выводит содержимое данных, отправленных из формы, в удобочитаемом формате Bootstrap
	Дата создания: 29.10.2014
*/
function debug_showGlobals()
{
	if (!empty($_POST)) {
		?>
		<section class="panel">
		  <header class="panel-heading font-bold">Содержимое $_POST</header>
		  <div class="panel-body">
            <pre><? print_r($_POST); ?>
            </pre>
		  </div>
		</section>
		<?
	}
	if (!empty($_GET)) {
		?>
		<div class="panel">
		  <header class="panel-heading font-bold">Содержимое $_GET</header>
		  <div class="panel-body">
			<pre><? print_r($_GET); ?>
			</pre>
		  </div>
		</div>
		<?
	}
}

/*
	Выводит имя переменной
	Взял тут http://rsdn.ru/forum/web/3560073.all<br>
	Дата создания: 29.10.2014
*/
function varName(&$var)
{	
	$old = $var;	
	$var = md5(mt_rand(0, 999999))."_".$var; // Временно изменяем значение на случай, если есть несколько переменных с одинаковым значением
	$name= array_search($var, $GLOBALS);	
	$var = $old;
	return $name;
}
?>