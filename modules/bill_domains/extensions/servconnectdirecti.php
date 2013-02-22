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

class servconnectDirecti extends serverDomainsObject{

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		$this->obj->values['vars']['invoiceType'] = $this->obj->values['invoiceType'];
		if($this->sendReq('products/customer-price')) return true;
		return false;
	}

	public function setConnectMatrix($service = '', $params = array()){
		/*
			Матрица для коннекта. Позволяет
				- Указать используется для коннекта пароль или хеш
		*/

		$params['fObj']->setMatrix(
			array(
				'login' => array(
					'text' => 'ID реселера',
					'comment' => 'Вы можете узнать его зайдя в раздел вашего аккаунта у Directi Настройки -> Личная информация -> Главный профиль'
				),
				'invoiceType' => array(
					'text' => 'Способ списания денежных средств',
					'type' => 'select',
					'comment' => 'Если вы не знаете что тут указать, оставте первый пункт списка',
					'additional' => array(
						'NoInvoice' => 'Оплата с вашего счета',
						'PayInvoice' => 'Оплата с немедленным списанием средств со счета клиента',
						'KeepInvoice' => 'Оплата со счета клиента с выставлением ему счета',
						'OnlyAdd' => 'Оплата со счета клиента с выставлением ему счета. Запрос будет отложен',
					)
				),
			),
			'form'
		);
	}

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

	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
			Зоны com, net, org, info, biz, ws, us, eu, in, name, net.in, cc, tv, bz, mobi, mn, de.com, gb.net, asia, org.co, me, tel, net.nz, coop, co, ca, de, es, com.bz, com.au, co.bz, xxx
		*/

		$eData = $this->obj->getOrderEntry($params['id'], true);
		$pkgData = $this->obj->serviceData($service, $eData['package']);

		//Ищем id клиента. Если его нет, создаем нового
		if(!$client = $this->setClient($eData['extra']['params1']['domain_owner'], $eData['client_id'])) return false;

		//Ищем контакты. Если нет, создаем новые
		$blanks = array();
		foreach(array('', '_a', '_b', '_t') as $e){
			if(!$blanks[$e] = $this->setContact($eData['extra']['params1']['domain_owner'.$e], $client, $eData['ident'])) return false;
		}

		//Регистрируем доменъ
		$result = $this->sendReq(
			'domains/register',
			array(
				'domain-name' => $eData['ident'],
				'years' => $eData['term'],
				'ns' => $this->getNs($eData['extra']['params1']),
				'customer-id' => $client,
				'reg-contact-id' => $blanks[''],
				'admin-contact-id' => $blanks['_a'],
				'tech-contact-id' => $blanks['_t'],
				'billing-contact-id' => $blanks['_b'],
				'invoice-option' => $this->connectData['invoiceType'],
				'protect-privacy' => 'false'
			)
		);

		if($result) return true;
		return false;
	}

	public function modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return false;
	}

	public function prolongAcc($service, $params = array()){
		/*
			Продливаит оккаунт пользоватиля. В данном случии нахуй не нужна
		*/

		$eData = $this->obj->getOrderEntry($params['id'], true);
		if(!$domData = $this->getDomainDetails($eData['s_ident'], 'OrderDetails')) return false;

		$result = $this->sendReq(
			'domains/renew',
			array(
				'order-id' => $domData->orderid,
				'years' => $eData['term'],
				'exp-date' => $domData->endtime,
				'invoice-option' => $this->connectData['invoiceType']
			)
		);

		if($result) return true;
		return false;
	}

	public function delAcc($service, $params = array()){
		/*
			Удоляит аккаунт пользоватиля
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return false;
	}

	public function suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return false;
	}

	public function unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return false;
	}

	public function isSuspendAcc($service, $params = array()){
		/*
			Проверяи чта акк из суспиндит
		*/

		return false;
	}

	public function listAccs($service, $params = array()){
		/*
			Усе акки
		*/

		return array();
	}


	/****************************************************************************************************************************************************************************************************************

																					Внешние функции сугубо доменного назначения

	****************************************************************************************************************************************************************************************************************/

	public function __ava__ns($service, $params = array()){
		/*
			Сообщает установленные для домена NS
		*/

		if(!$domData = $this->getDomainDetails($params['ident'], 'NsDetails')) return false;
		$return = array();
		for($i = 1; $i <= 4; $i ++) if(isset($domData->{'ns'.$i})) $return['ns'.$i.'_'] = trim($domData->{'ns'.$i});
		return $return;
	}

	public function __ava__newNs($service, $params = array()){
		/*
			Устанавливает новые ns
		*/

		if(!$domId = $this->getDomainId($params['ident'])) return false;
		return (bool)$this->sendReq(
			'domains/modify-ns',
			array(
				'order-id' => $domId,
				'ns' => $this->getNs($params['values'])
			)
		);
	}

	public function __ava__setWhois($service, $params = array()){
		/*
			Устанавливает сам Whois
		*/

		if(!$domId = $this->getDomainId($params['ident'])) return false;
		$sData = $this->obj->getOrderedService($params['id']);
		$client = $this->getClientInServer($params['domain_owner']);

		if(!$client = $this->setClient($params['domain_owner'], $sData['client_id'])) return false;
		foreach(array('', '_a', '_b', '_t') as $e){
			$blanks[$e] = $this->setContact($params['domain_owner'.$e], $client, $sData['ident']);
		}

		return (bool)$this->sendReq(
			'domains/modify-contact',
			array(
				'order-id' => $domId,
				'reg-contact-id' => $blanks[''],
				'admin-contact-id' => $blanks['_a'],
				'tech-contact-id' => $blanks['_t'],
				'billing-contact-id' => $blanks['_b'],
			)
		);
	}


	/****************************************************************************************************************************************************************************************************************

																										Служебные

	****************************************************************************************************************************************************************************************************************/

	private function setClient($owner, $clientId){
		/*
			Добавляет клиента
		*/

		if(!$client = $this->getClientInServer($owner)){
			//Если клиент не существует, создаем его
			$params2 = $this->getBlankVars($owner);

			if($cParams = $this->getClientInServerByEmail($clientId, $params2['eml'])){
				$client = $cParams['name'];
				$pwd = $cParams['pwd'];
			}
			else{
				$pwd = Library::inventPass();
				list($telCC, $telNo) = $this->getTelNo($params2['phone']);
				list($faxCC, $faxNo) = $this->getTelNo($params2['fax']);

				$client = $this->sendReq(
					'customers/signup',
					array(
						'username' => $params2['eml'],
						'passwd' => $pwd,
						'name' => $this->getEnName($params2),
						'company' => empty($params2['company']) ? 'n/a' : library::cyr2translit($params2['company']),
						'address-line-1' => library::cyr2translit($params2['street']),
						'city' => library::cyr2translit($params2['city']),
						'state' => library::cyr2translit($params2['region']),
						'country' => $params2['country'],
						'zipcode' => $params2['zip'],
						'phone-cc' => $telCC,
						'phone' => $telNo,
						'fax-cc' => $faxCC,
						'fax' => $faxNo
					),
					'int'
				);

				if(!$client) return false;
			}

			$this->setClient($owner, $client, $pwd);
		}

		return $client;
	}

	private function setContact($id, $client, $domain){		/*
			Создает контакт для домена
		*/
		if(!$return = $this->getBlankInServer($id)){
			$params2 = $this->getBlankVars($id);
			list($telCC, $telNo) = $this->getTelNo($params2['phone']);
			list($faxCC, $faxNo) = $this->getTelNo($params2['fax']);

			$sendData = array(
				'customer-id' => $client,
				'name' => $this->getEnName($params2),
				'company' => empty($params2['company']) ? 'n/a' : library::cyr2translit($params2['company']),
				'email' => $params2['eml'],
				'address-line-1' => library::cyr2translit($params2['street']),
				'city' => library::cyr2translit($params2['city']),
				'state' => library::cyr2translit($params2['region']),
				'country' => $params2['country'],
				'zipcode' => $params2['zip'],
				'phone-cc' => $telCC,
				'phone' => $telNo,
				'fax-cc' => $faxCC,
				'fax' => $faxNo
			);

			switch($z = $this->sObj->getDomainZone($domain)){
				case 'coop':
					$sendData['type'] = 'CoopContact';
					break;

				case 'eu':
					$sendData['type'] = 'EuContact';
					break;

				case 'cn':
					$sendData['type'] = 'CnContact';
					break;

				case 'co':
					$sendData['type'] = 'CoContact';
					break;

				case 'ca':
					$sendData['type'] = 'CaContact';
					break;

				case 'de':
					$sendData['type'] = 'DeContact';
					break;

				case 'es':
					$sendData['type'] = 'EsContact';
					break;

				case 'asia':
					$sendData['attr-name1'] = 'locality';
					$sendData['attr-value1'] = $params2['country'];

					$sendData['attr-name2'] = 'legalentitytype';
					$sendData['attr-name4'] = 'identform';
					$sendData['attr-name6'] = 'identnumber';

					if(!empty($params2['default_ced'])){
						$sendData['attr-value6'] = rand(100000, 999999);
						switch($params2['type']){
							case 'ip':
							case 'person':
								$sendData['attr-value2'] = 'naturalPerson';
								$sendData['attr-value4'] = 'passport';
								break;

							case 'organization':
								$sendData['attr-value2'] = 'corporation';
								$sendData['attr-value4'] = 'certificate';
								break;
						}
					}
					else{
						$sendData['attr-value2'] = $params2['entity_type'];
						$sendData['attr-value4'] = $params2['ident_form'];
						$sendData['attr-value6'] = $params2['ident_number'];
					}

				case 'us':
					if($z == 'us'){
						$sendData['attr-name1'] = 'purpose';
						$sendData['attr-value1'] = $params2['purpose'];
						$sendData['attr-name2'] = 'category';
						$sendData['attr-value2'] = $params2['us_citizen'];
					}

				default: $sendData['type'] = 'Contact';
			}

			$return = $this->sendReq('contacts/add', $sendData, 'int');
			if(!$return) return false;
			$this->setBlank($id, $return);
		}

		return $return;
	}

	private function getDomainId($domain){		/*
			Возвращает ID домена
		*/

		return $this->sendReq('domains/orderid', array('domain-name' => $domain), 'int');
	}

	private function getDomainDetails($domain, $type = 'All'){		/*
			Возвращает сведения о домене
		*/
		if(!$domId = $this->getDomainId($domain)) return false;
		if(!$domData = $this->sendReq('domains/details', array('order-id' => $domId, 'options' => $type))) return false;
		return $domData;
	}

	private function sendReq($path, $params = array(), $return = 'map', $extras = array()){
		/*
			Отправляет запрос
		*/

		$path .= '.json?auth-userid='.urlencode($this->connectData['login']).'&auth-password='.urlencode($this->connectData['pwd']).'&lang-pref=en';
		foreach($params as $i => $e){			if(is_array($e)) foreach($e as $i1 => $e1) $path .= '&'.$i.'='.urlencode($e1);
			else $path .= '&'.$i.'='.urlencode($e);		}

		$j = 0;
		foreach($extras as $i => $e){
			$path .= '&attr-name'.$j.'='.$i.'&attr-value'.$j.'='.urlencode($e);
			$j ++;
		}

		return $this->parseReq($this->httpConnect(regExp::replace("|/$|", '', $this->connectData['host'], true).'/'.$path), $return);
	}

	private function parseReq($httpObj, $return = 'map'){
		/*
			Разбор ответа JSON
		*/

		$code = $httpObj->getResponseCode();
		if($code != 200 && $code != 500){			$this->setErrorByHttp($httpObj);
			return false;		}

		$result = json_decode(trim($httpObj->getResponseBody()));
		if(is_object($result) && isset($result->status) && regExp::lower($result->status) == 'error'){
			if(isset($result->message) && $result->message == 'Invalid Password/UserId, or your User account maybe Inactive or Suspended') $this->setErrorParams(10, $result->message);
			elseif(isset($result->error) && $result->error == 'Invalid Password/UserId, or your User account maybe Inactive or Suspended') $this->setErrorParams(10, $result->error);
			elseif(isset($result->message)) $this->setErrorParams(100, $result->message);
			elseif(isset($result->error)) $this->setErrorParams(100, $result->error);
			return false;
		}
		elseif(($return == 'map' && !is_object($result)) || ($return == 'int' && !is_integer($result)) || ($return == 'float' && !is_float($result) && !is_integer($result)) || ($return == 'str' && !is_string($result))){
			$this->setErrorParams(7);
			return false;
		}

		return $result;
	}

	private function getTelNo($no){		$parts = regExp::Split(" ", regExp::Replace("+", '', $no));
		$cc = $parts['0'];
		unset($parts['0']);
		return array($cc, implode('', $parts));	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array('private_person' => array('type' => 'checkbox', 'text' => '{Call:Lang:modules:bill_domains:skrytdannyeo}', 'name' => 'privacyprotected')),
			'{Call:Lang:modules:bill_domains:naosnovedire}',
			'bill_domains'
		);
	}
}

?>