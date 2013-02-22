<?


	/******************************************************************************************************
	*** Package: AVA-Panel Version 3.0
	*** Copyright (c) 2006, Anton A. Rassypaeff. All rights reserved
	*** License: GNU General Public License v3
	*** Author: Anton A. Rassypaeff | Рассыпаев Антон Александрович
	*** Contacts:
	***   Site: http://ava-panel.ru
	***   E-mail: manage@ava-panel.ru
	******************************************************************************************************/


class Library extends objectInterface{

									/*******************************************************************************************

																		Функции обработки паролей

										********************************************************************************************/

	public static function getPassHash($login, $pwd, $str){
		return sha1(sha1(strrev($str).'::'.$login.'::'.$pwd.'::'.$str)).sha1($pwd).sha1(strrev($pwd));
	}

	public static function crypt($str, $key = false, $interface = false){
		self::checkCryptPath();
		if(!$str) return $str;
		if($interface === false) $interface = defined('CRYPT_INTERFACE') ? CRYPT_INTERFACE : '';

		if($interface) $str = self::mcrypto($str, self::getKey($key), $interface);
		else $str = base64_encode($str);
		$str = str_replace('=', '', strrev(base64_encode($str)));
		return $str;
	}

	public static function decrypt($str, $key = false, $interface = false){
		self::checkCryptPath();
		if(!$str) return $str;
		if($interface === false) $interface = defined('CRYPT_INTERFACE') ? CRYPT_INTERFACE : '';

		$str = base64_decode(strrev($str));
		if($interface) $str = self::mcrypto($str, self::getKey($key), $interface, true);
		else $str = base64_decode($str);
		return trim($str);
	}

	private static function checkCryptPath(){
		$path2 = regExp::lower(getEnv('SCRIPT_FILENAME'));
		$parts = explode("/", $path2);
		$path1 = regExp::replace("|^.+".$parts[1]."|", '/'.$parts[1], regExp::replace(array("\\", "core/library.php"), array("/", ""), regExp::lower(trim(__FILE__))), true);

		if(!regExp::Match($path1, $path2)){
			die('Unable run encryption');
		}
	}

	private static function mcrypto($str, $secret, $interface, $de = false){
		if(!function_exists('mcrypt_module_open')){
			die('Mcrypto encryption algoritm is not allow in your server. Please, turn off it in settings.php file.');
		}

		$td = mcrypt_module_open($interface, '', MCRYPT_MODE_ECB, '');
		@mcrypt_generic_init($td, substr(md5($secret), 0, mcrypt_enc_get_key_size($td)), substr(md5(self::inventStr(rand(3, 7)), $secret), 0, mcrypt_enc_get_iv_size($td)));

		if(!$de) $str = mcrypt_generic($td, $str);
		else $str = mdecrypt_generic($td, $str);

		mcrypt_generic_deinit ($td);
		mcrypt_module_close ($td);
		return $str;
	}

	private static function getKey(){
		return sha1(AVA_KEY).md5(AVA_KEY.sha1(md5(AVA_KEY))).sha1(md5(AVA_KEY));
	}



								/*******************************************************************************************

												Функция генерирует случайную строку из букв и цифр

									********************************************************************************************/


	public static function inventStr($length = 9, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890', $max = false){
		if($max !== false) $length = rand($length, $max);
		$chars = regExp::utf8($chars);
		$l = strlen($chars) - 1;
		$return = '';

		for($i = 0; $i < $length; $i ++){
			$return .= $chars[rand(0, $l)];
		}

		return regExp::win($return);
	}

	public static function inventPass($length = 8, $lengthTo = 10, $vowed = false, $agree = false, $int = false, $other = array()){
		if($vowed === false) $vowed = array("a","e","i","o","u","y","A","E","I","O","U","Y");
		if($agree === false) $agree = array("b","c","d","f","g","h","j","k","l","m","n","p","q","r","s","t","v","w","x","z","B","C","D","F","G","H","J","K","L","M","N","P","Q","R","S","T","V","W","X","Z");
		if($int === false) $int = array("0","1","2","3","4","5","6","7","8","9");
		if($other === false) $other = array("!","@","#","$","%","^","&","*","(",")","_","-","+","=","}","{","[","]",";",":","/",".",",","<",">");

		$pwd='';
		$l = rand($length, $lengthTo);

		for($i = 1; $i <= $l; $i ++){
			if($other && !($i % 5)) $pwd .= self::arrayRand($other);
			elseif($int && !($i % 4)) $pwd .= self::arrayRand($int);
			elseif($vowed && !($i % 2)) $pwd .= self::arrayRand($vowed);
			elseif($agree) $pwd .= self::arrayRand($agree);

		}

		return $pwd;
	}

