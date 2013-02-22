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


class Dates extends objectInterface {

	private static $termsList = array(
		'year' => '{Call:Lang:core:core:god}',
		'quarter' => '{Call:Lang:core:core:kvartal}',
		'month' => '{Call:Lang:core:core:mesiats}',
		'week' => '{Call:Lang:core:core:nedelia}',
		'day' => '{Call:Lang:core:core:den}',
		'hour' => '{Call:Lang:core:core:chas}',
		'minute' => '{Call:Lang:core:core:minuta}'
	);

	private static $termsListVariants = array(
		'year' => array(
			'0' => '{Call:Lang:core:core:god}',
			'1' => '{Call:Lang:core:core:goda}',
			'2' => '{Call:Lang:core:core:let}',
			'multi' => '{Call:Lang:core:core:goda}',
			'multi2' => '{Call:Lang:core:core:godakh}',
			'za' => '{Call:Lang:core:core:god}'
		),
		'quarter' => array(
			'0' => '{Call:Lang:core:core:kvartal}',
			'1' => '{Call:Lang:core:core:kvartala}',
			'2' => '{Call:Lang:core:core:kvartalov}',
			'multi' => '{Call:Lang:core:core:kvartaly}',
			'multi2' => '{Call:Lang:core:core:kvartalakh}',
			'za' => '{Call:Lang:core:core:kvartal}'
		),
		'month' => array(
			'0' => '{Call:Lang:core:core:mesiats}',
			'1' => '{Call:Lang:core:core:mesiatsa}',
			'2' => '{Call:Lang:core:core:mesiatsev}',
			'multi' => '{Call:Lang:core:core:mesiatsy}',
			'multi2' => '{Call:Lang:core:core:mesiatsakh}',
			'za' => '{Call:Lang:core:core:mesiats}'
		),
		'week' => array(
			'0' => '{Call:Lang:core:core:nedelia}',
			'1' => '{Call:Lang:core:core:nedeli}',
			'2' => '{Call:Lang:core:core:nedel}',
			'multi' => '{Call:Lang:core:core:nedeli}',
			'multi2' => '{Call:Lang:core:core:nedeliakh}',
			'za' => '{Call:Lang:core:core:nedeliu}'
		),
		'day' => array(
			'0' => '{Call:Lang:core:core:den}',
			'1' => '{Call:Lang:core:core:dnia}',
			'2' => '{Call:Lang:core:core:dnej}',
			'multi' => '{Call:Lang:core:core:dni}',
			'multi2' => '{Call:Lang:core:core:dniakh}',
			'za' => '{Call:Lang:core:core:den}'
		),
		'hour' => array(
			'0' => '{Call:Lang:core:core:chas}',
			'1' => '{Call:Lang:core:core:chasa}',
			'2' => '{Call:Lang:core:core:chasov}',
			'multi' => '{Call:Lang:core:core:chasy}',
			'multi2' => '{Call:Lang:core:core:chasakh}',
			'za' => '{Call:Lang:core:core:chas}'
		),
		'minute' => array(
			'0' => '{Call:Lang:core:core:minuta}',
			'1' => '{Call:Lang:core:core:minuty}',
			'2' => '{Call:Lang:core:core:minut}',
			'multi' => '{Call:Lang:core:core:minuty}',
			'multi2' => '{Call:Lang:core:core:minutakh}',
			'za' => '{Call:Lang:core:core:minutu}'
		)
	);

	private static $termsInSec = array(
		'year' => 31536000,
		'quarter' => 7948800,
		'month' => 2635200,
		'week' => 604800,
		'day' => 86400,
		'hour' => 3600,
		'minute' => 60,
		'second' => 1
	);

