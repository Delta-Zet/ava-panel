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



$GLOBALS['Core']->loadExtension('bill_domains', 'serverDomainsObject');

class rrpClient extends serverDomainsObject{
	public function __ava__parseReq($httpObj){
		/*
			Разбор ответа HTTP-запроса
		*/

		if(!$this->setErrorByHttp($httpObj)){
			return false;
		}

		$result = array(
			'response' => array(),
			'head' => array(),
			'body' => array()
		);

		$body = regExp::replace("\r", "", $httpObj->getResponseBody());
		$parts = regExp::split("\n\n", $body, false, 2);
		$parts[0] = regExp::Split("\n", $parts[0]);
		$result['response'] = regExp::Split("|\s+|", trim($parts[0][0]), true, 3);

		foreach($parts[0] as $e) $this->gerRRPEntry($e, $result['head']);

		if(!empty($parts[1])){
			$parts[1] = regExp::split("\n\n", trim($parts[1]));
			foreach($parts[1] as $i => $e){
				$e = regExp::split("\n", $e);
				foreach($e as $i1 => $e1) $this->gerRRPEntry($e1, $result['body'][$i]);			}
		}

		if(empty($result['head']['request-id'])) $result['head']['request-id'] = $this->transactionId;
		if(trim($result['head']['request-id']) != $this->transactionId){
			$this->setErrorParams(8);
			return false;
		}

		if(isset($result['body'][0]['[errors]'])){			foreach($result['body'][0] as $i => $e){				if($i != '[errors]') $result['response'][2] .= '. '.$i;			}		}

		switch($result['response'][1]){
			case '200': return $result;
			case '400': $this->setErrorParams(6, '{Call:Lang:modules:bill_domains:nepravilnyjz:'.Library::serialize(array($result['response'][2])).'}'); return false;
			case '401': $this->setErrorParams(10); return false;
			case '402': $this->setErrorParams(6, '{Call:Lang:modules:bill_domains:oshibkivtele:'.Library::serialize(array($result['response'][2])).'}'); return false;
			case '403': $this->setErrorParams(6, '{Call:Lang:modules:bill_domains:zaprostakogo:'.Library::serialize(array($result['response'][2])).'}'); return false;
			case '404': $this->setErrorParams(404, '{Call:Lang:modules:bill_domains:zaprashivaem:'.Library::serialize(array($result['response'][2])).'}'); return false;
			case '405': $this->setErrorParams(405, '{Call:Lang:modules:bill_domains:prevyshenied:'.Library::serialize(array($result['response'][2])).'}'); return false;
			case '500': $this->setErrorParams(500, '{Call:Lang:modules:bill_domains:vnutrenniaia:'.Library::serialize(array($result['response'][2])).'}'); return false;
			case '501': $this->setErrorParams(501, '{Call:Lang:modules:bill_domains:bazadannykhv:'.Library::serialize(array($result['response'][2])).'}'); return false;
			case '502': $this->setErrorParams(502, '{Call:Lang:modules:bill_domains:serverobrabo:'.Library::serialize(array($result['response'][2])).'}'); return false;
			default: $this->setErrorParams(2, $result['response'][2]); return false;
		}
	}

	private function gerRRPEntry($str, &$result){		/*
			Добавляет в результат строку
		*/
		$str = regExp::split(':', $str, false, 2);
		$str[0] = empty($str[0]) ? '' : trim($str[0]);
		$str[1] = empty($str[1]) ? '' : trim($str[1]);

		if($str[0]){			if(empty($result[$str[0]])) $result[$str[0]] = '';
			$result[$str[0]] .= $str[1];		}
	}

	public function __ava__getReq($head, $items){		/*
			Создает RRP-запрос
		*/

		return $this->getBlock($head).$this->getItems($items);
	}

	private function getItems($items){		/*
			Возвращает список всех Item
		*/

		$return = '';
		foreach($items as $i => $e){			$return .= "\n[".$i."]\n".$this->getBlock($e);
		}

		return $return;	}

	private function getBlock($req){
		/*
			Создает запрос RRP из массива
		*/

		$return = '';
		foreach($req as $i => $e){
			if(!is_array($e)) $e = regExp::split("\n", $e);
			foreach($e as $e1){
				$return .= $i.":".trim($e1)."\n";
			}
		}

		return $return;
	}
}

?>