	public static function getUniqKey($arr, $len = 6){
		/*
			Придумывает ключ которого еще нет в массиве
		*/
		$key = Library::inventStr($len);

		if(!isset($arr[$key])){
			return $key;
		}

		return Library::getUniqKey($arr, $len);
	}


			/*********************************************************************************************************************************

														Сериализация / десериализация переменных

			***********************************************************************************************************************************/

	public static function serialize($var){
		return base64_encode(serialize($var));
	}

	public static function unserialize($var){
		if(empty($var)) return array();
		return unserialize(base64_decode($var));
	}

	public static function cmpSerialize($var){
		return base64_encode(gzcompress(Library::serialize($var)));
	}

	public static function cmpUnserialize($var){
		if(empty($var)) return array();
		return Library::unserialize(gzuncompress(base64_decode($var)));
	}

	public static function cmpStr($var){
		return base64_encode(gzcompress($var));
	}

	public static function uncmpStr($var){
		if(empty($var)) return '';
		return gzuncompress(base64_decode($var));
	}

	public static function arrKeys2str($arr, $sep = ','){
		/*
			Создает строку из массива пригодную для помещения в БД
		*/

		if(empty($arr) || !is_array($arr)) return '';

		$return = $sep;
		foreach($arr as $i => $e){
			if(!$e) continue;
			$return .= $i.$sep;
		}

		return $return;
	}

	public static function str2arrKeys($str, $sep = ','){
		$return = array();

		foreach(regExp::Split($sep, $str) as $e){
			if($e) $return[$e] = 1;
		}
		return $return;
	}

	public static function block2hash($str){
		/*
			Создает из блока данных вида
			a=100
			b=200
			c=300
			хеш
		*/

		$str = regExp::Split("\n", $str);
		$return = array();

		foreach($str as $i => $e){
			$pair = regExp::Split('=', $e, false, 2);
			if(count($pair) < 2) continue;
			$return[trim($pair['0'])] = trim($pair['1']);
		}
		return $return;
	}

	public static function hash2block($arr){
		/*
			Создает из хеша блок
		*/

		if(!is_array($arr)) return '';

		$return = '';
		foreach($arr as $i => $e){
			$return .= $i.'='.$e."\n";
		}

		return $return;
	}

	public static function list2hash($str){
		/*
			Создает из блока данных вида
			a1
			b1
			c1
			хеш a1=1, b1=1, c1=1
		*/

		$str = regExp::Split("\n", $str);
		$return = array();

		foreach($str as $e){
			$e = trim($e);
			if(!$e) continue;
			$return[trim($e)] = 1;
		}
		return $return;
	}

	public static function hash2list($arr){
		/*
			Создает из хеша блок
		*/

		if(!is_array($arr)) return '';

		$return = '';
		foreach($arr as $i => $e){
			$return .= $i."\n";
		}

		return $return;
	}

	public static function inArray($need, $arr, &$key = false){
		foreach($arr as $i => $e){
			if($e == $need){
				$key = $i;
				return true;
			}
		}

		return false;
	}

	public static function concatPrefixArray($arr, $pref = '', $postf = '', $type = 'recursion'){
		/*
			Возвращает массив где всем значениям добавлены префикс и постфикс
			type:
				recursion - для рекурсивной вставки
				ignoge - для игнорирования подмассивов
		*/

		$return = array();

		foreach($arr as $i => $e){
			if(is_object($e)) $return[$i] = $e;
			elseif(is_array($e)){
				if($type == 'recursion') $return[$i] = self::concatPrefixArray($e, $pref, $postf, $type);
				else $return[$i] = $e;
			}
			else $return[$i] = $pref.$e.$postf;
		}

		return $return;
	}

	public static function concatPrefixArrayKey($arr, $pref = '', $postf = '', $type = 'ignore'){
		/*
			то же но с ключами
		*/

		$return = array();
		foreach($arr as $i => $e){
			if(is_array($e) && $type == 'recursion') $e = self::concatPrefixArrayKey($arr, $pref, $postf, $type);
			$return[$pref.$i.$postf] = $e;
		}

		return $return;
	}

