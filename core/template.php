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


class template extends objectInterface {
		/*
			Принимается шаблон для внесения замен и массив замен
			1. Вырезаются все блоки.
			2. Находятся и преобразуются в сугубо текстовые последовательности блоки-вызовы (call:)
			3. Происходит замена блоков
			4. Проверяется наличие еще незамененных блоков и при их наличии происходит рекурсивный вызов
		*/

	private $tmplText;				//Необработанный текст шаблона
	private $replaces;				//Массив для замены
	private $noCalls = array();

	public function __construct($tmplText, $replaces){
		$this->tmplText = $tmplText;
		$this->replaces = $replaces;
	}

	public function getTmplText(){
		return $this->tmplText;
	}

	public function replace($continue = true, $depth = 0){
		/*
			Функция осуществляет замену вхождений из $tmplText элементами $replaces
			Возвращает сгенерированный шаблон
		*/

		if(SHOW_TMPL_DEBUG_DATA > 1) $GLOBALS['Core']->setDebugAction('Начало обработки шаблона '.$__int_tmplText);
		if($this->generate() && ($continue === true || $continue < $depth)) $this->replace($continue, $depth + 1);
		$this->replaceBlocks($this->noCalls, '~~~~', '~~~~');
		return $this->tmplText;
	}

	private function stripNocalls(){
		/*
			Вырезает блоки к которым обращаться не положено
			В коде такие блоки заменяются на ~~~последовательность~~~
		*/

		foreach(array_unique(regExp::matchAll("|\[nocall\].+\[/nocall\]|iUs", $this->tmplText, false, 0, $_, 0)) as $i => $e) $this->noCalls[Library::getUniqKey($this->noCalls, 8)] = $e;
		$this->replaceBlocks($this->noCalls, '~~~~', '~~~~', 1);
	}

	private function generate(){
		/*
			Генерирует готовый код
			1. Выбираем вызовы типа "Call" и заменяем их на собственно вызовы
			2. Преобразовываем shortTags в <?php
			3. Вырезаем все что между тегами <?php ?> преобразовывая в спец. последовательности
			4. Все что вне преобразуем в строки для echo
			5. Возвращаем html-код
			Затем все выполняется в eval и возвращается результат
		*/

		if(SHOW_TMPL_DEBUG_DATA > 1) $GLOBALS['Core']->setDebugAction('Начало цикла обработки для '.$this->tmplText);
		$__int_textInRun = $this->tmplText;
		$this->stripNocalls();		//Вырезаем то что обработке не подлежит

		if(($isPhp = regExp::Match("<?", $this->tmplText)) || regExp::match("/\{[A-z_]\w*\}/", $this->tmplText, true)){
			if($isPhp){
				//Приводим php-теги к единому стандарту
				$this->tmplText = regExp::Replace(array("<"."?", "<"."?phpphp", "<"."?php=", "?>"), array('<'.'?php', '<'.'?php', '<'.'?php echo ', ";?>"), $this->tmplText);
				$this->tmplText = regExp::Replace("|\?>\s*<\?php|", ";", $this->tmplText, true);
				$this->tmplText = regExp::Replace("|(\S)\s+;|", "$1;", $this->tmplText, true);
				$this->tmplText = regExp::Replace("|\}\s*;+\s*else|", '}else', $this->tmplText, true);

				//Вырезаем php-код
				$__int_codeBlocks = array();
				foreach(Library::sortLen(array_unique(regExp::matchAll("/<\?php(.*)\?".">/iUs", $this->tmplText, false, 0, $_, 1))) as $__int_i => $__int_e){
					$__int_codeBlocks[Library::getUniqKey($__int_codeBlocks, 8)] = $__int_e;
				}
				$this->replaceBlocks($__int_codeBlocks, '~$~~', '~~&~', 1);
			}

			//Создаем код пригодный для исполнения в eval
			$this->tmplText = 'echo "'.regExp::Replace("/\{([A-z_]\w*)\}/", '{$$1}', addcslashes($this->tmplText, '"\\'), true).'";';
			if($isPhp){
				$this->tmplText = regExp::Replace(array('?'.'>', '<'.'?php'), array('echo "', '";'), $this->tmplText);
				$this->replaceBlocks($__int_codeBlocks, '~$~~', '~~&~');
			}

			//Получаем результат
			if(Library::constVal('SHOW_TMPL_DEBUG_DATA')) $GLOBALS['Core']->setDebugAction('Выполнена предварительная обработка кода в цикле');
			extract($this->replaces);
			if(Library::constVal('TEST_MODE')) error_reporting(E_WARNING | E_PARSE);

			ob_start();
			eval($this->tmplText);
			$result = ob_get_contents();
			ob_end_clean();

			if(Library::constVal('TEST_MODE')) error_reporting(E_ALL);
			if(regExp::Match('Parse error', $result)) throw new AVA_Templ_Exception('Ошибка разбора шаблона: <strong>'.$result.'</strong><br/><br/><pre>'.regExp::html($this->tmplText).'</pre>');
			if(Library::constVal('SHOW_TMPL_DEBUG_DATA')) $GLOBALS['Core']->setDebugAction('Цикл завершен');

			$this->tmplText = $result;
		}

		$this->stripNocalls();		//Вырезаем то что обработке не подлежит
		$callBlocks = regExp::matchAll("/\{call:([^<\s\{\}\,'".'"'."]*)\}/iUs", $this->tmplText);

		if(!empty($callBlocks[1])){
			//Если найдены вызовы типа Call

			foreach($callBlocks[1] as $i => $e){
				$str = explode(":", $e);
				switch(regExp::lower($str[0])){
					case 'modulecall':
						if(!empty($str[4])){
							$str[4] = regExp::Replace("|&amp;|i", '&', $str[4], true);
							$str[4] = Library::parseStr($str[4]);
						}
						else $str[4] = array();
						$replace = $GLOBALS['Core']->callModule($str[1], $str[2], $str[4])->getContentVar($str[3]);
						break;

					case 'lang':
						$replace = $GLOBALS['Core']->Lang->getPhrase($str[1], $str[2], $str[3], !empty($str['4']) ? Library::unserialize($str['4']) : array());
						break;

					case 'plugin':
						$replace = $this->evalPlugin($str[1]);
						break;

					default:
						throw new AVA_Exception('Неопределеный способ вызова блока '.$callBlocks[0][$i]);
				}

				$this->tmplText = regExp::replace($callBlocks[0][$i], $replace, $this->tmplText);
			}
		}

		if($__int_textInRun == $this->tmplText) return false;
		return true;
	}

	private function replaceBlocks($arr, $pre, $post, $trend = 0){
		/*
			Преобразует выборку из массива
		*/

		$s1 = $s2 = array();
		foreach($arr as $i => $e){
			if(regExp::printed($e)){
				$s1[] = $pre.$i.$post;
				$s2[] = $e;
			}
		}

		if($trend == 0) $this->tmplText = regExp::replace($s1, $s2, $this->tmplText);
		else $this->tmplText = regExp::replace($s2, $s1, $this->tmplText);
	}
}

?>