	private static $UTCList = array(
		-43200 => '{Call:Lang:core:core:gmtehnevetok}',
		-39600 => '{Call:Lang:core:core:gmtomiduehjs}',
		-36000 => '{Call:Lang:core:core:gmtgavaji}',
		-32400 => '{Call:Lang:core:core:gmtaliaska}',
		-28800 => '{Call:Lang:core:core:gmttikhookea}',
		-25200 => '{Call:Lang:core:core:gmtgornoevre}',
		-21600 => '{Call:Lang:core:core:gmttsentraln}',
		-18000 => '{Call:Lang:core:core:gmtvostochno}',
		-14400 => '{Call:Lang:core:core:gmtatlantich}',
		-12600 => '{Call:Lang:core:core:gmtniufaundl}',
		-10800 => '{Call:Lang:core:core:gmtbraziliia}',
		-7200 => '{Call:Lang:core:core:gmtsredneatl}',
		-3600 => '{Call:Lang:core:core:gmtazorskieo}',
		0 => '{Call:Lang:core:core:gmtkasablank}',
		3600 => '{Call:Lang:core:core:gmtamsterdam}',
		7200 => '{Call:Lang:core:core:gmtkievminsk}',
		10800 => '{Call:Lang:core:core:gmtbagdadehr}',
		12600 => '{Call:Lang:core:core:gmttegeran}',
		14400 => '{Call:Lang:core:core:gmtabudabiba}',
		16200 => '{Call:Lang:core:core:gmtkabul}',
		18000 => '{Call:Lang:core:core:gmtekaterinb}',
		19800 => '{Call:Lang:core:core:gmtbombejkal}',
		21600 => '{Call:Lang:core:core:gmtalmaatako}',
		25200 => '{Call:Lang:core:core:gmtbangkokkh}',
		28800 => '{Call:Lang:core:core:gmtirkutskpe}',
		32400 => '{Call:Lang:core:core:gmtosakasapp}',
		34200 => '{Call:Lang:core:core:gmtadelaidad}',
		36000 => '{Call:Lang:core:core:gmtkanberram}',
		39600 => '{Call:Lang:core:core:gmtmagadanno}',
		43200 => '{Call:Lang:core:core:gmtoklendfid}'
	);

	private static $UTC = false;


	public function UTCList(){
		/*
			Возвращает список GMT-зон
		*/

		return self::$UTCList;
	}

	public function UTCName($utc){
		/*
			Возвращает имя GMT-зоны по UTC
		*/

		return self::$UTCList[$utc];
	}

	public function getUTC(){
		/*
			Возвращает UTC текущего пользователя, либо "по умолчанию"
		*/

		if(!is_object($GLOBALS['Core'])) return $time;
		self::setUTCParams();
		return self::$UTC;
	}

	private function setUTCParams(){
		if(self::$UTC === false){
			if(is_object($GLOBALS['Core']->User) && isset($GLOBALS['Core']->User->params['utc'])) self::$UTC = $GLOBALS['Core']->User->params['utc'];
			else self::$UTC = $GLOBALS['Core']->getParam('UTC');
		}
	}