	public static function deconcatPrefixArrayKey($arr, $pref = '', $postf = '', $type = 'ignore'){
		/*
			Убирает префикс из ключа. Если его вообще не существует, значение в return не помещается
		*/

		$return = array();
		foreach($arr as $i => $e){
			if(($pref && regExp::Match("|^".$pref."|", $i, true)) || ($postf && regExp::Match("|".$postf."$|", $i, true))){
				if(is_array($e) && $type == 'recursion') $e = self::concatPrefixArrayKey($arr, $pref, $postf, $type);
				$return[regExp::replace(array("|^".$pref."|", "|".$postf."$|"), "", $i, true)] = $e;
			}
		}

		return $return;
	}

	public static function coordsBlock2Hash($str){
		/*
			Создает хеш из блока вида 10:10,20:30,40:50 массив:
			array(
				array('x' => 10, 'y' => 10)
				array('x' => 20, 'y' => 30)
				array('x' => 30, 'y' => 50)
			)
		*/

		$return = array();
		$str = regExp::Split(',', $str);

		foreach($str as $e){
			$e = regExp::Split(':', $e);
			$return[] = array('x' => trim($e['0']), 'y' => trim($e['1']));
		}

		return $return;
	}

	public static function hash2CoordsBlock($arr){
		/*
			Создает хеш из блока вида 10:10,20:30,40:50 массив:
			array(
				array('x' => 10, 'y' => 10)
				array('x' => 20, 'y' => 30)
				array('x' => 30, 'y' => 50)
			)
		*/

		$return = array();
		foreach($arr as $e){
			$return[] = $e['x'].':'.$e['y'];
		}

		return implode(',', $return);
	}

	public static function jsHash($arr, $ignoreEmptyArrays = false){
		/*
			Создает объект (хеш) для javascript из массива
		*/

		$return = array();
		foreach($arr as $i => $e){
			if($i === '') continue;

			if(is_array($e)){
				if(!$e && $ignoreEmptyArrays) continue;
				$e = self::jsHash($e);
			}
			elseif(is_bool($e)) $e = $e ? 'true' : 'false';
			elseif(!regExp::float($e)) $e = "'".addcslashes($e, "'")."'";
			$return[] = $i.": ".$e;
		}

		return '{'.implode(', ', $return).'}';
	}


			/*********************************************************************************************************************************

																	Работа с выводом

			***********************************************************************************************************************************/

	public static function getOutput(){
		/*
			Возвращает всю невыведенную инфу
		*/

		$return = '';
		while(($buff = ob_get_contents()) !== false){
			$return .= $buff;
			ob_end_clean();
		}

		return $return;
	}



			/*********************************************************************************************************************************

														Конвертирует строку из кирилицы в транслит

			***********************************************************************************************************************************/

	public static function cyr2translit($str){
		return regExp::Replace(
			array("а","б","в","г","д","е","ё","ж","з","и","й","к","л","м","н","о","п","р","с","т","у","ф","х","ц","ч","ш","щ","ъ","ы","ь","э","ю","я","А","Б","В","Г","Д","Е","Ё","Ж","З","И","Й","К","Л","М","Н","О","П","Р","С","Т","У","Ф","Х","Ц","Ч","Ш","Щ","Ъ","Ы","Ь","Э","Ю","Я"),
			array("a","b","v","g","d","e","io","zh","z","i","j","k","l","m","n","o","p","r","s","t","u","f","h","ts","ch","sh","shch","","y","","eh","iu","ia","A","B","V","G","D","E","Io","Zh","Z","I","J","K","L","M","N","O","P","R","S","T","U","F","H","Ts","Ch","Sh","Shch","","Y","","Eh","Iu","Ia"),
			$str
		);
	}

	public static function translitName($str){
		/*
			Создает транслитное имя файла
		*/

		return regExp::replace( "/\W+/i", "_", self::cyr2translit($str), true );
	}

	function hex2num($num){
		/*
			Переводит значение числа в байт-октетах в нормальное русское число, мля
		*/

		$l = strlen($num);
		$nstr = '';
		for($i = 0; $i < $l; $i ++) $nstr .= str_pad(base_convert(ord($num[$i]), 10, 16), 2, '0', STR_PAD_LEFT);
		return base_convert('0x'.$nstr, 16, 10);
	}

	function num2hex($num, $len = 0){
		/*
			Переводит нормальное число в байт октет
		*/

		$len = $len * 2;
		$len2 = regExp::len($num);
		$len = $len2 > $len ? $len2 : $len;

		$hexnum = str_pad(base_convert($num, 10, 16), $len, '0', STR_PAD_LEFT);
		$return = '';

		for($i = 0; $i < $len; $i = $i + 2) $return .= chr(base_convert('0x'.$hexnum[$i].$hexnum[$i + 1], 16, 10));
		return $return;
	}



			/*********************************************************************************************************************************

														Работа с HTML-мнемониками

			***********************************************************************************************************************************/


