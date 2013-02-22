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

class servconnectReg extends serverDomainsObject{

	private $reqResult;

	public function setConnectMatrix($service = '', $params = array()){
		/*
			Матрица для коннекта. Позволяет
				- Указать используется для коннекта пароль или хеш
		*/

		$params['fObj']->setMatrix(
			array(
				'api' => array(
					'type' => 'select',
					'text' => 'Версия API',
					'additional' => array(
						'' => 'API 2',
						'1' => 'API 1',
					)
				)
			),
			'form'
		);
	}

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		$this->obj->values['vars']['api'] = $this->connectData['api'] = $this->obj->values['api'];
		return $this->sendReq('user', ($this->connectData['api'] == '1') ? 'balance_get' : 'get_balance');
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
		if(count($vars) < 2) $vars = array('ns0' => 'ns1.reg.ru', 'ns1' => 'ns2.reg.ru');

		if(!empty($eData['extra']['params3'][$this->getParamsVar($service, 'private_person_flag')])) $vars['private_person_flag'] = 1;
		if(!empty($eData['extra']['params3'][$this->getParamsVar($service, 'fail_if_no_money')])) $vars['fail_if_no_money'] = 1;
		if(!empty($eData['extra']['params3'][$this->getParamsVar($service, 'no_bill_notify')])) $vars['no_bill_notify'] = 1;

		$vars['domain_name'] = $eData['ident'];
		$vars['period'] = $eData['term'];

		switch($pkgData['server_name']){			case 'ru':
			case 'su':
				$params2 = $this->getBlankVars($eData['extra']['params1']['domain_owner']);
				if(!$params2['address']) $params2['address'] = $this->getAddr($params2, true);
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

			default:
				foreach(array('' => 'o_', '_a' => 'a_', '_b' => 'b_', '_t' => 't_') as $i => $e){					$params2 = $this->getBlankVars($eData['extra']['params1']['domain_owner'.$i]);
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

		return $this->sendReq('domain', ($this->connectData['api'] == '1') ? 'domain_create' : 'create', $vars);
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

	private function sendReq($actionCat, $action, $vars = array()){
		/*
			Отправляет запрос
		*/

		$vars['action'] = $action;
		$vars['username'] = $this->connectData['login'];
		$vars['password'] = $this->connectData['pwd'];
		$vars['extended_message_lang'] = 'en';

		if($this->connectData['api'] == '1'){			$path = $this->getHostAppendQuery('/api/regru');			$vars = regExp::utf8($vars);
		}		else $path = $this->getHostAppendQuery('/api/regru2/').$actionCat.'/'.$action;
		return $this->parseReq($this->httpConnect($path, array(), $vars, 'POST'));
	}

	private function parseReq($httpObj){
		/*
			Разбор ответа HTTP-запроса
		*/

		$api = $this->connectData['api'] == '1';

		if(!$this->setErrorByHttp($httpObj)) return false;
		elseif($api == '1'){			if(!$this->reqResult = $this->parseRrpBlock($httpObj->getResponseBody())) return false;
			if(!empty($this->reqResult['Success'])) $result = 'success';
			elseif(!empty($this->reqResult['Error'])){				$result = 'error';
				$errorText = $this->reqResult['Error'];			}			else $result = '';
		}
		else{			if(!$this->reqResult = json_decode($httpObj->getResponseBody())) return false;			$result = $this->reqResult->result;
			if($result == 'error') $errorText = $this->reqResult->error_text;
		}

		if($result == 'success'){			$code = 0;
			$msg = '{Call:Lang:modules:bill_domains:zaprosvypoln}';
			$return = true;		}
		elseif($result == 'error'){			$code = 100;			$msg = $errorText;
			$return = false;
		}
		else{			$code = 6;
			$msg = 'Ошибка разбора запроса';
			$return = false;
		}

		$this->setErrorParams($code, $msg);
		return $return;	}

	private function getNs($params){		/*
			Возвращает NS и IP к нимъ
		*/

		$return = array();
		for($i = 0; $i <= 3; $i ++){			if(!empty($params['ns'.($i + 1).'_'])){				$ns = regExp::split("|\s+|", trim($params['ns'.($i + 1).'_']), true);
				$return['ns'.$i] = $ns[0];
				if(!empty($ns[1])) $return['ns'.$i.'ip'] = $ns[1];
			}		}

		return $return;	}

	private function getPassport($params){
		/*
			Выдает паспортные данные
		*/

		if($params['country'] == 'RU'){
			$params['passport'] = regExp::replace("|\D|", "", $params['passport'], true);
			$params['passport'] = $params['passport'][0].$params['passport'][1].$params['passport'][2].$params['passport'][3].' '.$params['passport'][4].$params['passport'][5].$params['passport'][6].$params['passport'][7].$params['passport'][8].$params['passport'][9];
		}

		return $params['passport'].', выдан '.$params['passportIssue'].($params['passportIssueDay'] ? ' '.date('d.m.Y', $params['passportIssueDay']) : '');
	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(
				'private_person_flag' => array('type' => 'checkbox', 'text' => '{Call:Lang:modules:bill_domains:skrytdannyeo}'),
				'fail_if_no_money' => array('type' => 'checkbox', 'text' => '{Call:Lang:modules:bill_domains:soobshchitob}'),
				'no_bill_notify' => array('type' => 'checkbox', 'text' => '{Call:Lang:modules:bill_domains:nevysylatuve}'),
			),
			'{Call:Lang:modules:bill_domains:naosnoveregr}',
			'bill_domains'
		);
	}
}

?>