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

if(function_exists('mb_regex_encoding')){
	mb_internal_encoding('UTF-8');
	define('MB_LOAD', 1);
}
else define('MB_LOAD', 0);


class regExp extends objectInterface {
	/*
		Класс предназначен для проведения проверок соответствия текста регулярному выражению
	*/

	/*******************************************************************************************************************************************************

													Проверка на соответствие определенному стандарту

	*******************************************************************************************************************************************************/

	public static function ip($str){
		/*
			Проверяет то что текст может быть IP-адресом
		*/

		return preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/iU", $str);
	}

	public static function folder($str){
		/*
			Проверяет то что текст может быть именем файла или директории
		*/

		return preg_match("/^[A-Za-z0-9А-Яа-яЁё\-_\.]+$/iUs", $str);
	}

	public static function digit($str){
		/*
			Проверяет то что текст является целым числом, в т.ч. и отрицательным
		*/

		return preg_match("/^\-?\d+$/iUs", $str);
	}

	public static function float($str){
		/*
			Проверяет то что текст является целым или дробным числом, в т.ч. и отрицательным
		*/

		if(preg_match("/^\-?\d+\.\d+$/iUs", $str)){
			return true;
		}

		return regExp::digit($str);
	}

	public static function alNum($str){
		/*
			Проверяет то что строка является набором цифр, символов латинского алфавита или знаком подчеркивания
		*/

		return preg_match("/^\w+$/iUs", $str);
	}

	public static function ident($str){
		/*
			Проверяет то что строка может быть идентификатором, т.е. состоит из букв, цифр и знака _ и начинается с буквы
		*/

		return preg_match("/^[A-Za-z]{1}\w{1,31}$/iUs", $str);
	}

	public static function calendar($str){
		return self::digit($str);
	}

	public static function gap($str){
		if(self::float($str)) return true;
		return false;
	}

	public static function space($str){
		/*
			Проверяет что строка - это только пробелы
		*/

		return !trim($str);
	}

	public static function printed($str){
		/*
			Проверяет что $str - это печатаебельные данные
		*/

		return !(is_array($str) || is_object($str) || is_resource($str));
	}

	public static function commaLine($str){
		/*
			Проверяет что строка является цифрами, разделенными запятой
		*/

		$str = explode(',', $str);
		foreach($str as $i => $e){
			if($e === '') return false;
			if(!regExp::digit($e)) return false;
		}

		return true;
	}

	public static function hashBlockFloat($str){
		/*
			Проверяет что это блок пригодный для хеширования, при этом что значения - float, а индекс - digit
		*/

		$str = explode("\n", $str);
		foreach($str as $i => $e){
			$e = trim($e);
			if(!$e) continue;

			$pair = explode("=", $e, 2);
			if(!self::digit(trim($pair['0'])) || !self::float(trim($pair['1']))) return false;
		}

		return true;
	}

	public static function pwd($str){
		/*
			Проверяет что в пароле есть только буквы, цифры и !@#$%^&*(){}[]:-_;.
		*/

		return preg_match("/^[A-Za-z0-9\!\@\#\$\%\^\&\*\(\)\{\}\[\]\:\-_\.]+$/iUs", $str);
	}

	public static function isAbsPath($str){
		/*
			Проверяет строка является абсолютным путем к файлу
		*/

		return preg_match("/^\//", $str);
	}

	public static function Time($str){
		/*
			Проверяет что строка является правильным значением времени ДД.ММ.ГГ либо ДД.ММ.ГГ ЧЧ:ММ либо ДД.ММ.ГГ ЧЧ:ММ:СС
		*/

		if(preg_match("/^\d{2,4}\.\d{2}\.\d{2,4}$/iUs", $str)) return true;
		elseif(preg_match("/^\d{2,4}\.\d{2}\.\d{2,4}\s+\d{2}:\d{2}$/iUs", $str)) return true;
		elseif(preg_match("/^\d{2,4}\.\d{2}\.\d{2,4}\s+\d{2}:\d{2}:\d{2}$/iUs", $str)) return true;

		return false;
	}

