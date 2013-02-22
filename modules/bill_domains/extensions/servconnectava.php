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

class servconnectAva extends serverDomainsObject{

	private $reqResult;

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		return $this->sendReq('balance_get');
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

		switch($params['server_name']){			case 'ru':
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

			default:
				foreach(array('' => 'o_', '_a' => 'a_', '_b' => 'b_', '_t' => 't_') as $i => $e){					$params2 = $this->getBlankVars($this->obj->values['acc_domain_owner'.$i.$params['id']]);
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

		return $this->sendReq('domain_create', $vars);
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

		return $this->sendReq('domain_renew', array('domain_name' => $params['s_ident'], 'period' => $this->obj->values['term'.$params['id']]));
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
		return 'No';
	}

	public function listAccs($service, $params = array()){
		/*
			Усе акки
		*/
	}


	/*
		Внешние сугубо доменные
	*/

	public function __ava__ns($service, $params = array()){		/*
			Устанавливает новые ns
		*/

		if(!$this->sendReq('domain_get_nss', array('showip' => 1, 'domain_name' => $params['ident']))) return false;
		$return = array();
		$j = 1;

		foreach(regExp::split(',', $this->reqResult['Success']) as $i => $e){			$return['ns'.$j.'_'] = trim($e);
			$j ++;		}

		return $return;
	}

	public function __ava__newNs($service, $params = array()){
		/*
			Устанавливает новые ns
		*/

		$vars = $this->getNs($params['values']);
		if(!$vars['ns0']) $vars = array('undelegate' => 1);
		$vars['domain_name'] = $params['ident'];
		return $this->sendReq('domain_update_nss', $vars);
	}

	public function __ava__getWhois($service, $params = array()){		/*
			Устанавливает матрицу для смены данных Whois
		*/
		if(!$data = $this->sendReq('domain_get_contacts', array('domain_name' => $params['ident']))) return false;
		return $this->getDefaultContactMatrix($data);
	}

	public function __ava__setWhois($service, $params = array()){		/*
			Устанавливает сам Whois
		*/

		$vars = $this->obj->values;
		unset($vars['mod'], $vars['func'], $vars['id'], $vars['ava_form_transaction_id']);
		$vars['domain_name'] = $params['ident'];
		return $this->sendReq('domain_update_contacts', $vars);
	}


	/*
		Служебные
	*/

	private function sendReq($action, $vars = array()){
		/*
			Отправляет запрос
		*/

		$vars['action'] = $action;
		$vars['username'] = $this->connectData['login'];
		$vars['password'] = $this->connectData['pwd'];
		$vars['extended_message_lang'] = 'en';
		$vars = regExp::utf8($vars);

		return $this->parseReq($this->httpConnect($this->getHostAppendQuery('/api/regru'), array(), $vars, 'POST'));
	}

	private function parseReq($httpObj){
		/*
			Разбор ответа HTTP-запроса
		*/

		if(!$this->setErrorByHttp($httpObj)) return false;
		$this->reqResult = $this->parseRrpBlock($httpObj->getResponseBody());

		if(!empty($this->reqResult['Success'])){			$code = 0;
			$msg = '{Call:Lang:modules:bill_domains:zaprosvypoln:'.Library::serialize(array($this->reqResult['Success'])).'}';
			$return = true;		}
		elseif(!empty($this->reqResult['Error'])){			$code = 100;			$msg = $this->reqResult['Error'];
			$return = false;
		}
		else{			$code = 0;
			$msg = '{Call:Lang:modules:bill_domains:zaprosvypoln1}';
			$return = $this->reqResult;
		}

		$this->setErrorParams($code, $msg);
		return $return;	}

	private function getExtras($service, $params, $pref = '', $postf = ''){		/*
			Доп. параметры. Такие как privateWhois и т.п.
		*/

		$return = $this->getNs($params, $pref, $postf);
		if(!empty($this->obj->values['acc_'.$this->getParamsVar($service, 'private_person_flag').$params['id']])) $return['private_person_flag'] = 1;
		if(!empty($this->obj->values['acc_'.$this->getParamsVar($service, 'fail_if_no_money').$params['id']])) $return['fail_if_no_money'] = 1;
		if(!empty($this->obj->values['acc_'.$this->getParamsVar($service, 'no_bill_notify').$params['id']])) $return['no_bill_notify'] = 1;

		return $return;
	}

	private function getNs($params, $pref = '', $postf = ''){		/*
			Возвращает NS и IP к нимъ
		*/

		$return = array();
		for($i = 0; $i <= 3; $i ++){			if(!empty($params[$pref.'ns'.($i + 1).'_'.$postf])){				$ns = regExp::split("|\s+|", trim($params[$pref.'ns'.($i + 1).'_'.$postf]), true);
				$return['ns'.$i] = $ns[0];
				if(!empty($ns[1])) $return['ns'.$i.'ip'] = $ns[1];
			}		}

		return $return;	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(
				'private_person_flag' => array('type' => 'checkbox', 'text' => '{Call:Lang:modules:bill_domains:skrytdannyeo}')
			),
			'AVA-Panel',
			'bill_domains'
		);
	}
}

?>