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


class captcha extends objectInterface{
	/*
		Капча
	*/

	private $id;
	private $picParams = array();
	private $captchaStandart;
	private $params = array();
	public $image;

	public function __construct($id, $standart = false){
		if(!$id) $id = $this->newCode($standart);
		if(!$this->picParams = $GLOBALS['Core']->DB->rowFetch(array('captcha', '*', "`id`='$id'"))) throw new AVA_Exception('Captcha '.$id.' не найдено');
		$this->id = $id;
		$this->picParams['vars'] = $this->params = Library::unserialize($this->picParams['vars']);

		$this->captchaStandart = $this->picParams['captcha_standart'];
		$this->params = Library::array_merge($GLOBALS['Core']->getCaptchaParams($this->captchaStandart), $this->params);
		$this->image = new Image;
		$this->image->createImage(_W.$GLOBALS['Core']->getParam('captchaFolder').Library::arrayRand($this->params['backgrounds']));
	}


	/***************************************************************************************************************************************************************

																	Возвращает сведения о капче

	****************************************************************************************************************************************************************/

	public function __ava__getParam($param){
		return $this->params[$param];
	}

	public function __ava__getPicParam($param){
		return $this->picParams[$param];
	}

	public function __ava__getParams(){
		return $this->params;
	}

	public function __ava__getPicParams(){
		return $this->picParams;
	}

	public function __ava__getStandart(){
		return $this->captchaStandart;
	}

	public function __ava__getCode(){
		return $this->picParams['code'];
	}

	public function __ava__getId(){
		return $this->id;
	}


	/***************************************************************************************************************************************************************

																		Создание изображения

	****************************************************************************************************************************************************************/

	public function __ava__getImage(){
		/*
			Возвращает изображение с капчей
		*/

		$this->writeText($this->picParams['code'], $this->params['direction']);
		return $this->image;
	}

	public function writeText($text, $direct){
		$text = regExp::utf8($text);
		$len = regExp::bLen($text);
		$posH = $posHStart = rand($this->params['start_position'], $this->params['start_position_to']);
		$posV = $posVStart = rand($this->params['start_position_vertical'], $this->params['start_position_vertical_to']);

		if($direct == 'c' || $direct == 'd'){
			$centerX = $this->image->getX() / 2;
			$centerY = $this->image->getY() / 2;
			$step = 2 * pi() / ($len + 2);

			$rad = sqrt(pow($posH - $centerX, 2) + pow($posV - $centerY, 2));
			$cat = ($posH - $centerX > 0) ? ($posH - $centerX) : ($centerX - $posH);
			$ang = asin($cat / $rad);

			if($posH > $centerX && $posV > $centerY) $ang += pi() / 2;
			elseif($posH <= $centerX && $posV >= $centerY) $ang += pi();
			elseif($posH < $centerX && $posV <= $centerY) $ang += pi() / 2 * 3;
		}

		for($i = 0; $i < $len; $i ++){
			if($direct == 'r' || $direct == 'l'){
				$box = $this->writeChar(regExp::win($text[$i]), $posH, $posV, $direct, 't');
				$posH += $box[0] + rand($this->params['letter_offset'], $this->params['letter_offset_to']);
				$posV = $posVStart + rand($this->params['letter_vertical_offset'], $this->params['letter_vertical_offset_to']);
			}
			elseif($direct == 't' || $direct == 'b'){
				$box = $this->writeChar(regExp::win($text[$i]), $posH, $posV, 'l', $direct);
				$posH = $posHStart + rand($this->params['letter_offset'], $this->params['letter_offset_to']);
				$posV += $box[1] + rand($this->params['letter_vertical_offset'], $this->params['letter_vertical_offset_to']);
			}
			elseif($direct == 'c' || $direct == 'd'){
				$box = $this->writeChar(regExp::win($text[$i]), $posH, $posV, 'l', $direct);
				$direct == 'c' ? $ang += $step : $ang -= $step;
				$posH = $centerX + round($rad * sin($ang)) + rand($this->params['letter_offset'], $this->params['letter_offset_to']);
				$posV = $centerY - round($rad * cos($ang)) + rand($this->params['letter_vertical_offset'], $this->params['letter_vertical_offset_to']);
			}
		}
	}

	public function writeChar($chr, $x, $y, $hTrend, $vTrend){
		/*
			Записывает символ. Возвращает ширину и высоту написанного символа
		*/

		$font = new Font(
			rand($this->params['font_size'], $this->params['font_size_to']),
			$this->getRandColor($this->params['color'], $this->params['color_to']),
			Library::arrayRand($this->params['fonts']),
			rand($this->params['angle'], $this->params['angle_to']),
			rand($this->params['transparent'], $this->params['transparent_to'])
		);

		$this->image->write($chr, $font, $x, $y, $hTrend, $vTrend, rand($this->params['font_blur'], $this->params['font_blur_to']));
		return $this->image->getBox($chr, $font);
	}



	/***************************************************************************************************************************************************************

																		Системные функции

	****************************************************************************************************************************************************************/

	private function getRandColor($from, $to){
		$return = '';
		if(regExp::len($from) == 3) $from = $from[0].$from[0].$from[1].$from[1].$from[2].$from[2];
		if(regExp::len($to) == 3) $to = $to[0].$to[0].$to[1].$to[1].$to[2].$to[2];

		for($i = 0; $i < 6; $i ++){
			$return .= Library::hexRand($from[$i], $to[$i]);
		}

		return $return;
	}

	private function newCode($standart = false){
		if($standart) $params = $GLOBALS['Core']->getCaptchaParams($standart);
		else{
			$params = $this->params;
			$standart = $this->captchaStandart;
		}

		if($params['captcha_type'] == 't') $code = Library::inventStr($params['len'], $params['symbols'], $params['len_to']);
		elseif($params['captcha_type'] == 'm'){
			$rndActs = rand($params['math_len'], $params['math_len_to']);
			$code = rand($params['math_nums'], $params['math_nums_to']);
			for($i = 0; $i < $rndActs; $i ++) $code .= Library::arrayRand($params['math_actions']).rand($params['math_nums'], $params['math_nums_to']);
		}
		else throw new AVA_Exception('Неверный тип CAPTCHA - "'.$params['captcha_type'].'"');

		$fields = array('date' => time(), 'code' => $code, 'captcha_standart' => $standart);
		if($params['direction'] == 'a') $fields['vars']['direction'] = Library::arrayRand(array('r', 'l', 't', 'b', 'c', 'd'));
		return $GLOBALS['Core']->DB->Ins(array('captcha', $fields));
	}


	/***************************************************************************************************************************************************************

																				Прочее

	****************************************************************************************************************************************************************/

	public static function inventCaptcha($standart, &$captcha = false){
		/*
			Выдумывает капчу, возвращает ID
		*/

		$captcha = new captcha(0, $standart);
		return $captcha->getId();
	}

	public static function checkCaptcha($id, $code){
		/*
			Проверяет капчу
		*/

		$captcha = new captcha($id);

		if(regExp::len($code) > 0){
			if($captcha->getParam('captcha_type') == 'm' && ($code == eval('return '.$captcha->getCode().';'))) return true;
			elseif($captcha->getParam('register_depend') && $code == $captcha->getCode()) return true;
			elseif(regExp::lower($code) == regExp::lower($captcha->getCode())) return true;
		}

		return false;
	}
}

?>