	public static function cronTime($str){
		/*
			Проверяет что указана правильная строка времени крона
		*/

		$str = trim($str);

		if($str == '*') return true;
		if(preg_match("/[^\d\-\,]/iUs", $str)) return false;
		if(preg_match("/^\d+$/", $str)) return true;

		if(strstr($str, ',,')) return false;
		if(strstr($str, '--')) return false;
		if(preg_match("/^\d[\d\,\-]+\d$/", $str)) return true;

		return false;
	}

	public static function date($str){
		/*
			Проверяет что строка - дата в формате ДД.ММ.ГГГГ
		*/

		return self::Match("/^\d{2}\.\d{2}\.\d{4}$/", $str, true);
	}

	public static function phone($str){
		/*
			Проверяет что строка - правильный номер телефона типа +7 495 1234567
		*/

		return self::Match("/^\+\d{1,5}\s\d{2,5}\s\d{4,8}$/", $str, true);
	}

	public static function url($str){
		/*
			Проверяет что строка - правильный URL
		*/

		return self::Match("|^[A-Za-z]+://\S+$|i", $str, true);
	}


	/*******************************************************************************************************************************************************

															Проверка на соответствие паттерну

	*******************************************************************************************************************************************************/

	public static function Match($need, $str, $isRegExp = false, $i = true, &$m = array()){
		/*
			Проверяет то что $need является подстрокой $str
			$isRegExp - указатель что рег. выражение
			$i - указатель что проверять в обоих регистрах
			$m - получает список совпадений если это регулярное выражение
		*/

		if($need == '') throw new AVA_Exception('Передана пустая строка');
		if($isRegExp){
			if(is_array($str) || is_array($need) || is_object($str) || is_object($need)) throw new AVA_Exception('{Call:Lang:core:core:massiviliobe}');
			return preg_match($need.'u', $str, $m);
		}
		elseif($i) return stristr($str, $need);
		else return strstr($str, $need);
	}

	public static function matchAll($pat, $str, $flags = false, $offset = 0, &$count = 0, $pos = false, &$all = array()){
		if($flags === false) $flags = PREG_PATTERN_ORDER;
		$count = preg_match_all($pat.'u', $str, $all, $flags, $offset);
		return $pos === false ? $all : (isset($all[$pos]) ? $all[$pos] : false);
	}

	public static function matchCount($pat, $str, $flags = false, $offset = 0){
		self::matchAll($pat, $str, $flags, $offset, $count);
		return $count;
	}

	public static function Split($separator, $str, $isRegExp = false, $limit = false, $i = true){
		/*
			Делит строку на участки и возвращает их
		*/

		if(is_array($separator) || is_array($str) || is_object($separator) || is_object($str)) throw new AVA_Exception('{Call:Lang:core:core:massiviliobe}');
		if(!$isRegExp){
			if($limit === false) return explode($separator, $str);
			else return explode($separator, $str, $limit);
		}
		else return preg_split($separator.'u', $str, $limit);
	}

	public static function Replace($search, $replace, $str, $isRegExp = false, $limit = -1, &$count = 0){
		/*
			Заменяет значения в строке или массиве строк
		*/

		if($isRegExp){
			if(is_array($search)) foreach($search as $i => $e) $search[$i] .= 'u';
			else $search .= 'u';
			return preg_replace($search, $replace, $str, $limit, $count);
		}
		else return str_replace($search, $replace, $str, $count);
	}

	public static function subStrReplace($str, $repl, $start, $len = false){
		$str = self::utf8($str);
		$repl = self::utf8($repl);
		if($len === false) return self::win(substr_replace($str, $repl, $start));
		else return self::win(substr_replace($str, $repl, $start, $len));
	}

	public static function stripNcTags($str){
		/*
			Удаляет теги nocall
		*/

		return str_replace(array('[nocall]', '[/nocall]'), '', $str);
	}

