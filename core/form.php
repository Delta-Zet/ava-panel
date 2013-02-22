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


class Form extends formCheck {

	public $matrix = array();
	public $matrixBlocks = array();
	public $excludes = array();
	public $errors = array();

	public $values = array();
	public $hiddens = array();
	protected $templPref = 'form';
	private $formHaveFile = false;


	public function __ava__setHiddens($hiddens){
		$this->hiddens = Library::array_merge($this->hiddens, $hiddens);
		$this->values = Library::array_merge($this->values, $hiddens);
	}

	public function __ava__setHidden($var, $value){
		$this->hiddens[$var] = $value;
		$this->values[$var] = $value;
	}

	public function __ava__getHiddens(){
		$return = $this->hiddens;
		foreach($this->matrix as $i => $e){
			if($e['type'] == 'hidden' || !empty($e['disabled'])) $return[$i] = isset($e['value']) ? $e['value'] : (isset($this->values[$i]) ? $this->values[$i] : '');
		}

		return $return;
	}

	public function __ava__setErrors($errors){
		/*
			Устанавливает список ошибок
		*/

		$this->errors = Library::array_merge($this->errors, $errors);
	}

	public function __ava__setError($var, $value){
		if(empty($this->errors[$var])) $this->errors[$var] = '';
		else $this->errors[$var] .= '<br />';
		$this->errors[$var] .= $value;
	}

	public function __ava__setMatrix($matrix, $block){
		if(empty($this->matrixBlocks[$block])) $this->matrixBlocks[$block] = array();
		$this->matrixBlocks[$block] = Library::array_merge($this->matrixBlocks[$block], $matrix);
		$this->matrix = Library::array_merge($this->matrix, $matrix);
	}

	public function __ava__setExcludes($excludes){
		if(!is_array($excludes)) $excludes = array($excludes);
		$this->excludes = Library::array_merge_numeric($this->excludes, $excludes);
	}

	public function __ava__rmExcludes($excludes){
		if(!is_array($excludes)) $excludes = array($excludes);
		foreach($this->excludes as $i => $e){
			foreach($excludes as $e1){
				if($e == $e1) unset($this->excludes[$i]);
			}
		}
	}

	public function __ava__setValues($values){
		$this->values = Library::array_merge($this->values, $values);
	}

	public function __ava__setValue($var, $value){
		$this->values[$var] = $value;
	}

	public function __ava__getValues(){
		$return = $this->values;
		foreach($this->matrixBlocks as $i => $e){
			foreach($e as $i1 => $e1){
				if(isset($e1['value'])) $return[$i1] = $e1['value'];
				elseif(!isset($return[$i1])){
					if($e1['type'] == 'select' || $e1['type'] == 'radio') $return[$i1] = Library::FirstKey($e1['additional']);
					elseif($e1['type'] != 'checkbox') $return[$i1] = '';
				}
			}
		}

		return $return;
	}

	public function __ava__getValue($var){
		if(isset($this->matrix[$var]['value'])) return $this->matrix[$var]['value'];
		elseif(isset($this->values[$var])) return $this->values[$var];
		return '';
	}

	public function __ava__getMatrix(){
		/*
			Проверяет что форма пуста
		*/

		$m = $this->matrix;
		foreach($this->hiddens as $i => $e){
			$m[$i] = array('type' => 'hidden', 'value' => $e);
		}

		foreach($this->excludes as $e) unset($m[$e]);
		return $m;
	}

	public function __ava__matrixIsEmpty(){
		/*
			Проверяет что форма пуста
		*/

		$m = $this->matrix;
		foreach($this->excludes as $e) unset($m[$e]);
		foreach($m as $i => $e) if($e['type'] == 'caption' || $e['type'] == 'hidden' || !empty($e['disabled'])) unset($m[$i]);
		return !$m;
	}

