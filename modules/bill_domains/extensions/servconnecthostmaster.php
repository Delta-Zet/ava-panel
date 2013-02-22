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

class servconnectHostmaster extends serverDomainsObject{

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

		$vars = $this->getExtras($service, $this->obj->values, 'acc_', $params['id']);
		$vars['domain_name'] = $params['s_ident'];
		$vars['period'] = $this->obj->values['term'.$params['id']];

		switch($params['server_name']){
			case 'ru':
			case 'su':
				$params2 = $this->getBlankVars($this->obj->values['acc_domain_owner'.$params['id']]);
				if(!$params2['oUAddress']) $params2['oUAddress'] = $this->getAddr($params2);
				if(!$params2['oPAddress']) $params2['oPAddress'] = $this->getAddr($params2, true);

				$vars['descr'] = 'Registered for client by '.$GLOBALS['Core']->Site->urlParse['host'];
				$vars['country'] = $params2['oCountry'];
				$vars['p_addr'] = $params2['oPAddress'];

				$vars['phone'] = $params2['oPhone'];
				$vars['fax'] = $params2['oFax'];
				$vars['e_mail'] = $params2['oEml'];

				switch($params2['domainOwnerType']){
					case 'i':
						$vars['code'] = $params2['oIpInn'];

					case 'p':
						$vars['o_company'] = 'Private person';
						$vars['person'] = $this->getEnName($params2);
						$vars['person_r'] = $this->getName($params2);

						$vars['passport'] = $this->getPassport($params2);
						$vars['birth_date'] = $params2['oBirth'];

						break;


					case 'o':
						$vars['o_company'] = library::cyr2translit($params2['oCompany']);
						$vars['code'] = $params2['oInn'];
						$vars['kpp'] = $params2['oKpp'];

						$vars['org'] = library::cyr2translit($params2['oCompany']);
						$vars['org_r'] = $params2['oCompany'];
						$vars['address_r'] = $params2['oUAddress'];

						break;
				}
				break;

			case 'kz':
			case 'uz':
			case 'tj':
				foreach(array('' => 'o_', '_a' => 'a_', '_b' => 'b_', '_t' => 't_') as $i => $e){
					$params2 = $this->getBlankVars($this->obj->values['acc_domain_owner'.$i.$params['id']]);
					if(!$params2['oCompany']) $params2['oCompany'] = 'Private person';
					if($e != 'o_') $vars[$e.'nick'] = $e.$this->obj->values['acc_domain_owner'.$i.$params['id']];

					$vars[$e.'name_ru'] = $this->getName($params2);
					$vars[$e.'name_en'] = $this->getEnName($params2);
					$vars[$e.'email'] = $params2['oEml'];

					$vars[$e.'phone'] = $this->getEnPhone($params2['oPhone']);
					$vars[$e.'fax'] = $this->getEnPhone($params2['oFax']);
					$vars[$e.'addr_ru'] = $params2['oStreet'];

					$vars[$e.'addr_en'] = library::cyr2translit($params2['oStreet']);
					$vars[$e.'city_ru'] = $params2['oCity'];
					$vars[$e.'city_en'] = library::cyr2translit($params2['oCity']);

					$vars[$e.'state_ru'] = $params2['oRegion'];
					$vars[$e.'state_en'] = library::cyr2translit($params2['oRegion']);
					$vars[$e.'postcode'] = $params2['oZip'];
					$vars[$e.'country_code'] = $params2['oCountry'];

					if($params2['domainOwnerType'] == 'o'){
						$vars[$e.'company_ru'] = $params2['oCompany'];
						$vars[$e.'company_en'] = library::cyr2translit($params2['oCompany']);

						if($e == 'o_'){							$vars[$e.'code'] = $params2['oInn'];
							$vars[$e.'bank'] = $params2['oBank'];
							$vars[$e.'bank_account'] = $params2['oBankNum'];
							$vars[$e.'mfo'] = $params2['oBankBik'];
							$vars[$e.'okonh'] = '72.20';
						}
					}
				}
				break;

			case 'asia':
				$params2 = $this->getBlankVars($this->obj->values['acc_domain_owner'.$params['id']]);

				$vars['first_name'] = library::cyr2translit($params2['oName']);
				$vars['last_name'] = library::cyr2translit($params2['oLastName']);
				$vars['email'] = $params2['oEml'];

				$vars['phone'] = $params2['oPhone'];
				$vars['fax'] = $params2['oFax'];
				$vars['addr'] =  library::cyr2translit($params2['oStreet']);

				$vars['city'] = library::cyr2translit($params2['oCity']);
				$vars['state'] = library::cyr2translit($params2['oRegion']);
				$vars['postcode'] = $params2['oZip'];

				$vars['country_code'] = $params2['oCountry'];
				$vars['cclocality'] = 'CN';
				$vars['ident_form'] = 'other';
				$vars['ident_number'] = time() + microtime();

				switch($params2['domainOwnerType']){
					case 'i':
					case 'p':
						$vars['company'] = 'Private person';
						$vars['entity_type'] = 'naturalPerson';
						break;

					case 'o':
						$vars['company'] = library::cyr2translit($params2['oCompany']);
						$vars['entity_type'] = 'corporation';
						break;
				}
				break;

			default:
				foreach(array('' => 'o_', '_a' => 'a_', '_b' => 'b_', '_t' => 't_') as $i => $e){
					$params2 = $this->getBlankVars($this->obj->values['acc_domain_owner'.$i.$params['id']]);
					if(!$params2['oCompany']) $params2['oCompany'] = 'Private person';

					$vars[$e.'company'] = library::cyr2translit($params2['oCompany']);
					$vars[$e.'first_name'] = library::cyr2translit($params2['oName']);
					$vars[$e.'last_name'] = library::cyr2translit($params2['oLastName']);

					$vars[$e.'email'] = $params2['oEml'];
					$vars[$e.'phone'] = $this->getEnPhone($params2['oPhone']);
					$vars[$e.'fax'] = $this->getEnPhone($params2['oFax']);

					$vars[$e.'addr'] = library::cyr2translit($params2['oStreet']);
					$vars[$e.'city'] = library::cyr2translit($params2['oCity']);
					$vars[$e.'state'] = library::cyr2translit($params2['oRegion']);

					$vars[$e.'postcode'] = $params2['oZip'];
					$vars[$e.'country_code'] = $params2['oCountry'];
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

	private function getExtras($service, $params, $pref = '', $postf = ''){
		/*
			Доп. параметры. Такие как privateWhois и т.п.
		*/

		$return = $this->getNs($params, $pref, $postf);
		if(!empty($this->obj->values['acc_'.$this->getParamsVar($service, 'private_person').$params['id']])) $return['private_person'] = 1;
		return $return;
	}

	private function getNs($params, $pref = '', $postf = ''){
		/*
			Возвращает NS и IP к нимъ
		*/

		$return = array();
		for($i = 0; $i <= 3; $i ++){
			if(!empty($params[$pref.'ns'.($i + 1).'_'.$postf])){
				$ns = regExp::split("|\s+|", trim($params[$pref.'ns'.($i + 1).'_'.$postf]), true);
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
		return array(array(), 'Hostmaster', 'bill_domains');
	}
}

?>