	public static function stripPhpComments($str){
		/*
			Удаляет камменты php из текста
		*/

		$str = regExp::Replace("|/\*.+\*/|iUs", "", $str, true);
		$str = regExp::Replace("|//([^\n'".'"'."]*)\n|iUs", "\n", $str, true);
		return $str;
	}

	public static function ReplaceCallback($pattern, $callback, $subj, $limit = -1){
		/*
			Преобразует по рег. выр. с callback функцией
		*/

		return preg_replace_callback($pattern, $callback, $subj, $limit);
	}

	public static function isExpr($str){
		/*
			Проверяет что строка - регулярное выражение. Возвращает true если она начинается с ограничителя |#/
		*/

		if(self::len($str) > 2 && ($str[0] == '|' || $str[0] == '#' || $str[0] == '/')) return true;
		return false;
	}

	public static function utf8($str){
		/*
			Кодирует строку UTF-8 в ASCII
		*/

		if(is_array($str)){
			foreach($str as $i => $e) $str[$i] = self::utf8($e);
			return $str;
		}
		elseif(is_object($str)){
			throw new AVA_Exception('{Call:Lang:core:core:massiviliobe}');
		}

		if(function_exists('mb_convert_encoding') && (!Library::constVal('ENCODE_EXT') || ENCODE_EXT == 'mb')) return @mb_convert_encoding($str, 'WINDOWS-1251', 'UTF-8');
		elseif(function_exists('iconv') && (!Library::constVal('ENCODE_EXT') || ENCODE_EXT == 'iconv')) return @iconv('UTF-8//IGNORE', 'WINDOWS-1251//IGNORE', $str);
		else return self::alternateCharset('utf-8', 'windows-1251', $str);
	}

	public static function win($str){
		/*
			Кодирует строку ASCII в UTF-8
		*/

		if(is_array($str)){
			foreach($str as $i => $e) $str[$i] = self::win($e);
			return $str;
		}
		elseif(is_object($str)){
			throw new AVA_Exception('{Call:Lang:core:core:massiviliobe}');
		}

		if(function_exists('mb_convert_encoding')) return  mb_convert_encoding($str, 'UTF-8', 'WINDOWS-1251');
		elseif(function_exists('iconv')) return iconv('WINDOWS-1251//IGNORE', 'UTF-8//IGNORE', $str);
		else return self::alternateCharset('windows-1251', 'utf-8', $str);
	}

	public static function charset($in, $out, $str){
		/*
			Кодирование между кадерофками
		*/

		$in = self::upper($in);
		$out = self::upper($out);
		if($in == $out) return $str;

		if(is_array($str)){
			$return = array();
			foreach($str as $i => $e) $return[$i] = self::charset($in, $out, $e);
		}
		else{
			if(function_exists('mb_convert_encoding')) return  mb_convert_encoding($str, $out, $in);
			elseif(function_exists('iconv')) $return = iconv($in.'//IGNORE', $out.'//IGNORE', $str);
			else $return = self::alternateCharset($in, $out, $str);
		}

		return $return;
	}

