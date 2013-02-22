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



$GLOBALS['Core']->loadExtension('bill_domains', 'rrpClient');

class servconnectRucenter extends rrpClient{

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		if($this->sendReq('search', 'contract', array('contracts-limit' => '2', 'contracts-first' => '1'))) return true;
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

	public function __ava__setAccOrderMatrix($service, $params = array()){
		if(isset($this->sObj->matrix['correspond'])){
			return array('correspond' => array('value' => array('ru' => 1)));		}
		elseif($owners = $this->sObj->getOwners($params['client_id'], 'ru')){
			$this->sObj->matrix[$params['prefix'].'domain_owner'.$params['id']]['additional'] = $owners;			return array();		}
		else{			require(_W.'modules/bill_domains/forms/domain_owners.php');
			$matrix['correspond']['value']['ru'] = 1;
			return $matrix;		}	}

	//Управление аккаунтами
	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

		if(!$clientLogin = $this->getClientInServer($this->obj->values[$params['prefix'].'domain_owner'.$params['id']])){			$pwd = Library::inventStr(8);
			$params2 = $this->getBlankVars($this->obj->values[$params['prefix'].'domain_owner'.$params['id']]);
			if(!$params2['address']) $params2['address'] = $this->getAddr($params2);
			if(!$params2['address']) $params2['address'] = $this->getAddr($params2, true);

			$vars = array(
				'password' => $pwd,
				'tech-password' => $pwd,
				'country' => $params2['country'],
				'phone' => $params2['phone'],
				'fax-no' => $params2['fax'],
				'e-mail' => $params2['eml'],
				'p-addr' => $params2['address']
			);

			switch($params2['type']){
				case 'ip':
					$vars['code'] = $params2['ipInn'];
					$vars['d-addr'] = $params2['address'];
					$vars['person-r'] = '{Call:Lang:modules:bill_domains:ip:'.Library::serialize(array($this->getName($params2))).'}';

				case 'person':
					if($params2['type'] == 'person'){
						$vars['person-r'] = $this->getName($params2);
					}

					$vars['contract-type'] = 'PRS';
					$vars['person'] = $this->getEnName($params2);
					$vars['passport'] = $GLOBALS['Core']->paramReplaces('{Call:Lang:modules:bill_domains:zaregistriro:'.Library::serialize(array($this->getPassport($params2), $params2['address'])).'}', $this->obj);
					if(!empty($params2['birth'])) $vars['birth-date'] = date('d.m.Y', $params2['birth']);

					break;

				case 'organization':
					$vars['contract-type'] = 'ORG';
					$vars['org'] = library::cyr2translit($params2['company']);
					$vars['org-r'] = $params2['company'];
					$vars['parent-org-r'] = $params2['company'];

					$vars['code'] = $params2['inn'];
					$vars['kpp'] = $params2['kpp'];
					$vars['d-addr'] = $params2['paddress'];
					$vars['address-r'] = $params2['address'];

					break;
			}

			if($result = $this->sendReq('create', 'contract', array(), array('contract' => $vars))){
				$clientLogin = $result['body'][0]['login'];
				$this->setClient($this->obj->values[$params['prefix'].'domain_owner'.$params['id']], $clientLogin, $pwd);
			}
			else return false;
		}

		$logins = array();
		foreach(array('' => 'o_', '_a' => 'a_', '_b' => 'b_', '_t' => 't_') as $i => $e){			if(!empty($this->obj->values[$params['prefix'].'domain_owner'.$i.$params['id']]) && empty($logins[$this->obj->values[$params['prefix'].'domain_owner'.$i.$params['id']]])){				if(!($logins[$this->obj->values[$params['prefix'].'domain_owner'.$i.$params['id']]] = $this->getBlankInServer($this->obj->values[$params['prefix'].'domain_owner'.$i.$params['id']]))){					if(empty($params2)) $params2 = $this->getBlankVars($this->obj->values[$params['prefix'].'domain_owner'.$params['id']]);

					$vars = array(
						'name' => $this->getEnName2($params2),
						'status' => 'registrant',
						'country' => $params2['country'],
						'region' => library::cyr2translit($params2['region']),
						'city' => library::cyr2translit($params2['city']),
						'street' => library::cyr2translit($params2['street']),
						'zipcode' => $params2['zip'],
						'phone' => $params2['phone'],
						'fax' => $params2['fax'],
						'email' => $params2['eml'],
						'org' => empty($params2['company']) ? '' : library::cyr2translit($params2['company'])
					);

					if($result = $this->sendReq('create', 'contact', array('subject-contract' => $clientLogin), array('contact' => $vars))){
						$logins[$this->obj->values[$params['prefix'].'domain_owner'.$i.$params['id']]] = $result['body'][0]['nic-hdl'];
						$this->setBlank($this->obj->values[$params['prefix'].'domain_owner'.$i.$params['id']], $logins[$this->obj->values[$params['prefix'].'domain_owner'.$i.$params['id']]]);
					}
					else return false;
				}			}
		}

		$vars = Library::array_merge(
			array(
				'action' => 'new',
				'domain' => $this->obj->values[$params['prefix'].'ident'.$params['id']],
				'type' => 'CORPORATE'
			),
			$this->getExtras($service, $this->obj->values, $params['prefix'], $params['id'])
		);

