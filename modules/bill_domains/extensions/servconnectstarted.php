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

class servconnectStarted extends serverDomainsObject{

	private $reqResult;
	private $reqResultParsed = array();

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		if(($res = $this->sendReq('objDomain', 'getSourcesList')) === false) return false;
		return true;
	}

	//Управление пакетами
	public function __ava__addPkg($service, $params = array()){
		/*
			Добавляет пакет
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return true;
	}

	public function __ava__delPkg($service, $params = array()){
		/*
			Удаление пакетов
			В передаваемом obj должен быть установлен список delPkg. Все они будут удалены
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return Library::arrayFill($params['pkgs'], true);
	}

	//Управление аккаунтами
	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

		if(!$userId = $this->getClientInServer($this->obj->values['acc_domain_owner'.$params['id']])){			$params2 = $this->getBlankVars($this->obj->values['acc_domain_owner'.$params['id']]);
			$pwd = Library::inventStr(8);
			list($cityType, $city, $addrType, $addr) = $this->getStartedAddr($params2);

			$vars = array(
				'email' => $params2['oEml'],
				'password' => $pwd,
				'fname' => $params2['oName'],
				'lname' => $params2['oLastName'],
				'mname' => $params2['oPName'],
				'country' => $params2['oCountry'],
				'state' => $params2['oRegion'],
				'city_type' => $cityType,
				'city' => $city,
				'addr_type' => $addrType,
				'addr' => $addr,
				'zip' => $params2['oZip'],
				'pstate' => $params2['oRegion'],
				'pcity_type' => $cityType,
				'pcity' => $city,
				'paddr_type' => $addrType,
				'paddr' => $addr,
				'pzip' => $params2['oZip'],
				'pto' => $params2['oName'],
				'tel' => $params2['oPhone'],
				'fax' => $params2['oFax']
			);

			switch($params2['domainOwnerType']){
				case 'i':
					$vars['p_inn'] = $params2[''];

				case 'p':
					list($vars['doc_serial'], $vars['doc_number']) = $this->getStartedPassport($params2['oPassport']);
					$vars['type'] = 'person';
					$vars['doc_issued'] = $params2['oPassportIssue'];
					$vars['doc_date'] = $params2['oPassportIssueDay'];
					$vars['birth_date'] = $params2['oBirth'];

					break;

				case 'o':
					$vars['type'] = 'organization';
					$vars['org'] = $params2['oCompany'];
					$vars['o_inn'] = $params2['oInn'];
					$vars['kpp'] = $params2['oKpp'];

					break;
			}

			if(!$result = $this->sendReq('objUser', 'create', $vars)) return false;
			$userId = $result['user_id'];
			$this->setClient($this->obj->values['acc_domain_owner'.$params['id']], $userId, $pwd);
		}

		if($this->sendReq(
			'objRequest',
			'create',
			array(
				'user_id' => $userId,
				'type' => 'domain',
				'domain' => $params['s_ident'],
				'ns' => implode(',', $this->getNs()),
				'who_pay' => 'reseller',
				'operation' => 'register'
			)
		)) return true;

		return false;
	}

	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return Library::arrayFill($params['pkgs'], true);
	}

	public function prolongAcc($service, $params = array()){
		/*
			Продливаит оккаунт пользоватиля. В данном случии нахуй не нужна
		*/

		return true;
	}

	public function __ava__delAcc($service, $params = array()){
		/*
			Удоляит аккаунт пользоватиля
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return Library::arrayFill($params['pkgs'], true);
	}

	public function __ava__suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return Library::arrayFill($params['pkgs'], true);
	}

	public function __ava__unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return Library::arrayFill($params['pkgs'], true);
	}

	public function __ava__isSuspendAcc($service, $params = array()){
		/*
			Проверяи чта акк из суспиндит
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return Library::arrayFill($params['pkgs'], true);
	}

	public function listAccs($service, $params = array()){
		/*
			Усе акки
		*/
	}


	/*
		Служебные
	*/

	private function sendReq($obj, $method, $vars = array()){
		/*
			Отправляет запрос
		*/

		$vars['method'] = $method;
		$vars = array(
			'RequestBody' => array(
				'reseller' => array(
					'login' => $this->connectData['login'],
					'password' => $this->connectData['pwd'],
				),
				$obj => $vars
			)
		);

		return $this->parseReturn(
			$this->httpConnect(
				$this->connectData['host'],
				array(
					'Content-type' => 'text/xml'
				),
				XML::getFullXML($vars),
				'POST'
			)
		);
	}

	private function parseReturn(httpClient $httpObj){
		/*
			Разбирает ответ Started
		*/

		$this->reqResult = $httpObj->getResponseBody();
		if(!$this->setErrorByHttp($httpObj)) return false;
		$this->reqResultParsed = XML::parseXML($this->reqResult);

		if(!isset($this->reqResultParsed['AnswerBody'])){			$this->setErrorParams(7);
			return false;
		}

		if(!empty($this->reqResultParsed['AnswerBody']['statusCode'])){
			if(!empty($this->reqResultParsed['AnswerBody']['statusMessage'])) $msg = ' ('.$this->reqResultParsed['AnswerBody']['statusMessage'].')';
			else $msg = '';

			switch(trim($this->reqResultParsed['AnswerBody']['statusCode'])){				case '500':
					switch($this->reqResultParsed['AnswerBody']['statusMessage']){						case 'auth failed':							$this->setErrorParams(10);
							return false;
					}

					$this->setErrorParams(6, '{Call:Lang:modules:bill_domains:nekorrektnyj2:'.Library::serialize(array($msg)).'}');
					return false;			}
		}

		return $this->reqResultParsed['AnswerBody'];
	}

	private function getStartedPassport($num){		/*
			Возвращает серию и номер паспорта для Started
		*/
		if(regExp::match("/^([\wА-Яа-яёЁ]+)(.+)$/is", $num, true, true, $m)){
			return array($m['1'], trim($m['2']));
		}

		$nums = explode(' ', trim($num));
		$i = count($nums) - 1;
		$numb = $nums[$i];
		unset($nums[$i]);

		return array(implode('', $nums), $numb);
	}

	private function getStartedAddr($values){		/*
			Возвращает параметры адреса для Started
		*/

		$city_type = 'city';
		$addr_type = 'street';
		$addr = trim(regExp::replace(array('{Call:Lang:modules:bill_domains:ul}', '{Call:Lang:modules:bill_domains:ul1}', '{Call:Lang:modules:bill_domains:prosp}', '{Call:Lang:modules:bill_domains:prosp1}', '{Call:Lang:modules:bill_domains:pr}', '{Call:Lang:modules:bill_domains:pr1}', '{Call:Lang:modules:bill_domains:ulitsa}', 'ul', 'ul.', 'str', 'str.', 'street'), '', $values['street']));
		$city = trim(str_replace(array('{Call:Lang:modules:bill_domains:g}', '{Call:Lang:modules:bill_domains:g1}', '{Call:Lang:modules:bill_domains:gor}', '{Call:Lang:modules:bill_domains:gor1}', '{Call:Lang:modules:bill_domains:s}', '{Call:Lang:modules:bill_domains:s1}', '{Call:Lang:modules:bill_domains:selo}', '{Call:Lang:modules:bill_domains:pos}', '{Call:Lang:modules:bill_domains:pos1}', '{Call:Lang:modules:bill_domains:poselok}', '{Call:Lang:modules:bill_domains:pgt}', '{Call:Lang:modules:bill_domains:pgt1}', '{Call:Lang:modules:bill_domains:mkr}', '{Call:Lang:modules:bill_domains:mkr1}', '{Call:Lang:modules:bill_domains:mikrorajon}'), '', $values['city']));

		return array($city_type, $city, $addr_type, $addr);
	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(),
			'{Call:Lang:modules:bill_domains:registrators}',
			'bill_domains'
		);
	}
}

?>