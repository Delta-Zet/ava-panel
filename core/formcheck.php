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


class formCheck extends pageObjectsInterface{

	private $errorMsg;				//Фатальные сообщения об ошибках, когда адекватная проверка формы становится невозможной

	public function check(){
		/*
			Прогоняет форму на предмет ошибок
			checkConditions имеет следующую структуру:
				Если поле по которому идет проверка не является массивом
					$matrix[Имя поля которое должно быть проверено][checkConditions][Имя поля которое должно быть заполнено, чтобы проверяемое было проверено] = значение  ИЛИ
					$matrix[Имя поля которое должно быть проверено][checkConditions][Имя поля которое должно быть заполнено, чтобы проверяемое было проверено] = массив значений, если любое из них принято, поле не проверяется

				Если поле по которому идет проверка является массивом
					Если в chrckConditions для него строка, проверка будет прекращена если внутри массива хотябы 1 индекс равен этой строке
					Если в chrckConditions для него массив, проверка будет прекращена если внутри массива хотябы 1 индекс равен хотябы одному значению из checkConditions

			Таким образом, если проверка поля (fld) должна выполняться если в checkbox_array (cha=val) отмечен определенный пункт, checkConditions должен выглядеть так:
				$matrix[fld][checkConditions][cha] = val
			Если проверка поля (fld) должна выполняться если в checkbox_array (cha=val или cha=val2) отмечен определенный пункт, checkConditions должен выглядеть так:
				$matrix[fld][checkConditions][cha] = array(val, val2)
		*/

		foreach($this->matrix as $i => $e){
			if($e['type'] == 'hidden') continue;

			if(!empty($e['checkConditions'])){
				foreach($e['checkConditions'] as $i1 => $e1){
					if(!isset($this->values[$i1])){ continue 2; }									//Если поле по которому идет проверка вообще не существует, например это checkbox
					elseif(!is_array($this->values[$i1])){											//Если поле по которому идет проверка не является массивом
						if(!is_array($e1) && $this->values[$i1] != $e1){ continue 2; }				//Если поле по которому идет проверка может иметь только 1 значение и оно не равно тому по которому надо проверять
						elseif(is_array($e1) && !in_array($this->values[$i1], $e1)){ continue 2; }	//Если поле по которому идет проверка имеет хоть одно значение из массива
					}
					else{																			//Если поле по которому идет проверка является массивом
						if(!is_array($e1) && empty($this->values[$i1][$e1])){ continue 2; }
						elseif(is_array($e1)){
							foreach($e1 as $e2){
								if(empty($this->values[$i1][$e2])){ continue 3; }
							}
						}
					}
				}
			}

			if($this->matrix[$i]['type'] == 'file') $this->checkFile($i);
			if(!empty($this->matrix[$i]['warn'])) $this->checkFill($i);
			if(!empty($this->matrix[$i]['warn_pattern']) && !empty($this->values[$i])) $this->checkPattern($i);
			if(!empty($this->matrix[$i]['warn_function']) && !empty($this->values[$i])) $this->checkFunction($i);
		}
	}

	private function checkFill($var){
		/*
			Проверяет, есть ли вообще данные для этой переменной
		*/

		if(!isset($this->values[$var]) || $this->values[$var] === ''){
			$this->setError($var, $this->matrix[$var]['warn']);
		}
	}

	private function checkPattern($var){
		//Проверяет соответствие паттерну

		if(!regExp::match("/^[\/\|#]/", $this->matrix[$var]['warn_pattern'], true)){
			$this->matrix[$var]['warn_pattern'] = '/'.$this->matrix[$var]['warn_pattern'].'/is';
		}

		if(!regExp::match($this->matrix[$var]['warn_pattern'], $this->values[$var], true)){
			if(!empty($this->matrix[$var]['warn_pattern_text'])){
				$this->setError($var, $this->matrix[$var]['warn_pattern_text']);
			}
			else{
				$this->setError($var, '{Call:Lang:core:core:polezapolnen}');
			}
		}
	}

	private function checkFunction($var){
		/*
			Обращается к некоторой функции, передавая ей введенное пользователем значение и возвращает сообщение error переданное этой функцией
			Передаются имя индекса проверки и по ссылке массивы values и errors
			Если $this->matrix[$var]['warn_function'] - массив, он должен содержать имя модуля и имя функции для вызова
		*/

		$func = $this->matrix[$var]['warn_function'];
		if(is_array($func)) $func = $func['mod'].'::'.$func['func'];
		elseif(!regexp::match('::', $func) && !function_exists($func)) $func = '$this->'.$func;

		if(!eval('return '.$func.'("'.$this->values[$var].'", $var, $this);')){
			$this->setError($var, !empty($this->matrix[$var]['warn_pattern_text']) ? $this->matrix[$var]['warn_pattern_text'] : '{Call:Lang:core:core:polezapolnen}');
		}
	}


	/*********************************************************************************************************************************************************************

																		Дополнительные интерфейсы

	*********************************************************************************************************************************************************************/

	public function lastError(){
		return $this->errorMsg;
	}

	private function checkFile($var){
		/*
			Проверяет файл на тип данных и на размер
		*/

		$fData = $GLOBALS['Core']->getGPCVar('f', $var);
		if($fData['name']){
			if(!empty($this->matrix[$var]['additional']['max']) && ($fData['size'] > $this->matrix[$var]['additional']['max'])){
				$this->setError($var, '{Call:Lang:core:core:maksimalnyjr:'.Library::serialize(array(Library::byte2mb($this->matrix[$var]['additional']['max']))).'}');
			}
			if(!empty($this->matrix[$var]['additional']['min']) && ($fData['size'] < $this->matrix[$var]['additional']['min'])){
				$this->setError($var, '{Call:Lang:core:core:minimalnyjra:'.Library::serialize(array(Library::byte2mb($this->matrix[$var]['additional']['min']))).'}');
			}

			if(!$this->matrix[$var]['additional']['allow_ext']){ throw new AVA_Exception('{Call:Lang:core:core:neustanovlen2:'.Library::serialize(array($var)).'}'); }
			elseif($this->matrix[$var]['additional']['allow_ext'] == 'all') return true;

			$ext = Files::getExtension($fData['name']);
			if(!in_array(regExp::lower($ext), $this->matrix[$var]['additional']['allow_ext'])) $this->setError($var, '{Call:Lang:core:core:dopustimyeti:'.Library::serialize(array(implode(', ', $this->matrix[$var]['additional']['allow_ext']))).'}');
		}
	}
}

?>