	public function date($format, $time = false, $utc = true){
		/*
			Возвращает дату
			Предполагается что передаваемое значение соответствует GMT, а отдается в соответствии с настройками пользователя
			Для получения русских дат:
				Ф - месяц полностью (напр. январь)
				ф - месяц полностью, род. падеж (напр. января, февраля и т.п.)
				М (рус.) - месяц короткий (напр. янв)
				л - день недели полностью (напр. пятница)
				Д - день недели 3 буквы (напр. птн)
				д - день недели 2 буквы (напр. пт)
				х - суффикс, например -ые, -ое и т.д.
				Ъ - месяц в шутку (пъянварь, фьювраль и т.п.)
				Ь - день недели падонк.

				!В квадратных скобках напр [Д]
		*/

		if($time === false) $time = time();
		$time = $time + ($utc === true ? self::getUTC() : 0);
		$return = gmDate($format, $time);

		if(regExp::Match('|\[[ФфМлДдЪЬ]\]|', $format, true)){
			$mth = date('n', $time);
			$dow = date('w', $time);

			$list = array(
				array('', '{Call:Lang:core:core:ianvar}', '{Call:Lang:core:core:fevral}', '{Call:Lang:core:core:mart}', '{Call:Lang:core:core:aprel}', '{Call:Lang:core:core:maj}', '{Call:Lang:core:core:iiun}', '{Call:Lang:core:core:iiul}', '{Call:Lang:core:core:avgust}', '{Call:Lang:core:core:sentiabr}', '{Call:Lang:core:core:oktiabr}', '{Call:Lang:core:core:noiabr}', '{Call:Lang:core:core:dekabr}'),
				array('', '{Call:Lang:core:core:ianvaria}', '{Call:Lang:core:core:fevralia}', '{Call:Lang:core:core:marta}', '{Call:Lang:core:core:aprelia}', '{Call:Lang:core:core:maia}', '{Call:Lang:core:core:iiunia}', '{Call:Lang:core:core:iiulia}', '{Call:Lang:core:core:avgusta}', '{Call:Lang:core:core:sentiabria}', '{Call:Lang:core:core:oktiabria}', '{Call:Lang:core:core:noiabria}', '{Call:Lang:core:core:dekabria}'),
				array('', 'пьянварь', 'фьювраль', 'хмарт', 'сопрель', 'сымай', 'теплюнь', 'жарюль', 'авгрусть', 'слюнтябрь', 'моктябрь', 'гноябрь', 'дубабрь'),
				array('', '{Call:Lang:core:core:ianv}', '{Call:Lang:core:core:fev}', '{Call:Lang:core:core:mar}', '{Call:Lang:core:core:apr}', '{Call:Lang:core:core:maj}', '{Call:Lang:core:core:iiun1}', '{Call:Lang:core:core:iiul1}', '{Call:Lang:core:core:avg}', '{Call:Lang:core:core:sen}', '{Call:Lang:core:core:okt}', '{Call:Lang:core:core:noia}', '{Call:Lang:core:core:dek}'),
				array('{Call:Lang:core:core:voskresene}', '{Call:Lang:core:core:ponedelnik}', '{Call:Lang:core:core:vtornik}', '{Call:Lang:core:core:sreda}', '{Call:Lang:core:core:chetverg}', '{Call:Lang:core:core:piatnitsa}', '{Call:Lang:core:core:subbota}'),
				array('{Call:Lang:core:core:vaskrisene}', '{Call:Lang:core:core:ponedelneg}', '{Call:Lang:core:core:vtorneg}', '{Call:Lang:core:core:sreda}', '{Call:Lang:core:core:chetverkh}', '{Call:Lang:core:core:piatnitstso}', '{Call:Lang:core:core:subboto}'),
				array('{Call:Lang:core:core:vsk}', '{Call:Lang:core:core:pnd}', '{Call:Lang:core:core:vtr}', '{Call:Lang:core:core:srd}', '{Call:Lang:core:core:chtv}', '{Call:Lang:core:core:ptn}', '{Call:Lang:core:core:sbt}'),
				array('{Call:Lang:core:core:vs}', '{Call:Lang:core:core:pn}', '{Call:Lang:core:core:vt}', '{Call:Lang:core:core:sr}', '{Call:Lang:core:core:cht}', '{Call:Lang:core:core:pt}', '{Call:Lang:core:core:sb}'),
			);

			$return = regExp::Replace(
				array('[Ф]', '[ф]', '[М]', '[л]', '[Д]', '[д]', '[Ъ]', '[Ь]'),
				array($list[0][$mth], $list[1][$mth], $list[3][$mth], $list[4][$dow], $list[6][$dow], $list[7][$dow], $list[5][$dow], $list[2][$mth]),
				$return
			);
		}

		return $return;
	}

	public function dateTime($time = false){
		/*
			Возвращает стандартный формат дата время
		*/

		return self::date($GLOBALS['Core']->getParam('dateFormat').' '.$GLOBALS['Core']->getParam('timeFormat'), $time);
	}

	public static function termsList(){
		/*
			Возвращает список всех базовых сроков
		*/

		return self::$termsList;
	}

	public static function termsListVars($term, $var = '0'){
		/*
			Возвращает правильный вариант написания срока
		*/

		if($term == '') throw new AVA_Exception('{Call:Lang:core:core:neustanovlen1}');
		return self::$termsListVariants[$term][$var];
	}

	public static function rightCaseTerm($base, $term, $mode = ''){
		//Возвращает правильный вариант написания срока

		if(!$base) throw new AVA_Exception('{Call:Lang:core:core:neustanovlen1}');
		return Library::rightCase(
			self::termsListVars( $base, 2 ),
			self::termsListVars( $base, 1 ),
			self::termsListVars( $base, 0 ),
			'',
			$term,
			$mode
		);
	}

	public static function termSec($term){
		/*
			Возвращает список всех базовых сроков
		*/

		return self::$termsInSec[$term];
	}

	public static function daysStay($term, $type = 'round'){
		/*
			Возвращает сколько дней до означенного срока
		*/

		return self::rightCaseTerm('day', $type(($term - time()) / 86400));
	}

