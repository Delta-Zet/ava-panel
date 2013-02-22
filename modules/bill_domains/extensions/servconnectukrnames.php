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

class servconnectUkrnames extends serverDomainsObject{

	private $epp;

	public function setConnectMatrix($service = '', $params = array()){
		/*
			Матрица для коннекта. Позволяет
				- Указать используется для коннекта пароль или хеш
		*/

		$params['fObj']->setMatrix(
			array(
				'host' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:bill_domains:khostiliipad}',
					'comment' => '',
					'warn' => '{Call:Lang:modules:bill_domains:neukazanipad}',
					'warn_function' => 'regExp::ip'
				),
				'port' => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:bill_domains:porteppapi}',
					'warn' => '{Call:Lang:modules:bill_domains:neukazanport}',
					'warn_function' => 'regExp::digit'
				),
				'secure' => array(
					'type' => 'checkbox',
					'text' => '{Call:Lang:modules:bill_domains:ispolzovatza}'
				)
			),
			'form'
		);
	}

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		$this->obj->values['vars']['port'] = $this->obj->values['port'];
		$this->obj->values['vars']['secure'] = $this->obj->values['secure'];
		$this->setRequestResult($result = $this->eppConnect());
		return $result;
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

		if(!$this->eppConnect()){			$this->setRequestResult(false);
			return false;
		}

		$o_ = $this->obj->values['acc_domain_owner'.$params['id']];
		$a_ = empty($this->obj->values['acc_domain_owner_a'.$params['id']]) ? $this->obj->values['acc_domain_owner'.$params['id']] : $this->obj->values['acc_domain_owner_a'.$params['id']];
		$b_ = empty($this->obj->values['acc_domain_owner_b'.$params['id']]) ? $this->obj->values['acc_domain_owner'.$params['id']] : $this->obj->values['acc_domain_owner_b'.$params['id']];
		$t_ = empty($this->obj->values['acc_domain_owner_t'.$params['id']]) ? $this->obj->values['acc_domain_owner'.$params['id']] : $this->obj->values['acc_domain_owner_t'.$params['id']];

		$logins = array();
		foreach(array('' => 'o_', '_a' => 'a_', '_b' => 'b_', '_t' => 't_') as $i => $e){
			if($$e && empty($logins[$$e]) && !($logins[$$e] = $this->getBlankInServer($$e))){				$params2 = $this->getBlankVars($$e);
				$logins[$$e] = 'uans'.Library::inventStr(12);
				$pwd = Library::inventStr(8);

				if(!$rslt = $this->epp->createContact(
					$logins[$$e],
					$pwd,
					$this->getEnName($params2),
					empty($params2['oCompany']) ? 'Private person' : library::cyr2translit($params2['oCompany']),
					library::cyr2translit($params2['oStreet']),
					library::cyr2translit($params2['oCity']),
					library::cyr2translit($params2['oRegion']),
					$params2['oZip'],
					$params2['oCountry'],
					$params2['oEml'],
					$params2['oPhone']
				)){
					$this->setRequestResult(false);
					return false;				}

				$this->setBlank($$e, $logins[$$e], $pwd);
			}
		}

		$pwd = Library::inventStr(8);
		if(!$this->epp->regDomain(
			$params['s_ident'],
			$this->obj->values['term'.$params['id']],
			$logins[$o_],
			$logins[$a_],
			$logins[$b_],
			$logins[$t_],
			$pwd,
			array(
				$this->obj->values['acc_ns1_'.$params['id']],
				$this->obj->values['acc_ns2_'.$params['id']],
				$this->obj->values['acc_ns3_'.$params['id']],
				$this->obj->values['acc_ns4_'.$params['id']],
			)
		)){			$this->setRequestResult(false);
			return false;		}

		$this->setRequestResult(true);
		return true;
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

	protected function __ava__setRequestResult($result){		$this->result = $this->epp->getResult();

		if(!$result){
			$error = $this->epp->getErrors();
			$this->setErrorParams($error[count($error) - 1]['code'], $error[count($error) - 1]['msg']);
		}	}

	protected function eppConnect(){
		$GLOBALS['Core']->loadExtension('bill_domains', 'eppClient');
		$this->epp = new eppClient;
		return $this->epp->Connect($this->connectData['host'], $this->connectData['login'], $this->connectData['pwd'], $this->connectData['port'], $this->connectData['secure'], 5);
	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(),
			'{Call:Lang:modules:bill_domains:registratoru}',
			'bill_domains'
		);
	}
}

?>