	public function __ava__addBlock($block = 'form'){
		/*
			Генерирует основной блок формы и hiddens. Возвращает их как ассоц. массив
			Матрица формы имеет вид $matrix[varName][Param] = Value, напр. $matrix['myname']['type'] = 'hidden'; Ни один тип второго параметра не является обязательным
			и при отсутствии type получает type = text;

			Шаблон записи формируется как entry{типПоля}{Постфикс}, например entrytext или entrytexttwoset. Постфикс необязателен.
			Если встречается блок с меткой disabled, его значение дублируется в hiddens, при этом перезаписывая существующий hidden с тем же именем
			Если же встречается блок типа hidden, он помещается в hiddens, но не может перезаписывать текущий hidden
			При конкурировании видимых блоков и hidden видимый имеет преимущество

			disabled должно быть передано как disabled=disabled

			Для отдельных типов записей, типа select, checkbox_array, radio, multiselect и др. использующий additional формируется свой субшаблон
			с префиксом additentry{Тип} например additentryselect. Может быть установлен свой шаблон идентифицируемый по постфиксу $name
		*/

		$form = '';
		$values = $this->values;

		//Формируем основной блок
		if(empty($this->matrixBlocks[$block])) throw new AVA_Exception('{Call:Lang:core:core:soderzhimoef:'.Library::serialize(array($block)).'}');

		foreach($this->matrixBlocks[$block] as $i => $e){
			if(empty($e['type'])) throw new AVA_Exception('{Call:Lang:core:core:neukazantipp1:'.Library::serialize(array($i)).'}');
			unset($this->hiddens[$i]);

			switch($e['type']){
				case 'hidden':
					$this->hiddens[$i] = empty($e['value']) ? (isset($values[$i]) ? $values[$i] : '') : $e['value'];
					continue 2;

				case 'checkbox':
					$e['mark'] = empty($values[$i]) && !isset($e['value']) ? '' : ' checked';
					break;

				case 'file':
					$e['max'] = empty($e['additional']['max']) ? 0 : $e['additional']['max'];
					$this->formHaveFile = true;
					break;

				case 'checkbox_array':
					if(!$e['additional']) continue 2;

				case 'multiselect':
				case 'select':
				case 'radio':
					$val = isset($e['value']) ? $e['value'] : (isset($values[$i]) ? $values[$i] : '');
					if(isset($e['additional']) && is_array($e['additional'])) $e['data'] = $this->getAdditional($i, $e, $e['additional'], $val);
					break;

				case 'gap':
				case 'calendar':
					$e['name_to'] = !empty($e['name_to']) ? $e['name_to'] : $i.'_to';
					$e['value_to'] = isset($e['value_to']) ? $e['value_to'] : (isset($values[$e['name_to']]) ? $values[$e['name_to']] : '');
					if($e['type'] == 'gap') break;
					$e['value_to'] = $e['value_to'] ? dates::datetime($e['value_to']) : '';

				case 'calendar2':
					$e['value'] = isset($e['value']) ? $e['value'] : (isset($values[$i]) ? $values[$i] : '');
					$e['value'] = $e['value'] ? dates::datetime($e['value']) : '';
					break;
			}

			if(!empty($this->errors[$i])) $e['error'] = $this->errors[$i];
			if(!isset($e['value'])) $e['value'] = isset($values[$i]) ? $values[$i] : '';

			if(!empty($e['disabled']) && $e['type'] != 'radio' && $e['type'] != 'checkbox_array'){
				$this->hiddens[$i] = $e['value'];
				$e['additional_style'] = isset($e['additional_style']) ? $e['additional_style'].' disabled' : ' disabled';
			}

			$e['name'] = $i;
			$e['id_prefix'] = empty($this->params['id_prefix']) ? '' : $this->params['id_prefix'];
			$e['extraParams'] = $this->params;

			$e['value'] = regExp::html($e['value'], false, '"\'');
			if($e['value']) $e['value'] = $this->setNocalls($e['value']);
			$form .= $GLOBALS['Core']->replace($this->getTmplBlock($e['type'], $e), $this->parent, $e);
		}

		$this->setParam($block, $form);
	}

	public function __ava__getAdditional($name, $parentParam, $additional, $value = ''){
		/*
			Формирует список для radio, select и т.п.
			$parentParam - параметры родительского блока в матрице
			префикс шаблона additentry{Тип} например additentryselect
			$name может выступать как постфикс
		*/

		$return = '';
		$type = $parentParam['type'];
		if(!$value && !empty($this->matrix[$name]['value'])) $value = $this->matrix[$name]['value'];
		if(!is_array($value)) $value = (string)$value;

		foreach($additional as $i => $e){
			$i = (string)$i;
			$replaces = is_array($e) ? $e : array('text' => $e);
			$replaces['name'] = $name;
			$replaces['id'] = regExp::html($i, '"\'');

			if($value !== false && ($i === $value || '[nocall]'.$i.'[/nocall]' === $value || (is_array($value) && !empty($value[$i])))){
				switch($type){
					case 'select':
					case 'multiselect':
						$replaces['mark'] = 'selected';
						break;

					default:
						$replaces['mark'] = 'checked';
				}
			}

			$replaces = Library::array_merge($parentParam, $replaces);
			foreach($replaces as $i1 => $e1){
				if(is_array($e1)){
					$replaces[$i1] = isset($e1[$i]) ? $e1[$i] : '';
				}
			}

			if(!empty($replaces['disabled'])){
				$replaces['additional_style'] = isset($replaces['additional_style']) ? $replaces['additional_style'].' disabled' : ' disabled';
				$this->hiddens[$name] = $value;
			}
			$return .= $GLOBALS['Core']->replace($this->getTmplBlock($type, $replaces, 'additentry'), $this->parent, $replaces);
		}

		return $return;
	}