	public static function sec2term($base, $sec, $round = 1){
		/*
			Переводит срок в секундах в нормальный, округляет в сторону увеличения
		*/

		if(!$base) throw new AVA_Exception('{Call:Lang:core:core:neustanovlen1}');
		if($sec == 0) return 0;
		return $round === false ? $sec / self::$termsInSec[$base] : round($sec / self::$termsInSec[$base], $round);
	}

	public static function term2sec($base, $term){
		/*
			Перевод нормального срока в секундный
		*/

		if(!$base) throw new AVA_Exception('{Call:Lang:core:core:neustanovlen1}');
		return self::$termsInSec[$base] * $term;
	}

	public static function termConvert($from, $to, $round = 1){
		/*
			Конвертирует из срока $from в срок $to
		*/

		return round((self::$termsInSec[$from] / self::$termsInSec[$to]), $round);
	}

	public static function intTime($date, $format = 'd1t1', $dlm = "/\s+/"){
		/*
			Преобразует дату в timestamp. Формат указывает на то в каком формате пришла дата
			d1 - d.m.Y, dmY и т.п.
			d2 - Ymd, Y-m-d и т.п.

			Предполагается что дата была введена по времени пользователя, а должна быть отдана по GMT
		*/

		if(!$date) return 0;
		$date = trim($date);

		regExp::Match("/^(d|t)?(\d*)(d|t)?(\d*)$/is", $format, 1, true, $m);
		$times = regExp::Split($dlm, $date, true, 2);

		if($m[1] == 't'){
			$times = array_reverse($times);
			$dateFormat = $m['4'];
			$timeFormat = $m['2'];
		}
		elseif($m[1] == 'd'){
			$dateFormat = $m['2'];
			$timeFormat = $m['4'];
		}

		$mth = date('m');
		$d = date('d');
		$y = date('Y');

		switch($dateFormat){
			case '1':
				regExp::Match("/^(\d{1,2})\D?(\d{1,2})\D?(\d{1,4})$/iUs", $times['0'], 1, true, $m);

				$mth = (int)$m['2'];
				$d = (int)$m['1'];
				$y = (int)$m['3'];

				break;

			case '2':
				regExp::Match("/^(\d{1,4})\D?(\d{1,2})\D?(\d{1,2})$/iUs", $times['0'], 1, true, $m);

				$mth = (int)$m['2'];
				$d = (int)$m['3'];
				$y = (int)$m['1'];

				break;

			default:
				return 0;
		}

		$h = $i = $s = 0;

		switch($timeFormat){
			case '1':
				regExp::Match("/^\[{0,1}(\d{1,2}):(\d{1,2}):(\d{1,2})\]{0,1}$/iUs", $times['1'], 1, true, $m);

				$h = (int)$m['1'];
				$i = (int)$m['2'];
				$s = (int)$m['3'];

				break;

			case '2':
				regExp::Match("/^\[{0,1}(\d{1,2}):(\d{1,2})\]{0,1}$/iUs", $times['1'], 1, true, $m);

				$h = (int)$m['1'];
				$i = (int)$m['2'];
				$s = 0;

				break;
		}

		return self::mkTime($h, $i, $s, $mth, $d, $y);
	}

	public static function mkTime($h = false, $i = false, $s = false, $m = false, $d = false, $y = false, $utc = true){
		/*
			Возвращает timestamp с поправкой на текущее серверное время, т.е., если все настроено правильно, возвращено будет timestamp по GMT
		*/

		if($h === false) $h = self::date('G', time(), $utc);
		if($i === false) $i = self::date('i', time(), $utc);
		if($s === false) $s = self::date('s', time(), $utc);

		if($m === false) $m = self::date('m', time(), $utc);
		if($d === false) $d = self::date('d', time(), $utc);
		if($y === false) $y = self::date('y', time(), $utc);

		return gmMkTime($h, $i, $s, $m, $d, $y) - ($utc === true ? self::getUTC() : 0);
	}

	public static function strTime($time = false){
		/*
			Возвращает время в RFC822 формате 12 Jun 2009 11:40:08 -0000
			Время возвращается по GMT, т.к. часовой поес золожен в строку
		*/

		if($time === false) $time = time();
		return self::date('r', $time);
	}
}

?>