	public static function htmlQuot($e){
		//Превращает кавычки в html-мнемоники

		$e = str_replace('"', '&quot;', $e);
		$e = str_replace("'", '&#039;', $e);

		return $e;
	}

	public static function htmlUnquot($e){
		//Превращает  html-мнемоники в кавычки

		$e = str_replace('&quot;', '"', $e);
		$e = str_replace('&#039;', "'", $e);

		return $e;
	}

	public static function htmlunentities($string){
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		$trans_tbl = array_flip($trans_tbl);
		return strtr($string, $trans_tbl);
	}




			/*********************************************************************************************************************************

														Работа с текстом

			***********************************************************************************************************************************/

	public static function text2Html($str){
		/*
			Преобразовывает просто текст в HTML:
				\n -> <br/>
				ссылки
				мфло
		*/

		$str = regExp::replace("\n", "<br/>", $str);
		$links = regExp::MatchAll("#\s(http|https)://\S+#is", $str);
		$emls = regExp::MatchAll("#\s(\S@\S)\s#is", $str);

		foreach($links['0'] as $i => $e){
			$e = trim($e);
			$str = regExp::replace($e, "<a href='".$e."'>".$e."</a>", $str);
		}

		foreach($emls['0'] as $i => $e){
			$e = trim($e);
			$str = regExp::replace($e, "<a href='mailto:".$e."'>".$e."</a>", $str);
		}

		return $str;
	}



	/* Функция выдает правильную приписку к числу, например отдаешь вариант N помидор и в зависимости от их числа она правильно переделает слово помидор
		$mode=куда присоединять num: 0-в начало, 1-в конец
		letter1 - 0(>0),5,6,7,8,9,10,11,12,13,14
		letter2 - 2,3,4
		letter3 - 1
		letter4 - 0(=0)
	*/

	public static function rightCase($letter1, $letter2, $letter3, $letter4, $num, $mode = ''){
		/*
			Правильный вариант написания
		*/

		$e = substr($num,-1);
		$e1 = substr($num,-2);
		if ((($e==0) && ($num!=0 || !$letter4)) || ($e==5) || ($e==6) || ($e==7) || ($e==8) || ($e==9) || ($e1==11) || ($e1==12) || ($e1==13) || ($e1==14)){
			$letter=$letter1;
		}
		elseif(($e==2) || ($e==3) || ($e==4)){
			$letter=$letter2;
		}
		elseif($e==1){
			$letter=$letter3;
		}
		elseif ($num==0){
			$letter=$letter4;
			return $letter;
		}
		else{
			return false;
		}

		$return = $mode ? ($mode == 2 ? $letter : ($letter." ".$num)) : ($num." ".$letter);
		return $return;
	}

	/*
		Функция делит строку пробелами
		max-мах число символов без пробела подряд
		step-шаг через который вставл. пробел
	*/

	public static function addspace($text, $max, $step){
		$a=preg_split("/[\t\r\n ]/",$text);
		$i=0;

		while(isset($a[$i])){
			if(strlen($a[$i])>$max){ $a[$i]=wordwrap($a[$i], $step, " ", 1); }
			$i++;
		}

		$text=implode(" ",$a);
		return $text;
	}


	/*
		Функция отрезает часть строки специф. длины или менее при этом не попадает внутрь html-тега и отрезает по пробел, не работает с отриц аргументами
		$str=строка
		$length=длина
		$start=стартовая позиция
		$min=минимально допустимая строка
	*/

	public static function mysubstr($str, $start, $length, $min){
		$substr=substr($str,$start,$length);
		if(($start<0) || ($length<0)) {
			return $substr;
		}

		$pos[]=strrpos($substr," ");
		$pos[]=strrpos($substr,"\n");
		$pos[]=strrpos($substr,"\t");
		$pos[]=strrpos($substr,"<")-1;
		$pos[]=strrpos($substr,">");

		sort($pos,SORT_NUMERIC);
		$poschr=$pos['4']+1;

		if($poschr<$min){ $substr=substr($str,$start,$length); }
		else{ $substr=substr($str,$start,$poschr); }

		return $substr;
	}

	public static function getUrlPart($url, $part = 'host'){
		/*
			Возвращает часть URL
		*/

		$url = parse_url($url);
		return empty($url[$part]) ? '' : $url[$part];
	}

	public static function parseStr($str){
		/*
			Преобрадовывает строку в массив. Возвращает результат
		*/

		parse_str($str, $return);
		$return = self::urldecodeArray($return);
		return $return;
	}