	public function alternateCharset($in, $out, $str){
		/*
			Кодирование из кодировки в кодировку без использования iconv
		*/

		$inCSN = self::cyrStrName($in);
		$outCSN = self::cyrStrName($out);

		if($inCSN == $outCSN) return $str;
		elseif($inCSN != 'u' && $outCSN != 'u') return convert_cyr_string($str, $inCSN, $outCSN);
		elseif($inCSN != 'u' && $inCSN != 'w') $str = convert_cyr_string($str, $inCSN, 'w');

		$_utf8win1251 = array(
			"\xD0\x90"=>"\xC0","\xD0\x91"=>"\xC1","\xD0\x92"=>"\xC2","\xD0\x93"=>"\xC3","\xD0\x94"=>"\xC4","\xD0\x95"=>"\xC5","\xD0\x81"=>"\xA8",
			"\xD0\x96"=>"\xC6","\xD0\x97"=>"\xC7","\xD0\x98"=>"\xC8","\xD0\x99"=>"\xC9","\xD0\x9A"=>"\xCA","\xD0\x9B"=>"\xCB","\xD0\x9C"=>"\xCC",
			"\xD0\x9D"=>"\xCD","\xD0\x9E"=>"\xCE","\xD0\x9F"=>"\xCF","\xD0\x20"=>"\xD0","\xD0\xA1"=>"\xD1","\xD0\xA2"=>"\xD2","\xD0\xA3"=>"\xD3",
			"\xD0\xA4"=>"\xD4","\xD0\xA5"=>"\xD5","\xD0\xA6"=>"\xD6","\xD0\xA7"=>"\xD7","\xD0\xA8"=>"\xD8","\xD0\xA9"=>"\xD9","\xD0\xAA"=>"\xDA",
			"\xD0\xAB"=>"\xDB","\xD0\xAC"=>"\xDC","\xD0\xAD"=>"\xDD","\xD0\xAE"=>"\xDE","\xD0\xAF"=>"\xDF","\xD0\x87"=>"\xAF","\xD0\x86"=>"\xB2",
			"\xD0\x84"=>"\xAA","\xD0\x8E"=>"\xA1","\xD0\xB0"=>"\xE0","\xD0\xB1"=>"\xE1","\xD0\xB2"=>"\xE2","\xD0\xB3"=>"\xE3","\xD0\xB4"=>"\xE4",
			"\xD0\xB5"=>"\xE5","\xD1\x91"=>"\xB8","\xD0\xB6"=>"\xE6","\xD0\xB7"=>"\xE7","\xD0\xB8"=>"\xE8","\xD0\xB9"=>"\xE9","\xD0\xBA"=>"\xEA",
			"\xD0\xBB"=>"\xEB","\xD0\xBC"=>"\xEC","\xD0\xBD"=>"\xED","\xD0\xBE"=>"\xEE","\xD0\xBF"=>"\xEF","\xD1\x80"=>"\xF0","\xD1\x81"=>"\xF1",
			"\xD1\x82"=>"\xF2","\xD1\x83"=>"\xF3","\xD1\x84"=>"\xF4","\xD1\x85"=>"\xF5","\xD1\x86"=>"\xF6","\xD1\x87"=>"\xF7","\xD1\x88"=>"\xF8",
			"\xD1\x89"=>"\xF9","\xD1\x8A"=>"\xFA","\xD1\x8B"=>"\xFB","\xD1\x8C"=>"\xFC","\xD1\x8D"=>"\xFD","\xD1\x8E"=>"\xFE","\xD1\x8F"=>"\xFF",
			"\xD1\x96"=>"\xB3","\xD1\x97"=>"\xBF","\xD1\x94"=>"\xBA","\xD1\x9E"=>"\xA2","\xD0\xA0"=>"\xD0","\xC2\xAB"=>"\xAB","\xC2\xBB"=>"\xBB",
			"\xE2\x84\x96"=>"\xB9","\xE2\x80\x9C"=>"\x22","\xE2\x80\x9D"=>"\x22",
		);

		$_win1251utf8 = array(
			"\xC0"=>"\xD0\x90","\xC1"=>"\xD0\x91","\xC2"=>"\xD0\x92","\xC3"=>"\xD0\x93","\xC4"=>"\xD0\x94","\xC5"=>"\xD0\x95","\xA8"=>"\xD0\x81",
			"\xC6"=>"\xD0\x96","\xC7"=>"\xD0\x97","\xC8"=>"\xD0\x98","\xC9"=>"\xD0\x99","\xCA"=>"\xD0\x9A","\xCB"=>"\xD0\x9B","\xCC"=>"\xD0\x9C",
			"\xCD"=>"\xD0\x9D","\xCE"=>"\xD0\x9E","\xCF"=>"\xD0\x9F","\xD0"=>"\xD0\x20","\xD1"=>"\xD0\xA1","\xD2"=>"\xD0\xA2","\xD3"=>"\xD0\xA3",
			"\xD4"=>"\xD0\xA4","\xD5"=>"\xD0\xA5","\xD6"=>"\xD0\xA6","\xD7"=>"\xD0\xA7","\xD8"=>"\xD0\xA8","\xD9"=>"\xD0\xA9","\xDA"=>"\xD0\xAA",
			"\xDB"=>"\xD0\xAB","\xDC"=>"\xD0\xAC","\xDD"=>"\xD0\xAD","\xDE"=>"\xD0\xAE","\xDF"=>"\xD0\xAF","\xAF"=>"\xD0\x87","\xB2"=>"\xD0\x86",
			"\xAA"=>"\xD0\x84","\xA1"=>"\xD0\x8E","\xE0"=>"\xD0\xB0","\xE1"=>"\xD0\xB1","\xE2"=>"\xD0\xB2","\xE3"=>"\xD0\xB3","\xE4"=>"\xD0\xB4",
			"\xE5"=>"\xD0\xB5","\xB8"=>"\xD1\x91","\xE6"=>"\xD0\xB6","\xE7"=>"\xD0\xB7","\xE8"=>"\xD0\xB8","\xE9"=>"\xD0\xB9","\xEA"=>"\xD0\xBA",
			"\xEB"=>"\xD0\xBB","\xEC"=>"\xD0\xBC","\xED"=>"\xD0\xBD","\xEE"=>"\xD0\xBE","\xEF"=>"\xD0\xBF","\xF0"=>"\xD1\x80","\xF1"=>"\xD1\x81",
			"\xF2"=>"\xD1\x82","\xF3"=>"\xD1\x83","\xF4"=>"\xD1\x84","\xF5"=>"\xD1\x85","\xF6"=>"\xD1\x86","\xF7"=>"\xD1\x87","\xF8"=>"\xD1\x88",
			"\xF9"=>"\xD1\x89","\xFA"=>"\xD1\x8A","\xFB"=>"\xD1\x8B","\xFC"=>"\xD1\x8C","\xFD"=>"\xD1\x8D","\xFE"=>"\xD1\x8E","\xFF"=>"\xD1\x8F",
			"\xB3"=>"\xD1\x96","\xBF"=>"\xD1\x97","\xBA"=>"\xD1\x94","\xA2"=>"\xD1\x9E","\xD0"=>"\xD0\xA0","\xAB"=>"\xC2\xAB","\xBB"=>"\xC2\xBB",
			"\xB9"=>"\xE2\x84\x96",
		);

		if($outCSN == 'u') $str = strtr($str, $_win1251utf8);
		elseif($inCSN == 'u'){
			$str = strtr($str, $_utf8win1251);
			if($outCSN != 'w' && $inCSN != 'u') $str = convert_cyr_string($str, 'w', $outCSN);
		}

		return $str;
	}

