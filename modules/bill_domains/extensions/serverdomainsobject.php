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



$GLOBALS['Core']->loadExtension('billing', 'servconnectObject');

class serverDomainsObject extends servconnectObject{
	private $blanks = array();
	private $blanksByEml = array();
	private $contacts = array();
	private $clients = array();
	protected $bTypes = array('', '_a', '_b', '_t');

	protected function __ava__getBlankInServer($id){		/*
			Возвращает имя анкеты на сервере или false если ее не существует
		*/

		$this->fetchBlank($id);
		return empty($this->contacts[$id][$this->extension]) ? false : $this->contacts[$id][$this->extension];
	}

	protected function __ava__setBlank($id, $name, $pwd = ''){		/*
			Добавляет сведения о хранимй у регистратора анкете
		*/

		$pwd = Library::crypt($pwd);
		$this->contacts[$id][$this->extension] = $name;
		return $this->sObj->DB->Ins(array('contacts', array('domain_owners_id' => $id, 'name' => $name, 'pwd' => $pwd, 'connection' => $this->extension)));
	}

	protected function __ava__getClientInServer($id){
		/*
			Возвращает имя анкеты на сервере или false если ее не существует
		*/

		$this->fetchBlank($id);
		return empty($this->clients[$id][$this->extension]) ? false : $this->clients[$id][$this->extension];
	}

	protected function __ava__getClientInServerByEmail($clientId, $eml){
		/*
			Возвращает имя анкеты на сервере или false если ее не существует по e-mailу
		*/

		$this->fetchBlanksByEml($clientId);
		return empty($this->blanksByEml[$clientId][$this->extension][$eml]) ? false : $this->blanksByEml[$clientId][$this->extension][$eml];
	}

	protected function __ava__setClient($id, $name, $pwd = ''){
		/*
			Добавляет сведения о хранимй у регистратора анкете
		*/

		$pwd = Library::crypt($pwd);
		$this->clients[$id][$this->extension] = $name;
		return $this->sObj->DB->Ins(array('clients', array('domain_owners_id' => $id, 'name' => $name, 'pwd' => $pwd, 'connection' => $this->extension)));
	}

	protected function __ava__getBlank($id){		/*
			Возвращает анкету
		*/

		$this->fetchBlank($id);
		return $this->blanks[$id];	}

	protected function __ava__getBlankVars($id){
		/*
			Возвращает анкету
		*/

		$params = $this->getBlank($id);
		return $params['vars'];
	}

	private function fetchBlanksByEml($clientId){
		/*
			Извлекает анкету
		*/

		if(!isset($this->blanksByEml[$clientId])){
			$this->blanksByEml[$clientId] = array();
			foreach($this->sObj->DB->columnFetch(array('domain_owners', '*', 'id', "`client_id`='$clientId'")) as $i => $e){				$e['vars'] = Library::unserialize($e['vars']);
				if($cData = $this->sObj->DB->rowFetch(array('clients', array('name', 'pwd'), "`domain_owners_id`='$i' AND `connection`='{$this->extension}'"))){					$this->blanksByEml[$clientId][$this->extension][$e['vars']['eml']] = $cData;
				}
			}
		}
	}

	private function fetchBlank($id){		/*
			Извлекает анкету
		*/
		if(empty($this->blanks[$id])){
			$this->blanks[$id] = $this->sObj->DB->rowFetch(array('domain_owners', '*', "`id`='$id'"));
			$this->blanks[$id]['vars'] = Library::unserialize($this->blanks[$id]['vars']);
			$this->contacts[$id] = $this->sObj->DB->columnFetch(array('contacts', 'name', 'connection', "`domain_owners_id`='$id'"));
			$this->clients[$id] = $this->sObj->DB->columnFetch(array('clients', 'name', 'connection', "`domain_owners_id`='$id'"));
		}
	}

	protected function __ava__getName($params){
		/*
			Выдает русский вариант имени
		*/

		return ucWords($params['lname'].' '.$params['fname'].' '.$params['pname']);
	}

	protected function __ava__getPassport($params){		/*
			Выдает паспортные данные
		*/

		return $params['passport'].', выдан '.$params['passportIssue'].($params['passportIssueDay'] ? ' '.date('d.m.Y', $params['passportIssueDay']) : '');
	}

	protected function __ava__getEnName($params){
		/*
			Выдает английский вариант имени
		*/

		$str = library::cyr2translit($params['fname']).' '.regExp::subStr(library::cyr2translit($params['pname']), 0, 1).' '.library::cyr2translit($params['lname']);
		return ucWords($str);
	}

	protected function __ava__getEnName2($params){
		/*
			Выдает английский вариант имени по другому
		*/

		$str = library::cyr2translit($params['lname']).', '.library::cyr2translit($params['fname']);
		return ucWords($str);
	}

	protected function __ava__getEnName3($params){
		/*
			Выдает английский вариант имени по другому
		*/

		$str = library::cyr2translit($params['lname']).' '.library::cyr2translit($params['fname']).' '.regExp::subStr(library::cyr2translit($params['pname']), 0, 1);
		return ucWords($str);
	}

	protected function __ava__getEnPhone($phone){
		$phone = trim($phone);
		if(!$phone) return '';
		$phn = regExp::split(' ', $phone, false, 2);		return $phn['0'].'.'.regExp::replace("/\W/", '', $phn['1'], true);	}

	protected function __ava__getAddr($params, $zip = false, $komu = false){
		/*
			Выдает адрес
		*/

		$return = $params['region'].', '.$params['city'].', '.$params['street'];
		if($zip) $return = $params['zip'].', '.$return;
		if($komu) $return = $return.', '.$this->getName($params);
		return $return;
	}

	protected function __ava__getNs($params){
		/*
			Возвращает NS и IP к нимъ
		*/

		$return = array();
		for($i = 1; $i <= 4; $i ++){
			if(!empty($params['ns'.$i.'_'])) $return[] = trim($params['ns'.$i.'_']);
			elseif($ns = $this->sObj->getParam('ns'.$i)) $return[] = trim($ns);
		}

		return $return;
	}

	protected function __ava__getDefaultContactMatrix($data){		/*
			Создает матрицу для оформления контакта
		*/
		$matrix = array();
		foreach($data as $i => $e){
			$matrix[$i] = array(
				'text' => $i,
				'type' => 'text',
				'value' => $e
			);
		}

		return $matrix;
	}

	protected function __ava__parseRrpBlock($text){		/*
			Парсит блок текста для rrp вида
				name: value
		*/

		$return = array();
		$text = regExp::Split("\n", $text);

		foreach($text as $e){			if(count($e = regExp::Split(":", $e, false, 2)) > 1){				$e['0'] = trim($e['0']);
				$e['1'] = trim($e['1']);
				if(empty($return[$e['0']])) $return[$e['0']] = '';
				$return[$e['0']] .= $e['1'];
			}		}

		return $return;
	}
}

?>