	public static function parseStrOneLevel($str){
		/*
			Делает parseStr при этом не создавая массивов
		*/

		$str = regExp::replace('&amp;', '&', $str);
		$str = regExp::split("&", $str);
		$return = array();

		foreach($str as $i => $e){
			$e = regExp::split("=", $e, false, 2);
			$return[$e[0]] = isset($e[1]) ? $e[1] : '';
		}

		return $return;
	}

	public static function deparseStr($data, $l = '', $r = '', $pref = ''){
		/*
			Преобрадовывает массив в строку GET-запроса. Возвращает результат
		*/

		$return = '';
		foreach($data as $i => $e){
			if(is_array($e)) $return .= self::deparseStr($e, '[', ']', $pref.$l.$i.$r);
			else $return .= $pref.$l.$i.$r.'='.urlencode($e)."&";
		}

		return $return;
	}

	public static function urldecodeArray($arr){
		/*
			Urldecode массив
		*/

		$return = array();
		foreach($arr as $i => $e){
			if(is_array($e)) $return[urldecode($i)] = self::urldecodeArray($e);
			else $return[urldecode($i)] = urldecode($e);
		}

		return $return;
	}

	public static function encodeUrl($str){
		/*
			Конвертиртирует строку на основании моих собственных установок
		*/

		return str_replace('%', ';', urlencode($str));
	}

	public static function decodeUrl($str){
		/*
			Конвертиртирует строку на основании моих собственных установок
		*/

		if(is_array($str)){
			$return = array();
			foreach($str as $i => $e){
				$return[$i] = self::decodeUrl($e);
			}

			return $return;
		}

		return urldecode(str_replace(';', '%', $str));
	}

	public static function getHtmlTagAttr($tag, $attr){
		/*
			Принимает текст html-тега. Ищет атрибут
		*/

		if(regExp::Match("|\s".$attr."=(\S?)|is", $tag, true,  true, $m)){
			$q = ($m[1] == '"' || $m[1] == "'") ? $m[1] : '';
			regExp::Match("|\s".$attr."=".$q."([^".$q."]*)".$q."|is", $tag, true, true, $m);
			return $m[1];
		}

		return false;
	}



			/********************************************************************************************

														Обработка чисел

			*********************************************************************************************/

	public static function byte2mb($num, $dcm = 0){
		if($num > 1000000000){
			return round($num / 1000000000, $dcm).'Gb';
		}
		elseif($num > 1000000){
			return round($num / 1000000, $dcm).'Mb';
		}
		elseif($num > 1000){
			return round($num / 1000, $dcm).'Kb';
		}
		else{
			return '{Call:Lang:core:core:bajt:'.Library::serialize(array($num)).'}';
		}
	}


			/********************************************************************************************

							Обработка денежных данных

			*********************************************************************************************/

	public static function humanCurrency($cur, $sep = ' '){
		/*
			Формирует человекопонятное представление валюты
		 */

		$cur = round($cur, 2);
		$ceil = 0;
		$fract = 0;

		$pair = regExp::Split(".", $cur);
		$ceil = $pair['0'];
		if(!empty($pair['1'])) $fract = $pair['1'];

		if(strlen($ceil) > 3){
			$tmp = strrev($ceil);
			$ceil = '';

			$j = 0;
			while($segm = substr($tmp, $j, 3)){
				$ceil .= $segm.$sep;
				$j = $j + 3;
			}

			$ceil = strrev($ceil);
		}

		if($fract) $ceil .= '.'.$fract;
		return $ceil;
	}

	public static function bankCurrency($sum){
		/*
			Формирует сумму с отбивкой копеек (2 знака) точкой
		*/

		$sum = explode('.', $sum);
		if(empty($sum['1'])) $sum['1'] = '0';
		$sum['1'] = str_pad($sum['1'], 2, '0');
		return $sum['0'].'.'.$sum['1'];
	}

	public static function nds($nds, $sum){
		/*
			Расчитывает НДС по ставке
		*/

		return $sum - round((100 * $sum) / (100 + $nds), 2);
	}

	public static function printMoney($sum, $cur, $coin = ''){
		$sum = self::num2str($sum);
		return $sum[0].$cur.' '.str_pad($sum[1], 2, '0', STR_PAD_LEFT).' '.$coin;
	}

