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

class servconnectNaunet extends serverDomainsObject{
	private $reqResult;
	private $reqResultParsed = array();

	public function setConnectMatrix($service = '', $params = array()){
		/*
			Матрица для коннекта. Позволяет
				- Указать используется для коннекта пароль или хеш
		*/

		$params['fObj']->setMatrix(
			array(
				'mode' => array(
					'type' => 'select',
					'text' => '{Call:Lang:modules:bill_domains:rezhim}',
					'additional' => array(
						'async' => '{Call:Lang:modules:bill_domains:asinkhronnyj}',
						'' => '{Call:Lang:modules:bill_domains:sinkhronnyj}'
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

		$this->obj->values['vars']['mode'] = !empty($this->obj->values['mode']);
		$this->sendReq('GET', array('domain' => 'ya.ru'));
		if($this->reqResultParsed['1']['2'] == 'unknown_domain') return true;
		return false;
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

		$params2 = $this->getBlankVars($this->obj->values[$params['prefix'].'domain_owner'.$params['id']]);
		$vars = array(
			'domain' => $this->obj->values[$params['prefix'].'ident'.$params['id']],
			'nserver' => $this->getNs($this->obj->values, $params['prefix'], $params['id'])
		);

		if($vars['nserver']) $vars['state'] = 'DELEGATED';
		$issetLogin = true;

		if(!$clientLogin = $this->getClientInServer($this->obj->values[$params['prefix'].'domain_owner'.$params['id']])){
			$issetLogin = false;
			if(!$params2['address']) $params2['address'] = $this->getAddr($params2, true);
			if(!$params2['paddress']) $params2['paddress'] = $this->getAddr($params2, true, true);

			$vars['contact-login'] = 'admin@'.$vars['domain'];
			$vars['p-addr'] = $params2['paddress'];
			$vars['phone'] = $params2['phone'];

			$vars['fax-no'] = $params2['fax'];
			$vars['e-mail'] = $params2['eml'];
			$vars['address-r'] = $params2['address'];

			switch($params2['type']){
				case 'ip':
					$vars['code'] = $params2['ipInn'];

				case 'person':
					$vars['person'] = $this->getEnName3($params2);
					$vars['person-r'] = $this->getName($params2);
					$vars['passport'] = $this->getPassport($params2);
					$vars['birth-date'] = date('d.m.Y', $params2['birth']);

					break;

				case 'organization':
					$vars['org'] = library::cyr2translit($params2['company']);
					$vars['org-r'] = $params2['company'];
					$vars['code'] = $params2['inn'];
					$vars['kpp'] = $params2['kpp'];
					$vars['ogrn'] = $params2['ogrn'];

					break;
			}
		}
		else{			$vars['contact-login'] = $clientLogin;		}

		if($result = $this->sendReq('NEW', $vars)){
			if(!$issetLogin) $this->setClient($this->obj->values[$params['prefix'].'domain_owner'.$params['id']], $clientLogin, '');
			return true;		}

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

	private function sendReq($action, $vars = array()){
		/*
			Отправляет запрос
		*/

		$vars['action'] = $action;
		$vars['login'] = $this->connectData['login'];
		$vars['passwd'] = $this->connectData['pwd'];
		$vars = regExp::utf8($vars);

		$send = '';
		foreach($vars as $i => $e){			if(is_array($e)) foreach($e as $i1 => $e1) $send .= $i.'='.urlencode($e1).'&';
			else $send .= $i.'='.urlencode($e).'&';		}

		return $this->parseReturn(
			$this->httpConnect(
				$this->getHostAppendQuery('/c/registrar?mode='.!empty($this->connectData['mode'])),
				array(),
				$send,
				'POST'
			)
		);
	}

	private function parseReturn(httpClient $httpObj){		/*
			Разбирает ответ Naunet
		*/

		$this->reqResult = $httpObj->getResponseBody();
		if(!$this->setErrorByHttp($httpObj)) return false;
		$res = regExp::split(':', trim(strip_tags($this->reqResult)), false, 2);

		if(empty($res['1'])){			$this->setErrorParams(7);
			return false;		}

		$res['1'] = regExp::split(' ', trim($res['1']), false, 3);
		$this->reqResultParsed = $res;
		if(!isset($res['1']['2'])) $res['1']['1'] = '';
		else $res['1']['2'] = ' ('.$res['1']['2'].')';

		switch($res['1']['1']){			case 'dns_check':
			case 'done':
				$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:zaiavkavypol:'.Library::serialize(array($res['1']['2'])).'}');
				return true;

			case 'in_progress':
			case 'checked':
				$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:zaiavkaispol:'.Library::serialize(array($res['1']['2'])).'}');
				return true;

			case 'money_wait':
				$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:zaiavkabudet:'.Library::serialize(array($res['1']['2'])).'}');
				return true;

			case 'processing_wait':
				$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:zaiavkaozhid:'.Library::serialize(array($res['1']['2'])).'}');
				return true;

			case 'manual_check':
			case 'docs_wait':
				$this->setErrorParams(0, '{Call:Lang:modules:bill_domains:zaiavkabudet1:'.Library::serialize(array($res['1']['2'])).'}');
				return true;

			case 'wrong_auth':
			case 'wrong_severity':
			case 'wrong_rights':
				$this->setErrorParams(10, '{Call:Lang:modules:bill_domains:nedostatochn:'.Library::serialize(array($res['1']['2'])).'}');
				return true;

			case 'wrong_password_trycounter':
				$this->setErrorParams(10, '{Call:Lang:modules:bill_domains:prevyshenodo:'.Library::serialize(array($res['1']['2'])).'}');
				return true;

			case 'wrong_password':
			case 'wrong_ident':
			case 'unknown_login':
			case 'unknown_contact-login':
				$this->setErrorParams(10, '{Call:Lang:modules:bill_domains:nepravilnyel1:'.Library::serialize(array($res['1']['2'])).'}');
				return true;

			case 'registrar_error':
				switch($res['1']['2']){					case 'tc_error':
						$this->setErrorParams(101, '{Call:Lang:modules:bill_domains:zaiavkaotklo:'.Library::serialize(array($res['1']['2'])).'}');
						return false;

					case 'documents_wait':
						$this->setErrorParams(102, '{Call:Lang:modules:bill_domains:isteksrokozh:'.Library::serialize(array($res['1']['2'])).'}');
						return false;

					case 'money_wait':
						$this->setErrorParams(103, '{Call:Lang:modules:bill_domains:isteksrokozh1:'.Library::serialize(array($res['1']['2'])).'}');
						return false;
				}

			default:
				$this->setErrorParams(2, '{Call:Lang:modules:bill_domains:neopredelenn1:'.Library::serialize(array($res['1']['2'])).'}');
				return false;
		}
	}

	private function getNs($params, $pref = '', $postf = ''){
		/*
			Возвращает NS и IP к нимъ
		*/

		$return = array();
		for($i = 1; $i <= 4; $i ++){
			if(!empty($params[$pref.'ns'.$i.'_'.$postf])){
				$return[] = trim($params[$pref.'ns'.$i.'_'.$postf]);
			}
		}

		if(count($return) < 2) $return = array();
		return $return;
	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(
				'fail_if_no_money' => array('type' => 'checkbox', 'text' => '{Call:Lang:modules:bill_domains:soobshchitob}'),
				'no_bill_notify' => array('type' => 'checkbox', 'text' => '{Call:Lang:modules:bill_domains:nevysylatuve}'),
				'private_person_flag' => array('type' => 'checkbox', 'text' => '{Call:Lang:modules:bill_domains:skrytdannyeo}')
			),
			'{Call:Lang:modules:bill_domains:naosnovenaun}',
			'bill_domains'
		);
	}
}

?>