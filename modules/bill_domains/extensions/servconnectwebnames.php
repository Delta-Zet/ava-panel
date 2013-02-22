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

class servconnectWebnames extends serverDomainsObject{

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		return $this->sendReq('pispBalance');
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

		$eData = $this->obj->getOrderEntry($params['id'], true);
		$pkgData = $this->obj->serviceData($service, $eData['package']);
		$vars = $this->getNs($eData['extra']['params1']);

		if(count($vars) < 2) $vars = array('ns0' => 'ns1.nameself.com', 'ns1' => 'ns2.nameself.com');
		if(!empty($eData['extra']['params3'][$this->getParamsVar($service, 'private_person')])) $vars['private_person'] = 1;

		$vars['domain_name'] = $eData['ident'];
		$vars['period'] = $eData['term'];

		switch($pkgData['server_name']){
			case 'ru':
			case 'su':
			case 'рф':
				$params2 = $this->getBlankVars($eData['extra']['params1']['domain_owner']);
				if(!$params2['address']) $params2['address'] = $this->getAddr($params2);
				if(!$params2['paddress']) $params2['paddress'] = $this->getAddr($params2, true, true);

				$vars['descr'] = 'Registered for client by '.$GLOBALS['Core']->Site->urlParse['host'];
				$vars['country'] = $params2['country'];
				$vars['p_addr'] = $params2['paddress'];

				$vars['phone'] = $params2['phone'];
				$vars['fax'] = $params2['fax'];
				$vars['e_mail'] = $params2['eml'];

				switch($params2['type']){
					case 'ip':
						$vars['code'] = $params2['ipInn'];

					case 'person':
						$vars['person'] = $this->getEnName($params2);
						$vars['person_r'] = $this->getName($params2);
						$vars['passport'] = $this->getPassport($params2);

						$vars['residence'] = $this->getAddr($params2, true);
						$vars['birth_date'] = date('d.m.Y', $params2['birth']);
						break;

					case 'organization':
						$vars['o_company'] = library::cyr2translit($params2['company']);
						$vars['code'] = $params2['inn'];
						$vars['kpp'] = $params2['kpp'];

						$vars['org'] = library::cyr2translit($params2['company']);
						$vars['org_r'] = $params2['company'];
						$vars['address_r'] = $params2['address'];

						break;
				}

				break;

			case 'uz':
			case 'tj':
				foreach(array('' => 'o_', '_a' => 'a_', '_b' => 'b_', '_t' => 't_') as $i => $e){
					$params2 = $this->getBlankVars($eData['extra']['params1']['domain_owner'.$i]);
					if(!$params2['company']) $params2['company'] = 'Private person';
					if($e != 'o_') $vars[$e.'nick'] = $e.$eData['extra']['params1']['domain_owner'.$i];

					$vars[$e.'name_ru'] = $this->getName($params2);
					$vars[$e.'name_en'] = $this->getEnName($params2);
					$vars[$e.'email'] = $params2['eml'];

					$vars[$e.'phone'] = $this->getEnPhone($params2['phone']);
					$vars[$e.'fax'] = $this->getEnPhone($params2['fax']);
					$vars[$e.'addr_ru'] = $params2['street'];

					$vars[$e.'addr_en'] = library::cyr2translit($params2['street']);
					$vars[$e.'city_ru'] = $params2['city'];
					$vars[$e.'city_en'] = library::cyr2translit($params2['city']);

					$vars[$e.'state_ru'] = $params2['region'];
					$vars[$e.'state_en'] = library::cyr2translit($params2['region']);
					$vars[$e.'postcode'] = $params2['zip'];
					$vars[$e.'country_code'] = $params2['country'];

					if($params2['type'] == 'organization'){
						$vars[$e.'company_ru'] = $params2['company'];
						$vars[$e.'company_en'] = library::cyr2translit($params2['company']);

						if($e == 'o_'){							$vars[$e.'code'] = $params2['inn'];
							$vars[$e.'bank'] = $params2['bank'];
							$vars[$e.'bank_account'] = $params2['bankNum'];
							$vars[$e.'mfo'] = $params2['bankBik'];
							$vars[$e.'okonh'] = '72.20';
						}
					}
				}
				break;

			case 'asia':
				$params2 = $this->getBlankVars($eData['extra']['params1']['domain_owner']);

				$vars['first_name'] = library::cyr2translit($params2['fname']);
				$vars['last_name'] = library::cyr2translit($params2['lname']);
				$vars['email'] = $params2['eml'];

				$vars['phone'] = $params2['phone'];
				$vars['fax'] = $params2['fax'];
				$vars['addr'] =  library::cyr2translit($params2['street']);

				$vars['city'] = library::cyr2translit($params2['city']);
				$vars['state'] = library::cyr2translit($params2['region']);
				$vars['postcode'] = $params2['zip'];

				$vars['country_code'] = $params2['country'];
				$vars['cclocality'] = 'CN';
				$vars['ident_form'] = 'other';
				$vars['ident_number'] = time() + microtime();

				switch($params2['type']){
					case 'ip':
					case 'person':
						$vars['company'] = 'Private person';
						$vars['entity_type'] = 'naturalPerson';
						break;

					case 'organization':
						$vars['company'] = library::cyr2translit($params2['company']);
						$vars['entity_type'] = 'corporation';
						break;
				}
				break;

			default:
				foreach(array('' => 'o_', '_a' => 'a_', '_b' => 'b_', '_t' => 't_') as $i => $e){
					$params2 = $this->getBlankVars($eData['extra']['params1']['domain_owner'.$i]);
					if(!$params2['company']) $params2['company'] = 'Private person';

					$vars[$e.'company'] = library::cyr2translit($params2['company']);
					$vars[$e.'first_name'] = library::cyr2translit($params2['fname']);
					$vars[$e.'last_name'] = library::cyr2translit($params2['lname']);

					$vars[$e.'email'] = $params2['eml'];
					$vars[$e.'phone'] = $this->getEnPhone($params2['phone']);
					$vars[$e.'fax'] = $this->getEnPhone($params2['fax']);

					$vars[$e.'addr'] = library::cyr2translit($params2['street']);
					$vars[$e.'city'] = library::cyr2translit($params2['city']);
					$vars[$e.'state'] = library::cyr2translit($params2['region']);

					$vars[$e.'postcode'] = $params2['zip'];
					$vars[$e.'country_code'] = $params2['country'];
				}
		}