	public static function num2str($num){
		$return = "";
		$fraction = intval(($num * 100 - intval($num) * 100));
		$num = intval($num);

		if($num >= 1000000000){
			$num2 = intval($num / 1000000000);
			$return .= self::numName($num2).self::rightCase('{Call:Lang:core:core:milliardov}', '{Call:Lang:core:core:milliarda}', '{Call:Lang:core:core:milliard}', '', $num2, 2)." ";
			$num %= 1000000000;
		}

		if($num >= 1000000){
			$num2 = intval($num / 1000000);
			$return .= self::numName($num2).self::rightCase('{Call:Lang:core:core:millionov}', '{Call:Lang:core:core:milliona}', '{Call:Lang:core:core:million}', '', $num2, 2)." ";
			$num %= 1000000;
		}

		if($num >= 1000){
			$num2 = intval($num / 1000);
			$return .= self::numName($num2, 'w').self::rightCase('{Call:Lang:core:core:tysiach}', '{Call:Lang:core:core:tysiachi}', '{Call:Lang:core:core:tysiacha}', '', $num2, 2)." ";
			$num %= 1000;
		}

		if($num != 0){
			$return .= self::numName(intval($num))." ";
		}

		return array($return, $fraction);
	}

	public static function numName($num, $mode = 'm'){
		/*
			Правильный вариант написания числа строкой
		*/

		$hang = array('', '{Call:Lang:core:core:sto}', '{Call:Lang:core:core:dvesti}', '{Call:Lang:core:core:trista}', '{Call:Lang:core:core:chetyresta}', '{Call:Lang:core:core:piatsot}', '{Call:Lang:core:core:shestsot}', '{Call:Lang:core:core:semsot}', '{Call:Lang:core:core:vosemsot}', '{Call:Lang:core:core:deviatsot}');
		$des = array('', '', '{Call:Lang:core:core:dvadtsat}', '{Call:Lang:core:core:tridtsat}', '{Call:Lang:core:core:sorok}', '{Call:Lang:core:core:piatdesiat}', '{Call:Lang:core:core:shestdesiat}', '{Call:Lang:core:core:semdesiat}', '{Call:Lang:core:core:vosemdesiat}', '{Call:Lang:core:core:devianosto}');
		$ed = array('', '{Call:Lang:core:core:odin}', '{Call:Lang:core:core:dva}', '{Call:Lang:core:core:tri}', '{Call:Lang:core:core:chetyre}', '{Call:Lang:core:core:piat}', '{Call:Lang:core:core:shest}', '{Call:Lang:core:core:sem}', '{Call:Lang:core:core:vosem}', '{Call:Lang:core:core:deviat}', '{Call:Lang:core:core:desiat}', '{Call:Lang:core:core:odinnadtsat}', '{Call:Lang:core:core:dvenadtsat}', '{Call:Lang:core:core:trinadtsat}', '{Call:Lang:core:core:chetyrnadtsa}', '{Call:Lang:core:core:piatnadtsat}', '{Call:Lang:core:core:shestnadtsat}', '{Call:Lang:core:core:semnadtsat}', '{Call:Lang:core:core:vosemnadtsat}', '{Call:Lang:core:core:deviatnadtsa}');
		$edw = array('', '{Call:Lang:core:core:odna}', '{Call:Lang:core:core:dve}');

		if($num < 0) $num = $num * -1;
		$return = '';

		if($num >= 100){
			$return .= $hang[intval($num / 100)].' ';
			$num %= 100;
		}

		if($num >= 20){
			$return .= $des[intval($num / 10)].' ';
			$num %= 10;
		}

		if(($num == 1 || $num == 2) && $mode == 'w') $return .= $edw[$num].' ';
		else $return .= $ed[$num].' ';

		return $return;
	}



			/********************************************************************************************

							Функции работы с массивами

			*********************************************************************************************/

	public static function isHash($arr){
		/*
			Определяет что переданный массив содержит нечисловые ключи
		*/

		if(!is_array($arr)) return false;

		foreach($arr as $i => $e){
			if(!is_numeric($i)){
				return true;
			}
		}

		return false;
	}

	public static function sortLen($arr, $trend = 'desc'){
		/*
			Сортирует массив по длине значений
		*/

		$l = array();
		$return = array();

		foreach($arr as $i => $e){
			if(is_array($e)) $l[$i] = count($e);
			elseif(is_object($e) || is_resource($e)) $l[$i] = 0;
			else $l[$i] = strlen($e);
		}

		if($trend == 'desc') arsort($l);
		else asort($l);

		foreach($l as $i => $e){
			$return[$i] = $arr[$i];
		}

		return $return;
	}

	public static function syncArraySeq($arr1, $arr2, $byKeys = false){
		/*
			Синхронизует последовательность пунктов из arr1 со значениями из $arr2
		*/

		if($byKeys) $arr2 = array_keys($arr2);

		$return = array();
		foreach($arr2 as $e){
			if(empty($arr1[$e])) continue;
			$return[$e] = $arr1[$e];
		}

		$return = self::array_merge($return, $arr1);
		return $return;
	}