	private function cyrStrName($charset){
		/*
			Имя кодировки для convert_cyr_string
		*/

		switch(true){
			case stristr($charset, 'utf'): return 'u';
			case stristr($charset, 'koi'): return 'k';
			case stristr($charset, 'win'): return 'w';
			case stristr($charset, 'iso'): return 'i';
			case stristr($charset, '866'): return 'a';
			case stristr($charset, 'mac'): return 'm';
			default: throw new AVA_Exception('{Call:Lang:core:core:nepodderzhiv:'.Library::serialize(array($charset)).'}');
		}
	}

	public static function html($str, $nl2br = false, $symbols = '&$><{}'){
		/*
			Обрабатывает html-текст, делая его пригодным для отображения
		*/

		$return = array();
		if(is_array($str)){
			foreach($str as $i => $e) $return[$i] = self::html($e, $nl2br, $symbols);
		}
		else{
			$l = regExp::blen($symbols);
			$search = $repl = array();

			for($i = 0; $i < $l; $i ++){
				$search[$i] = $symbols[$i];
				$repl[$i] = '&#'.ord($search[$i]).';';
			}

			$return = regExp::Replace($search, $repl, $str);
			if($nl2br) $return = nl2br($return);
		}

		return $return;
	}

	public static function implode($str, $arr){
		return implode($str, $arr);
	}