		switch($params['pkgData']['server_name']){
			case 'ru':
				$vars['service'] = 'domain_ru';

			case 'su':
				if($params['pkgData']['server_name'] == 'su') $vars['service'] = 'domain_su';

				$vars['template'] = 'client_ru';
				$vars['descr'] = 'Registered for client by '.$GLOBALS['Core']->Site->urlParse['host'];

				break;

			case 'cc':
				$vars['service'] = 'domain_epp_cc';

			case 'tv':
				$vars['service'] = 'domain_epp_tv';

			case 'me':
				$vars['service'] = 'domain_epp_me';

			default:
				if(empty($vars['service'])) $vars['service'] = 'domain_rrp';
				$vars['template'] = 'client_rrp';
				$vars['period'] = $this->obj->values['term'.$params['id']];

				$vars['admin-c'] = $logins[$this->obj->values[$params['prefix'].'domain_owner_a'.$params['id']]];
				$vars['bill-c'] = $logins[$this->obj->values[$params['prefix'].'domain_owner_b'.$params['id']]];
				$vars['tech-c'] = $logins[$this->obj->values[$params['prefix'].'domain_owner_t'.$params['id']]];

				break;
		}

		$result = $this->sendReq('create', 'order', array('subject-contract' => $clientLogin), array('order-item' => $vars));
		if($result['response'][1] == '200'){			return true;		}
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
		Создание мацриц
	*/

	public function setConnectMatrix($service = '', $params = array()){
		/*
			Матрица для коннекта. Позволяет
				- Указать используется для коннекта пароль или хеш
		*/

		$params['fObj']->setMatrix(
			array(
				'login' => array(
					'text' => '{Call:Lang:modules:bill_domains:nomerdogovor}',
					'comment' => '{Call:Lang:modules:bill_domains:polnostiuten}'
				)
			),
			'form'
		);
	}


	/*
		Внешние сугубо доменные
	*/

	public function ns($service, $params = array()){
		/*
			Устанавливает новые ns
		*/


		return $return;
	}

	public function __ava__newNs($service, $params = array()){
		/*
			Устанавливает новые ns
		*/

		return $this->sendReq('pispRedelegation', $vars);
	}

	public function __ava__getWhois($service, $params = array()){
		/*
			Устанавливает матрицу для смены данных Whois
		*/

		$z = gen_bill_domains::getDomainZone($params['ident']);
		switch($z){			case 'ru':
			case 'su':
				return $this->getDefaultContactMatrix($data);

			default:
				$owners = $this->sObj->getOwners();
				$contacts = $this->getContacts($params['ident']);

				return array(
					'domain_owner' => array(
						'type' => 'select',
						'text' => '{Call:Lang:modules:bill_domains:vladeletsdom}',
						'additional' => $owners,
					),
					'domain_owner_a' => array(
						'type' => 'select',
						'text' => '{Call:Lang:modules:bill_domains:administrati}',
						'additional' => $owners,
					),
					'domain_owner_b' => array(
						'type' => 'select',
						'text' => '{Call:Lang:modules:bill_domains:billingovyjk}',
						'additional' => $owners,
					),
					'domain_owner_t' => array(
						'type' => 'select',
						'text' => '{Call:Lang:modules:bill_domains:tekhnicheski}',
						'additional' => $owners,
					),
				);		}
	}

	public function __ava__setWhois($service, $params = array()){
		/*
			Устанавливает сам Whois
		*/

		return $this->sendReq($func, $vars);
	}

	private function getContacts($domain){		/*
			Ищет все контакты к домену
		*/
		$result = $this->sendReq('search', 'contact', array(), array('contact' => array('domain' => $domain)));
		$return = array();
		return $return;
	}


	/*
		Служебные
	*/

	private function sendReq($operation, $request, $head = array(), $items = array()){
		/*
			Отправляет запрос
		*/

		if(!$items) $items = array($request => array());
		$this->transactionId = Dates::date('YmdHis').'.'.getmypid().rand(10,99).'@'.$GLOBALS['Core']->Site->urlParse['host'];

		return $this->parseReq(
			$this->httpConnect(
				$this->getHostAppendQuery('/dns/dealer'),
				array(),
				array(
					'SimpleRequest' => regExp::charset(
						'UTF-8',
						'KOI8-R',
						$this->getReq(
							Library::array_merge(
								array(
									'lang' => 'ru',
									'request' => $request,
									'operation' => $operation,
									'login' => $this->connectData['login'].'/ADM',
									'password' => $this->connectData['pwd'],
									'request-id' => $this->transactionId
								),
								$head
							),
							$items
						)
					)
				),
				'POST'
			)
		);
	}

	private function getExtras($service, $params, $pref = '', $postf = ''){
		/*
			Доп. параметры. Такие как privateWhois и т.п.
		*/

		$return['nsserver'] = $this->getNs($params, $pref, $postf);
		return $return;
	}

	private function getNs($params, $pref = '', $postf = ''){
		/*
			Возвращает NS и IP к нимъ
		*/

		return '';
	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(),
			'{Call:Lang:modules:bill_domains:registratorr}',
			'bill_domains'
		);
	}
}

?>