	public static function array_del($arr1, $arr2){
		/*
			Функция возвращает массив из всех ключей arr1 из которых убрали ключи встреченные в arr2
		*/

		if(!is_array($arr2)){ return $arr1; }
		if(!is_array($arr1)){ return array(); }
		foreach($arr2 as $i=>$e){ unset($arr1[$i]); }
		return $arr1;
	}

	public static function arr2str($arr, $p = ""){
		/*
			Создает текстовое представление массива для файла настройки и т.п.
		*/

		$return = '';
		foreach($arr as $i => $e){
			if(is_array($e)) $e = self::arr2str($e, $p."\t");
			$return .= $p."\t'$i' => '$e',\n";
		}

		return "array(\n".$return.$p.')';
	}

	public static function arrEmpty($arr, $exIndex = array()){
		/*
			Проверяет что массив будет пустой после удаления всех $exIndex
		*/

		foreach($exIndex as $e){
			unset($arr[$e]);
		}

		return empty($arr);
	}


	/*
		Функция принимает массивы arr1 и arr2 с совпадающими ключами
		Возвращает массив, где значения arr1 стали ключами arr2, а значениями стали ключи совпадающие с arr1
		Кароче:
			$arr1=array("a"=>"alpha","b"=>"beta,"c"=>"","d"=>"delta");
			$arr2=array("a"=>"auto","b"=>"benzo,"d"=>"duron");

			$r=array_merge_with_transposition($arr1,$arr2);

		Выход:
			$r=array("alpha"=>"auto","beta"=>"benzo","delta"=>"duron");

		Ключи, встреченные только в одном из массивов игнорируются
		Пустые значения массива 1 удаляются
		При совпадении ключей последующие перезаписывают предыдущие
	*/
	public static function array_merge_with_transposition($arr1,$arr2){
		$r=array();

		foreach($arr1 as $i=>$e){
			if($e==''){ continue; }
			if(isset($arr2[$i])){
				$r[$e]=$arr2[$i];
			}
		}

		return $r;
	}

	public static function arrayRand($arr, &$index = false){
		$keys = array_keys($arr);
		return $arr ? $arr[$index = $keys[rand(0, count($keys) - 1)]] : NULL;
	}

	public static function hexRand($from, $to){
		$from = hexDec($from);
		$to = hexDec($to);
		return decHex(rand($from, $to));
	}

	public static function arrayMix($arr){
		/*
			Возвращает перемешанный массив
		*/

		srand((float)microtime() * 1000000);
		shuffle($arr);
		return $arr;
	}


	/*
		Функция производит слияние массивов
		Если имеются общие ключи, а значения не являются массивами, они перезаписываются, при этом превалирует значение второго массива, иначе массивы сливаются
		Если операнды не являются массивами, они превращаются в числовой массив
	*/

	public static function array_merge($arr1, $arr2, $depth = false, $level = 0, $append = false){
		if(!is_array($arr1)){
			if($arr1){ $arr1 = array($arr1); }
			else{ $arr1 = array(); }
		}

		if(!is_array($arr2)){
			if($arr2){ $arr2 = array($arr2); }
			else{ $arr2 = array(); }
		}

		//Находим общие ключи
		foreach($arr2 as $i => $e){
			if(isset($arr1[$i]) && (is_array($e) || (is_array($arr1[$i]) && $append)) && (($level < $depth) || $depth === false)){
				$arr1[$i] = library::array_merge($arr1[$i], $e, $depth, ($level + 1), $append);
				continue;
			}
			elseif(isset($arr1[$i]) && $append){
				$arr1[]=$e;
				continue;
			}

			$arr1[$i]=$e;
		}

		return $arr1;
	}

	public static function array_merge_numeric($arr1, $arr2){
		/*
			Возвращает числовой массив. Все ключи стираются
		*/

		if(!is_array($arr1)) throw new AVA_Exception('{Call:Lang:core:core:neiavliaetsi1:'.Library::serialize(array($arr1)).'}');
		if(!is_array($arr2)) throw new AVA_Exception('{Call:Lang:core:core:neiavliaetsi1:'.Library::serialize(array($arr2)).'}');

		$return = array();
		foreach($arr1 as $i => $e) $return[] = $e;
		foreach($arr2 as $i => $e) $return[] = $e;

		return $return;
	}