	public function addJS(){
		/*
			Создает javascript-проверку для формы
		*/

		$warns = $warnPatterns = $warnPatternText = $types = $addit = array();

		foreach($this->matrix as $i => $e){
			if(!empty($e['warn'])) $warns[] = "{$i}: '{$e['warn']}'";
			if(!regExp::match("|^[A-z_]|", $i, true)) $i = '_'.$i;
			$i = regExp::replace("|\W|", "_", $i, true);
			$types[] = "{$i}: '{$e['type']}'";

			if(!empty($e['warn']) && ($e['type'] == 'radio' || $e['type'] == 'checkbox_array')){
				if(is_array($e['additional'])){
					$additData = array();
					foreach($e['additional'] as $i1 => $e1){
						if(!regExp::match("|^[A-z_]|", $i1, true)) $i1 = '_'.$i1;
						$i1 = regExp::replace("|\W|", "_", $i1, true);
						$additData[] = "{$i1}: true";
					}
					$addit[] = $i.': {'.implode(',', $additData).'}';
				}
			}

			if(!empty($e['warn_pattern'])){
				$wp = regExp::Match("/^\//", $e['warn_pattern'], true) ? $e['warn_pattern'] : '/'.$e['warn_pattern'].'/gim';
				$warnPatterns[] = "{$i}: {$wp}";
				if(!empty($e['warn_pattern_text'])) $warnPatternText[] = "{$i}: '".regExp::replace(array("'", '"'), "`", $e['warn_pattern_text'])."'";
				else $warnPatternText[] = '{Call:Lang:core:core:nepravilnoza:'.Library::serialize(array($i, regExp::replace(array("'", '"'), "`", $e['text']))).'}';
			}
		}

		$this->setParam('js', "{"."warns: "."{".implode(',', $warns)."}, ".
			"types: "."{".implode(',', $types)."}, ".
			"addit: "."{".implode(',', $addit)."}, ".
			"warnPatterns: "."{".implode(',', $warnPatterns)."}, ".
			"warnPatternsText: "."{".implode(',', $warnPatternText)."}}");
	}

	public function addErrorsList(){
		/*
			Возвращает список всех ошибок полученных в форме
		*/

		$return = '';
		foreach($this->errors as $i => $e){
			$return .= $GLOBALS['Core']->replace($this->getTmplBlock('error_entry', $e, 'extra'), $this->parent, array('var' => $i, 'text' => $e));
		}
		$this->setParam('errors', $return);
	}

	public function addHiddensList(){
		/*
			Возвращает список всех hidden полей формы
		*/

		$return = '';
		foreach($this->setNocalls($this->hiddens) as $i => $e){
			$return .= $GLOBALS['Core']->replace($this->getTmplBlock('hidden_entry', $e, 'extra'), $this->parent, array('name' => $i, 'value' => regExp::html($e, false, '"\'')));
		}
		$this->setParam('hiddens', $return);
	}

	public function addMatrixBlocks(){
		foreach($this->matrixBlocks as $i => $e){
			$this->addBlock($i);
		}
	}

	public function exclude(){
		foreach($this->excludes as $e){
			unset($this->matrix[$e], $this->values[$e], $this->hiddens[$e]);
			foreach($this->matrixBlocks as $i1 => $e1){
				unset($this->matrixBlocks[$i1][$e]);
			}
		}
	}

	public function addAllBlocks(){
		$this->addMatrixBlocks();
		$this->addHiddensList();
		$this->addErrorsList();
		$this->addJS();

		if($this->formHaveFile && (empty($this->params['extras']) || !regExp::Match('enctype=', $this->params['extras']))){
			$this->setParam('extras', ' enctype="multipart/form-data"');
			$this->params['method'] = 'post';
		}
	}
}

?>