	public static function Slashes($str, $chars = array("\\", "'")){
		if(is_array($str)){
			foreach($str as $i => $e){
				$str[$i] = self::Slashes($e, $chars);
			}

			return $str;
		}

		foreach($chars as $e){
			$str = str_replace($e, "\\".$e, $str);
		}

		return $str;
	}

	public static function stripSlashes($str){
		if(is_array($str)){
			foreach($str as $i => $e){
				$str[$i] = self::stripSlashes($e);
			}
		}
		else{
			$str = str_replace("\\\\", "====DUALSLASHES====", $str);
			$str = str_replace("\\", "", $str);
			$str = str_replace("====DUALSLASHES====", "\\", $str);
		}

		return $str;
	}

	public static function subStr($str, $start, $len = false){
		if(MB_LOAD){
			if($len === false) return mb_substr($str, $start);
			else return mb_substr($str, $start, $len);
		}
		else{
			$str = regExp::utf8($str);
			if($len === false) return regExp::win(subStr($str, $start));
			else return regExp::win(subStr($str, $start, $len));
		}
	}

	public static function subStrCount($str, $subStr){
		/*
			Считает число вхождений подстроки
		*/

		if(MB_LOAD) return mb_substr_count($str, $subStr);
		else return regExp::win(substr_count(regExp::utf8($str), regExp::utf8($subStr)));
	}

	public static function Len($str){
		if(MB_LOAD) return mb_strlen($str);
		else return strlen(regExp::utf8($str));
	}

	public static function bLen($str){
		/*
			Длина binary строки
		*/
		return strlen($str);
	}

	public static function URLParamsParse($url, $params, $leave = 0){
		$url = explode('/', $url);
		$return = array();

		foreach($params as $i => $e){
			$return[$i] = empty($url[$i + $leave]) ? '' : $url[$i + $leave];
		}
		return $return;
	}

	public static function fullUrlByRelative($base, $url){
		/*
			Возвращает полный URL по относительному и базе
		*/

		$url2 = parse_url($url);
		if(empty($url2['scheme'])){
			if(!regExp::Match("|^/|", $url, true)) $url = self::replace("|/[^/]*$|is", '/', $base, true).$url;
			else $url = self::replace("|^(\w+://[^/]+).*$|is", '$1', $base, true).$url;
		}

		return $url;
	}

	public static function lower($str){
		/*
			Преобразовывает строку в нижний регистр
		*/

		$return = self::win(strtolower(self::utf8($str)));
		$return = str_replace(
			array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'),
			array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'),
			$return
		);

		return $return;
	}

	public static function upper($str){
		/*
			Преобразовывает строку в верхний регистр
		*/

		$return = self::win(strtoupper(self::utf8($str)));
		$return = str_replace(
			array('а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'),
			array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я'),
			$return
		);

		return $return;
	}

	public static function uFirst($str){
		/*
			Переводит в верхний регистр первый символ
		*/

		$str = self::utf8($str);
		$str = preg_replace("|^".$str[0]."|", self::utf8(self::upper(self::win($str[0]))), $str, 1);
		return self::win($str);
	}

	public static function uWords($str){
		/*
			Переводит в верхний регистр первый символ всех слов
		*/

		return self::win(ucWords(self::utf8($str)));
	}

	public static function strIsStr($str1, $str2){
		/*
			Проверяет что строки равны без учета регистра
		*/

		$str1 = self::lower($str1);
		$str2 = self::lower($str2);
		return ($str1 == $str2);
	}

	public static function quot($str, $delimiter = "/"){
		return preg_quote($str, $delimiter);
	}
}

?>