	public static function arrayMergeWithPrefix($arr1, $arrs){
		/*
			Добавляет в массив 1 значения из 2 с использованием префиксов
		*/

		foreach($arrs as $i => $e){
			foreach($e as $i1 => $e1){
				$arr1[$i.$i1] = $e1;
			}
		}

		return $arr1;
	}

	public static function arrayFill($arr, $value){
		/*
			Сохраняет ключи массива $arr, но значения делает value
		*/

		$return = array();
		foreach($arr as $i => $e){
			$return[$i] = $value;
		}

		return $return;
	}

	public static function arrayValues($arr1, $arr2){
		/*
			Возвращает из $arr2 только значения представленные в arr1
		*/

		$return = array();
		foreach($arr1 as $e){
			if(isset($arr2[$e])) $return[$e] = $arr2[$e];
		}

		return $return;
	}

	public static function firstKey($arr){
		/*
			Возвращает первый ключ в массиве
		*/

		foreach($arr as $i => $e){
			return $i;
		}

		return false;
	}

	public static function lastKey($arr){
		/*
			Возвращает последний ключ в массиве
		*/

		foreach(array_reverse($arr, true) as $i => $e){
			return $i;
		}

		return false;
	}

	public static function arrayValues2keys($arr, $value = false){
		/*
			Преобразует значения массива в ключи
		*/

		$return = array();
		foreach($arr as $i => $e){
			$return[$e] = $value === false ? $i : $value;
		}

		return $return;
	}


	/*
		Функция отделяет от массива начало до специфицированного ключа
	*/
	public static function array_fission($arr, $key){
		foreach($arr as $i=>$e){
			if($i==$key){ break; }
			$r[$i]=$e;
		}

		return $r;
	}

	public static function getEmptyIndex($arr, $index, $step = 1){
		/*
			Возвращает ближайший свободный числовой индекс
		*/

		while(isset($arr[$index])){
			$index = $index + $step;
		}

		return $index;
	}

	public static function getEmptyHashIndex($arr, $index, $step = 1, $start = ''){
		/*
			Возвращает ближайший свободный числовой индекс
		*/

		while(isset($arr[$index.$start])){
			$start += $step;
		}
		return $index.$start;
	}

	public static function arr_dump($arr, $pref = '', $pref2 = '--'){
		foreach($arr as $i => $e){
			if(is_object($e)) echo $pref."$i => Object <br />\n";
			else{
				echo $pref."$i => $e <br />\n";
				if(is_array($e)){
					Library::arr_dump($e, $pref.$pref2, $pref2);
				}
			}
		}
	}

	public static function callClass($class, $func, $params = '', $callArray = false){
		/*
			Обращается к методу
		 */

		return $callArray ? call_user_func_array(array($class, $func), $params) : call_user_func(array($class, $func), $params);
	}

	public static function versionCompare($ver1, $ver2){
		/*
			Сравнивает версии. Если ver1 <= ver2 возвращает true, иначе false
		*/

		$ver1 = explode('.', $ver1);
		$ver2 = explode('.', $ver2);
		$base = count($ver2) > count($ver1) ? $ver2 : $ver1;

		foreach($base as $i => $e){
			if(!isset($ver1[$i])) $ver1[$i] = 0;
			if(!isset($ver2[$i])) $ver2[$i] = 0;
			if($ver1[$i] < $ver2[$i]) return true;
			elseif($ver1[$i] > $ver2[$i]) return false;
		}

		return true;
	}

	public static function shell($cmd){
		/*
			Запускае $cmd в shell
		*/
	}

	public static function shellPhp($file, $args = ''){
		/*
			Запускает PHP-файл в шелл
		*/

		if($args && is_array($args)) $args = implode(' ', $args);
		if(function_exists('shell_exec')) return shell_exec('/usr/local/bin/php '.$file.' '.$args);
		throw new AVA_Exception('{Call:Lang:core:core:nevozmozhnoz2:'.Library::serialize(array($file)).'}');
	}

	public static function constVal($const){
		if(!defined($const) || !constant($const)) return false;
		return constant($const);
	}


			/********************************************************************************************

							Функции работы с URL

			*********************************************************************************************/

	public function array2url($url, $disallow = array()){
		/*
			Создает URL из массива
		*/

		$return = '';
		foreach($url as $i => $e){
			if(!empty($disallow[$i])) continue;

			if(is_array($e)){
				foreach($e as $i1 => $e1){
					$e1 = self::encodeUrl($e1);
					$return .= "{$i}[{$i1}]={$e1}&";
				}

				continue;
			}

			$e = self::encodeUrl($e);
			$return .= "{$i}={$e}&";
		}

		return $return;
	}
}

?>