		return $this->sendReq('pispRegistration', $vars);
	}

































	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:dannaiaopera}');
		return Library::arrayFill($params['pkgs'], true);
	}

	public function __ava__prolongAcc($service, $params = array()){
		/*
			Продливаит оккаунт пользоватиля. В данном случии нахуй не нужна
		*/

		return $this->sendReq('pispRenewDomain', array('domain_name' => $params['s_ident'], 'period' => $this->obj->values['term'.$params['id']]));
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
		Внешние сугубо доменные
	*/

	public function __ava__ns($service, $params = array()){
		/*
			Устанавливает новые ns
		*/

		if(!$this->sendReq('pispGetDelegation', array('domain_name' => $params['ident']))) return false;
		$return = array();
		$j = 1;

		foreach(regExp::split(',', $this->reqResult['Success']) as $i => $e){
			$return['ns'.$j.'_'] = trim($e);
			$j ++;
		}

		return $return;
	}

	public function __ava__newNs($service, $params = array()){
		/*
			Устанавливает новые ns
		*/

		$vars = $this->getNs($params['values']);
		$vars['domain_name'] = $params['ident'];
		return $this->sendReq('pispRedelegation', $vars);
	}

	public function __ava__getWhois($service, $params = array()){
		/*
			Устанавливает матрицу для смены данных Whois
		*/

		if(!$data = $this->sendReq('pispGetContactDetails', array('domain_name' => $params['ident']))) return false;
		return $this->getDefaultContactMatrix($data);
	}

	public function __ava__setWhois($service, $params = array()){
		/*
			Устанавливает сам Whois
		*/

		$vars = $this->obj->values;
		unset($vars['mod'], $vars['func'], $vars['id'], $vars['ava_form_transaction_id']);
		$vars['domain_name'] = $params['ident'];

		$z = bill_domains::getDomainZone($params['ident']);
		if($z == 'ru' || $z == 'su' || $z == 'info' || $z == 'biz' || $z == 'mobi' || $z == 'org' || $z == 'com' || $z == 'net' || $z == 'kz' || $z == 'uz' || $z == 'tj') $func = 'pispContactDetails';
		else $func = 'pispChangeOwnership';
		return $this->sendReq($func, $vars);
	}


	/*
		Служебные
	*/

	private function sendReq($action, $vars = array()){
		/*
			Отправляет запрос
		*/

		$vars['thisPage'] = $action;
		$vars['username'] = $this->connectData['login'];
		$vars['password'] = $this->connectData['pwd'];

		$vars['interface_revision'] = '1';
		$vars['interface_lang'] = 'en';
		$vars = regExp::utf8($vars);

		return $this->parseReq($this->httpConnect($this->getHostAppendQuery('/RegTimeSRS.pl'), array(), $vars, 'POST'));
	}

	private function parseReq($httpObj){
		/*
			Разбор ответа HTTP-запроса
		*/

		if(!$this->setErrorByHttp($httpObj)) return false;
		$result = regExp::Split(':', trim($httpObj->getResponseBody()), false, 2);
		$result[1] = empty($result[1]) ? '' : trim($result[1]);
		$return = false;

		if($result[0] == 'Success'){
			$code = 0;
			$result[1] = '{Call:Lang:modules:bill_domains:zaprosvypoln:'.Library::serialize(array($result[1])).'}';
			$return = true;
		}
		elseif($result[0] == 'Error'){			if($result[1] == 'username/password incorrect'){				$code = 10;
				$result[1] = '';			}
			else $code = 100;		}
		else $code = 7;

		$this->setErrorParams($code, $result[1]);
		return $return;
	}
	private function getNs($params){
		/*
			Возвращает NS и IP к нимъ
		*/

		$return = array();
		for($i = 0; $i <= 3; $i ++){
			if(!empty($params['ns'.($i + 1).'_'])){
				$ns = regExp::split("|\s+|", trim($params['ns'.($i + 1).'_']), true);
				$return['ns'.$i] = $ns[0];
				if(!empty($ns[1])) $return['ns'.$i.'ip'] = $ns[1];
			}
		}

		return $return;
	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(
				'private_person' => array('type' => 'checkbox', 'text' => '{Call:Lang:modules:bill_domains:skrytdannyeo}', 'name' => 'private_person_flag')
			),
			'{Call:Lang:modules:bill_domains:registratorw}',
			'bill_domains'
		);
	}
}

?>