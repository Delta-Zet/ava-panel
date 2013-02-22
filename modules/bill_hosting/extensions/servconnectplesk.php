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



$GLOBALS['Core']->loadExtension('bill_hosting', 'serverHostingObject');

class servConnectPlesk extends serverHostingObject{

	private $selectedUsers;
	private $selectedResellers;
	private $selectedDomains;


	/********************************************************************************************************************************************************************

																				Соединение

	*********************************************************************************************************************************************************************/

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		if(is_array($this->getTemplates())) return true;
		return false;
	}



	/********************************************************************************************************************************************************************

																		Работа с пакетами

	*********************************************************************************************************************************************************************/

	public function __ava__setAddPkgMatrix($service, $params = array()){
		/*
			Устанавливается:
				1. Имя шаблона домена
				2. IP (список)
				3. locale - локализация (en-US)
		*/

		$this->obj->addFormBlock($params['fObj'], $this->getAddPkgMatrix($service, $params), array(), array(), 'block2');
		return true;
	}

	protected function __ava__getAddPkgMatrix($service, $params = array()){
		/*
			Добавляет пакет
		*/

		$tpls = $this->getDomainTemplates();
		$tpl = !is_array($tpls) ? $params['pkgData']['server_name'] : (isset($tpls[$params['pkgData']['server_name']]) ? $tpls[$params['pkgData']['server_name']] : '@new');

		return array(
			'pkgdsc_'.$this->getParamsVar($service, 'ip') => array(
				'type' => 'checkbox_array',
				'additional' => $this->getIps(),
				'warn' => '{Call:Lang:modules:bill_hosting:vyneukazalii1}'
			),
			'pkgdsc_'.$this->getParamsVar($service, 'domain-template') => array(
				'additional' => Library::array_merge(
					array('' => '{Call:Lang:modules:bill_hosting:net}', '@new' => '{Call:Lang:modules:bill_hosting:sozdatnovyj}'),
					$tpls
				),
				'value' => $tpl
			),
			'pkgdsc_'.$this->getParamsVar($service, 'locale') => array(
				'additional' => $this->getLocales()
			),
			'pkgdsc_'.$this->getParamsVar($service, 'domain-template-modify') => array(
				'value' => 1
			),
			'pkgdsc_expired' => array(
				'type' => 'checkbox',
				'text' => '{Call:Lang:modules:bill_hosting:peredatdatuo}',
				'comment' => '{Call:Lang:modules:bill_hosting:poskolkuples}'
			)
		);
	}

	public function __ava__addPkg($service, $params = array()){
		/*
			Добавляет пакет
		*/

		return $this->sendAddPkg($service, $params);
	}

	protected function __ava__sendAddPkg($service, $params, $type = 'client-template'){		/*
			Собственно отправка запроса на создание акка
		*/

		$limits = $this->insertSpecialParams($this->convertParams($service, $this->obj->values, 'pkgdsc_'));
		$templates = $this->getDomainTemplates();
		$tmplVar = 'pkgdsc_'.$this->getParamsVar($service, 'domain-template');
		$tmplVal = empty($this->obj->values[$tmplVar]) ? '' : $this->obj->values[$tmplVar];

		//Создаем шаблон домена если это возможно
		if($tmplVal == '@new'){			$tmplName = $this->obj->values['server_name'];
			$j = 1;

			while(in_array($tmplName, $templates)){				$tmplName = $this->obj->values['server_name'].$j;
				$j ++;			}

			if($this->sendAddDomainPkg($service, $params, $limits, $tmplName)){				$this->obj->values[$tmplVar] = $tmplName;
			}
			else{				$this->obj->setError('', '{Call:Lang:modules:bill_hosting:neudalossozd:'.Library::serialize(array($tmplName)).'}');
			}
		}
		elseif($tmplVal != '' && $this->getParamsValue($service, 'domain-template-modify', 'pkgdsc_')){			if($this->sendAddDomainPkg($service, $params, $limits, $tmplVal, 'set')){
				$this->obj->values['pkgdsc_'.$this->getParamsVar($service, 'domain-template')] = $tmplVal;
			}
			else{
				$this->obj->setError('', '{Call:Lang:modules:bill_hosting:neudalosizme:'.Library::serialize(array($tmplVal)).'}');
			}
		}

		$clientTemplates = $this->getTemplates($type);
		$tplParams = $this->getClientTplParams($limits);
		if($type == 'reseller-template') unset($tplParams['preferences']['shared']);

		if(isset($clientTemplates[$this->obj->values['server_name']])){			$req = Library::array_merge(array('filter' => array('name' => $this->obj->values['server_name'])), $tplParams);
			$setType = 'set';
		}
		else{			$req = Library::array_merge(array('name' => $this->obj->values['server_name']), $tplParams);
			$setType = 'add';
		}

		if(is_array($this->sendReq($req, $type, $setType))) return true;
		return false;
	}

	protected function __ava__sendAddDomainPkg($service, $params, $limits, $tplName, $type = 'add'){		/*
			Создает шаблон домена
		*/

		if($type == 'set') $limits = Library::array_merge(array('filter' => array('name' => $tplName)), $this->getDomainTplParams($limits));
		else{			$limits = Library::array_merge(array('name' => $tplName), $this->getDomainTplParams($limits));			$limits['hosting'] = $limits['hosting']['vrt_hst'];
		}

		if(is_array($this->sendReq($limits, 'domain-template', $type))) return true;
		return false;
	}

	public function __ava__delPkg($service, $params = array()){
		/*
			Удаление пакетов
			В передаваемом obj должен быть установлен список delPkg. Все они будут удалены
		*/

		return $this->sendDelPkg($service, $params);
	}

	protected function __ava__sendDelPkg($service, $params, $type = 'client-template'){
		/*
			Собственно отправка запроса на удаление акка
		*/

		$return = Library::arrayFill($params['pkgs'], false);
		$templates = $this->getTemplates($type);
		$reqData = array('filter' => array());

		foreach($params['pkgs'] as $i => $e){
			if(!isset($templates[$e['server_name']])) $return[$i] = true;
			else $reqData['filter']['name'][] = $e['server_name'];
		}

		if(isset($reqData['filter']['name']) && is_array($this->sendReq($reqData, $type, 'del'))) return Library::arrayFill($params['pkgs'], true);
		return $return;
	}


	/********************************************************************************************************************************************************************

																		Работа с аккаунтами

	*********************************************************************************************************************************************************************/

	public function __ava__setAccOrderMatrix($service, $params = array()){
		/*
			Форма заказа услуги
				- pname - ФИО. Берется из данных акка клиента. Проверяется что такого еще нет в Plesk
				- phone
				- fax
				- address
				- city
				- country
				- locale
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				$params['prefix'].$this->getParamsVar($service, 'locale').$params['id'] => array('additional' => $this->getLocales()),
				$params['prefix'].'pname'.$params['id'] => array(
					'type' => 'text',
					'text' => '{Call:Lang:modules:bill_hosting:imiaifamilii}',
					'comment' => '{Call:Lang:modules:bill_hosting:imiaifamilii1}',
					'warn' => '{Call:Lang:modules:bill_hosting:vyneukazalii2}'
				),
				$params['prefix'].'domain'.$params['id'] => array(
					'warn' => ''
				)
			),
			array(),
			array(),
			$params['bName']
		);

		$eData = $this->obj->getOrderEntry($params['eId']);
		$cData = $this->obj->getUserByClientId($eData['client_id']);
		$params['fObj']->setValue($params['prefix'].'pname'.$params['id'], $cData['name']);
	}

	public function __ava__checkAccOrderMatrix($service, $params = array()){
		/*
			Проверка заказа. Проверяет логин, домен и ФИО
		*/

		$users = $this->getAccounts();
		foreach($users as $i => $e){
			if($e['pname'] == $params['values'][$params['prefix'].'pname'.$params['id']]){
				$this->obj->setError($params['prefix'].'pname'.$params['id'], '{Call:Lang:modules:bill_hosting:polzovatelst}');
				break;
			}
		}

		if(!empty($params['values'][$params['prefix'].'ident'.$params['id']]) && isset($users[$params['values'][$params['prefix'].'ident'.$params['id']]])){
			$this->obj->setError($params['prefix'].'ident'.$params['id'], '{Call:Lang:modules:bill_hosting:takojloginuz1}');
		}

		if($this->getDomains(array('domain-name' => $params['prefix'].'domain'.$params['id']))){
			$this->obj->setError($params['prefix'].'domain'.$params['id'], '{Call:Lang:modules:bill_hosting:takojdomenuz}');
		}
	}

	public function __ava__setAddAccMatrix($service, $params = array()){
		/*
			Устанавливает форму заказа в одминке
		*/

		$pkgData = $this->obj->getPkgByOrderEntry($params['eId']);
		$ip = $pkgData['vars'][$this->getParamsVar($service, 'ip')];

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				$params['prefix'].$this->getParamsVar($service, 'ip').$params['id'] => array(
					'additional' => $this->getIps(),
					'value' => regExp::replace('_', '.', is_array($ip) ? Library::firstKey($ip) : $ip)
				)
			),
			array(),
			array(),
			$params['bName']
		);
	}

	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

		return $this->sendAddAcc($service, $params);
	}

	public function __ava__sendAddAcc($service, $params = array(), $type = 'client'){
		/*
			Непосредственно запрос на создание акка
		*/

		$eData = $this->obj->getOrderEntry($params['id'], true);
		$pkgData = $this->obj->serviceData($service, $eData['package']);

		if(!is_array(
			$result = $this->sendReq(
				array(
					'gen_info' => array(
						'cname' => regExp::substr($eData['extra']['params1'][$this->getParamsVar($service, 'cname')], 0, 50),
						'pname' => $eData['extra']['params1']['pname'],
						'login' => $eData['ident'],
						'passwd' => $eData['extra']['params1']['pwd'],
						'status' => '0',
						'phone' => $eData['extra']['params1'][$this->getParamsVar($service, 'phone')],
						'fax' => $eData['extra']['params1'][$this->getParamsVar($service, 'fax')],
						'email' => $this->obj->getClientEml($eData['client_id']),
						'address' => $eData['extra']['params1'][$this->getParamsVar($service, 'address')],
						'city' => $eData['extra']['params1'][$this->getParamsVar($service, 'city')],
						'country' => $eData['extra']['params1'][$this->getParamsVar($service, 'country')],
						'locale' => $eData['extra']['params1'][$this->getParamsVar($service, 'locale')]
					),
					'template-name' => $pkgData['server_name']
				),
				$type,
				'add'
			)
		)) return false;

		if(!empty($eData['extra']['params1']['domain'])){
			$ipVar = $this->getParamsVar($service, 'ip');
			$ip = !empty($eData['extra']['params1'][$ipVar]) ? $eData['extra']['params1'][$ipVar] : Library::firstKey($params['pkgData']['vars'][$ipVar]);

			if(!is_array($this->sendReq(
				array(
					'gen_setup' => array(
						'name' => $this->idna($eData['extra']['params1']['domain']),
						'owner-login' => $eData['ident'],
						'htype' => 'vrt_hst',
						'ip_address' => $ip,
						'status' => 0
					),
					'hosting' => array(
						'vrt_hst' => array(
							'property' => $this->convert2standart(
								array(
									'ftp_login' => $eData['ident'],
									'ftp_password' => $eData['extra']['params1']['pwd']
								)
							),
							'ip_address' => $ip
						)
					),
					'template-name' => $pkgData['server_name']
				),
				'domain',
				'add'
			))){
				$this->obj->setMessage('price'.$params['id'], '{Call:Lang:modules:bill_hosting:neudalossozd1:'.Library::serialize(array($eData['extra']['params1']['domain'], $eData['ident'])).'}', 'error');
			}
		}

		$this->obj->upOrderEntry($params['id'], array('extra' => array('params2' => array('name' => $eData['extra']['params1']['pname']))));
		return true;
	}

	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля, а также все домены относящиеся к нему
		*/

		$owners = array();
		$domains = array();
		$users = $this->getUsers();
		$return = Library::arrayFill($params['accs'], true);

		foreach($params['accs'] as $i => $e){
			if(isset($users[$e['ident']])) $owners[] = $e['ident'];
			else $return[$i] = false;
		}

		if($owners){
			foreach($this->getDomains(array('owner-login' => $owners)) as $i => $e){
				$domains[] = $i;
			}

			if($domains){
				$tplParams = $this->getDomainTemplateParams($params['pkgData']['vars'][$this->getParamsVar($service, 'domain-template')]);
				$tplParams['hosting']['vrt_hst']['ip_address'] = regExp::replace(
					'_',
					'.',
					is_array($params['pkgData']['vars']['ip']) ? Library::firstKey($params['pkgData']['vars']['ip']) : $params['pkgData']['vars']['ip']
				);

				$this->sendReq(
					array(
						'filter' => array('domain-name' => $domains),
						'values' => array(
							'limits' => $tplParams['limits'],
							'hosting' => $tplParams['hosting'],
							'performance' => $tplParams['performance']
						)
					),
					'domain',
					'set'
				);
			}

			$mData = $this->obj->getServiceMainModifyData($params['id']);
			$pkgData = $this->obj->serviceData($mData['service'], $mData['pkg']);
			$tplParams = $this->getTemplateParams($pkgData['server_name']);

			return Library::array_merge(
				$return,
				$this->sendMultiAccsReq(
					$service,
					$params,
					'client',
					'set',
					array(
						'values' => array(
							'limits' => $tplParams['limits'],
							'permissions' => $tplParams['permissions'],
							'sbnet-user' => $tplParams['preferences']['sbnet-user']
						)
					)
				)
			);
		}
		else{
			$this->setErrorParams(10000, 'Владелец аккаунта не найден на сервере');
			return $return;
		}
	}









































	/********************************************************************************************************************************************************************

																		Работа с аккаунтами

	*********************************************************************************************************************************************************************/




	//Управление аккаунтами


	public function __ava__prolongAcc($service, $params = array()){
		/*
			Продливаит оккаунт пользоватиля. В данном случии нахуй не нужна
		*/

		return true;
	}

	public function __ava__delAcc($service, $params = array()){
		/*
			Удоляит аккаунт пользоватиля
		*/

		return $this->sendMultiAccsReq($service, $params, 'client', 'del');
	}

	public function __ava__suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/

		$reqData['values']['gen_info']['status'] = 16;
		return $this->sendMultiAccsReq($service, $params, 'client', 'set', $reqData);
	}

	public function __ava__unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/

		$reqData['values']['gen_info']['status'] = 0;
		return $this->sendMultiAccsReq($service, $params, 'client', 'set', $reqData);
	}

	protected function __ava__sendMultiAccsReq($service, $params, $type, $req, $reqData0 = array()){		/*
			Массовые операции над аккаунтами
		*/
		$return = Library::arrayFill($params['accs'], false);
		$users = ($type == 'reseller') ? $this->getResellers() : $this->getUsers();
		$reqData = array();

		foreach($params['accs'] as $i => $e){
			if(($req == 'del' && !isset($users[$e['ident']]))) $return[$i] = true;
			elseif(isset($users[$e['ident']])) $reqData['filter']['login'][] = $e['ident'];
		}

		$reqData = Library::array_merge($reqData, $reqData0);
		if(!empty($reqData['filter']['login'])){
			$result = $this->sendReq($reqData, $type, $req);

			if(is_array($result)){
				if(Library::isHash($result['client'][$req]['result'])) $result['client'][$req]['result'] = array($result['client'][$req]['result']);
				$deleted = array();
				foreach($result['client'][$req]['result'] as $i => $e) $deleted[$e['filter-id']] = $e['filter-id'];
				foreach($params['accs'] as $i => $e) if(isset($deleted[$e['ident']])) $return[$i] = true;			}
		}

		return $return;
	}

	public function isSuspendAcc($service, $params = array()){
		/*
			Проверяи чта акк из суспиндит
		*/
	}

	public function listAccs($service, $params = array()){
		/*
			Усе акки
		*/
	}


	/********************************************************************************************************************************************************************

															Отправка параметров в соответствии со схемой

	*********************************************************************************************************************************************************************/

	protected function __ava__getDomainTplParams($params){
		/*
			Возвращает настройки для создания шаблона домена
		*/

		$return = array(
			'mail' => array(),
			'limits' => array(),
			'log-rotation' => array(),
			'preferences' => array(),
			'hosting' => array(),
			'performance' => array()
		);

		if($params['nonexistent-user'] == 'bounce') $return['mail']['nonexistent-user']['bounce'] = 'This address no accepts mail';
		elseif($params['nonexistent-user'] == 'forward') $return['mail']['nonexistent-user']['forward'] = $GLOBALS['Core']->User->params['eml'];
		$return['mail']['webmail'] = $params['webmail'];

		if(empty($params['log-rotation'])) $return['log-rotation'] = array('off' => '');
		else{
			if($params['log-rotation'] == 'BySize') $return['log-rotation']['on']['log-condition']['log-bysize'] = $params['log-bysize'];
			else $return['log-rotation']['on']['log-condition']['log-bytime'] = $params['log-rotation'];
			$return['log-rotation']['on'] = Library::array_merge($return['log-rotation']['on'], Library::arrayValues(array('log-max-num-files', 'log-compress', 'log-email'), $params));
		}

		if(!empty($params['domain-stat'])) $return['preferences']['stat'] = $params['domain-stat'];
		if(!empty($params['max_maillists'])) $return['preferences']['maillists'] = 'true';
		if(!empty($params['dns_zone_type'])) $return['preferences']['dns_zone_type'] = $params['dns_zone_type'];
		if(!empty($params['shared-domain-template'])) $return['preferences']['shared'] = $params['shared-domain-template'];

		$return['performance'] = array();
		if(!empty($params['bandwidth-speed'])) $return['performance']['bandwidth'] = $params['bandwidth-speed'];
		if(!empty($params['max_connections'])) $return['performance']['max_connections'] = $params['max_connections'];

		$return['limits']['overuse'] = $params['overuse'];
		$return['limits']['limit'] = $this->convert2standart(
			Library::arrayValues(
				array(
					'max_webapps',
					'max_maillists',
					'max_resp',
					'max_mg',
					'max_redir',
					'mbox_quota',
					'max_box',
					'max_db',
					'max_wu',
					'max_traffic',
					'disk_space',
					'max_subdom',
					'max_dom_aliases'
				),
				$params
			)
		);

		$return['hosting']['vrt_hst']['property'] = $this->convert2standart(
			Library::arrayValues(
				array(
					'same_ssl',
					'ssl',
					'ssi',
					'wu_script',
					'cgi',
					'perl',
					'python',
					'coldfusion',
					'asp',
					'miva',
					'errdocs',
					'shell',
					'php_safe_mode',
					'sb_publishing',
					'fastcgi',
					'webstat',
					'webstat_protected',
					'create-sb-subdomains'
				),
				$params
			)
		);

		$return['hosting']['vrt_hst']['property'][] = array('name' => 'quota', 'value' => $params['ftp_quota']);
		if($params['php-type']){			$return['hosting']['vrt_hst']['property'][] = array('name' => 'php', 'value' => 'true');
			$return['hosting']['vrt_hst']['property'][] = array('name' => 'php_handler_type', 'value' => $params['php-type']);
		}

		return $return;
	}

	protected function __ava__getClientTplParams($params){
		/*
			Возвращает настройки для создания шаблона домена
		*/

		$return = array(
			'limits' => array(),
			'permissions' => array(),
			'ip-pool' => array(),
			'preferences' => array()
		);

		$return['limits']['resource-policy']['overuse'] = $params['overuse'];
		$return['limits']['limit'] = $this->convert2standart(
			Library::arrayValues(
				array(
					'max_webapps',
					'max_maillists',
					'max_resp',
					'max_mg',
					'max_redir',
					'mbox_quota',
					'max_box',
					'max_db',
					'max_wu',
					'max_traffic',
					'disk_space',
					'max_subdom',
					'max_dom',
					'max_dom_aliases'
				),
				$params
			)
		);

		$return['permissions']['permission'] = $this->convert2standart(
			Library::arrayValues(
				array(
					'create_domains',
					'manage_phosting',
					'manage_quota',
					'manage_subdomains',
					'change_limits',
					'manage_dns',
					'manage_log',
					'manage_crontab',
					'manage_anonftp',
					'manage_webapps',
					'manage_maillists',
					'site_builder',
					'remote_access_interface',
					'manage_performance',
					'select_db_server',
					'cp_access',
					'manage_domain_aliases',
					'manage_php_safe_mode',
					'dashboard',
					'stdgui',
					'manage_dashboard',
					'manage_spamfilter',
					'manage_virusfilter',
					'manage_webstat',
					'allow_local_backups',
					'allow_ftp_backups'
				),
				$params
			)
		);

		if($params['manage_sh_access']){			$return['permissions']['permission'][] = array('name' => 'manage_sh_access', 'value' => 'true');
			if($params['manage_sh_access'] == 'any') $return['permissions']['permission'][] = array('name' => 'manage_not_chroot_shell', 'value' => 'true');
			else $return['permissions']['permission'][] = array('name' => 'manage_not_chroot_shell', 'value' => 'false');
		}
		else{			$return['permissions']['permission'][] = array('name' => 'manage_sh_access', 'value' => 'false');		}

		$return['ip-pool']['ip-address'] = regExp::replace('_', '.', array_keys($params['ip']));
		$return['ip-pool']['allocate-ip'] = $params['allocate_ip'];
		$return['preferences']['sbnet-user'] = $params['sbnet-user'];
		$return['preferences']['shared'] = 'false';

		return $return;
	}



	/********************************************************************************************************************************************************************

																			Служебные

	*********************************************************************************************************************************************************************/

	protected function getAccounts(){		/*
			Список пользовательских и реселлерских акков
		*/

		return Library::array_merge($this->getUsers(), $this->getResellers());	}

	protected function __ava__getUsers($filter = '', $data = array('gen_info' => ''), $force = false){		/*
			Возвращает списко всех пользователей
		*/

		if(!is_array($this->selectedUsers)){			$this->selectedUsers = array();
			if($result = $this->sendReq(array('filter' => $filter, 'dataset' => $data), 'client', 'get')){				if(Library::isHash($result['client']['get']['result'])) $result['client']['get']['result'] = array($result['client']['get']['result']);
				foreach($result['client']['get']['result'] as $i => $e){					if(isset($e['data']['gen_info']['login'])) $this->selectedUsers[$e['data']['gen_info']['login']] = $e['data']['gen_info'];				}
			}
		}

		return $this->selectedUsers;
	}

	protected function __ava__getResellers($filter = array('all' => ''), $data = array('gen-info' => ''), $force = false){
		/*
			Возвращает списко всех пользователей
		*/

		if(!is_array($this->selectedResellers)){
			$this->selectedResellers = array();
			if($result = $this->sendReq(array('filter' => $filter, 'dataset' => $data), 'reseller', 'get')){
				if(Library::isHash($result['reseller']['get']['result'])) $result['reseller']['get']['result'] = array($result['reseller']['get']['result']);
				foreach($result['reseller']['get']['result'] as $i => $e){
					if(isset($e['data']['gen-info']['login'])) $this->selectedResellers[$e['data']['gen-info']['login']] = $e['data']['gen-info'];
				}
			}
		}

		return $this->selectedResellers;
	}

	protected function __ava__getDomains($filter = '', $data = array('gen_info' => ''), $force = false){
		/*
			Список доминов
		*/

		if(!is_array($this->selectedDomains)){
			$this->selectedDomains = array();
			if($result = $this->sendReq(array('filter' => $filter, 'dataset' => $data), 'domain', 'get')){
				if(Library::isHash($result['domain']['get']['result'])) $result['domain']['get']['result'] = array($result['domain']['get']['result']);
				foreach($result['domain']['get']['result'] as $i => $e){
					if(isset($e['data']['gen_info']['name'])) $this->selectedDomains[$e['data']['gen_info']['name']] = $e['data']['gen_info'];
				}
			}
		}

		return $this->selectedDomains;
	}

	protected function __ava__getLocales(){		/*
			Возвращает список доступных локализаций
		*/

		if(!($result = $this->sendReq(array('filter' => ''), 'locale', 'get'))) return array();
		if(Library::isHash($result['locale']['get']['result'])) $result['locale']['get']['result'] = array($result['locale']['get']['result']);
		$return = array();

		foreach($result['locale']['get']['result'] as $i => $e){			if($e['info']['enabled'] == 'true') $return[$e['info']['id']] = $e['info']['lang'].' ('.$e['info']['country'].')';		}
		return $return;
	}

	protected function __ava__getTemplates($type = 'client-template'){		/*
			Список шаблонов
		*/

		$req = ($type == 'reseller-template') ? array('filter' => array('all' => '')) : array('filter' => '');		$result = $this->sendReq($req, $type, 'get');

		if(is_array($result)){
			$return = array();
			foreach($result[$type]['get']['result'] as $i => $e){				$return[$e['name']] = $e;			}

			return $return;
		}

		return false;
	}

	protected function __ava__getIps($type = 'shared'){		/*
			Список всех IP-адресов
		*/

		$return = array();
		if($ips = $this->sendReq(array('get' => ''), 'ip')){			if(is_array($ips['ip']['get']['result']['addresses']['ip_info'])){
				if(Library::isHash($ips['ip']['get']['result']['addresses']['ip_info'])) $ips['ip']['get']['result']['addresses']['ip_info'] = array($ips['ip']['get']['result']['addresses']['ip_info']);				foreach($ips['ip']['get']['result']['addresses']['ip_info'] as $e){
					if(($type && $e['type'] == $type) || !$type) $return[$e['ip_address']] = $e['ip_address'];
				}
			}
		}

		return $return;
	}

	protected function getDomainTemplates(){		/*
			Возвращает список шаблонов домена
		*/
		$return = array();
		$templates = $this->sendReq(array('filter' => ''), 'domain-template', 'get');

		if(!Library::isHash($templates['domain-template']['get']['result'])){
			foreach($templates['domain-template']['get']['result'] as $i => $e){				$return[$e['name']] = $e['name'];			}
		}
		else{			$return = array($templates['domain-template']['get']['result']['id'] => $templates['domain-template']['get']['result']['name']);		}

		return $return;
	}

	protected function __ava__getDomainTemplateParams($tpl){		/*
			Параметры шаблона домена
		*/
		$templates = $this->sendReq(array('filter' => array('name' => $tpl)), 'domain-template', 'get');
		return $templates['domain-template']['get']['result'];
	}

	protected function __ava__getTemplateParams($tpl, $type = 'client-template'){
		/*
			Параметры шаблона клиента
		*/

		$templates = $this->sendReq(
			array(
				'filter' => array('name' => $tpl),
				'limits' => '',
				'permissions' => '',
				'ip-pool' => '',
				'preferences' => ''
			),
			$type,
			'get'
		);

		return $templates[$type]['get']['result'];
	}

	private function sendReq($vars, $operation = '', $action = ''){
		/*
			Отправляет запрос
		*/

		if($action) $vars = array($action => $vars);
		if($operation) $vars = array($operation => $vars);

		$http = $this->httpConnect(
			$this->getOnlyHost($this->connectData['host']).'/enterprise/control/agent.php',
			array(
				'HTTP_AUTH_LOGIN' => $this->connectData['login'],
				'HTTP_AUTH_PASSWD' => $this->connectData['pwd'],
				'HTTP_PRETTY_PRINT' => 'TRUE',
				'Content-type' => 'text/xml; charset=UTF-8'
			),
			'<packet version="1.6.0.2">'.XML::getXML($vars).'</packet>',
			'POST'
		);

		if(
			!($http->getResponseCode() != 200 && !$this->setErrorByHttp($http)) &&
			($return = $this->parseReturn($http->getResponseBody(), $operation, $action))
		){			return $return;		}
		return false;	}

	private function parseReturn($text, $operation = '', $action = ''){		/*
			Проверяет что http валидный. Диагностирует ошибки. Если ошибок не найдено возвращает разобранный массив иначе false
		*/

		if(false === ($return = XML::parseXML($text))){			$this->setErrorParams(7);			return false;
		}
		$return = $return['packet'];

		if(!empty($return['system']['errcode'])){			$this->setIntError($return['system']);
			return false;		}
		elseif($operation && $action && !empty($return[$operation][$action]['result']['errcode'])){			$this->setIntError($return[$operation][$action]['result']);
			return false;
		}

		if(!$this->code) $this->setErrorParams(0);
		return $return;
	}

	private function setIntError($xml){
		/*
			Устанавливает параметр внутренней ошибки запроса
		*/

		if($xml['errcode'] == '1001') $this->setErrorParams(10);
		elseif($xml['errcode'] == '1014') $this->setErrorParams(6);
		else $this->setErrorParams($xml['errcode'], $xml['errtext']);
	}

	private function convert2standart($list){		/*
			Конвертирует параметры из вариации для старого интерфейса в новый
		*/

		$return = array();
		foreach($list as $i => $e){			$return[] = array('name' => $i, 'value' => $e);		}

		return $return;	}



	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(
				'ip' => array('type' => 'checkbox_array', 'text' => '{Call:Lang:modules:bill_hosting:ipadresa}', 'extra' => array('sort' => 0), 'aacc' => 1, 'mpkg' => 0, 'pkg_list' => 0),
				'domain-template' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:shablondomen}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('sort' => 100)),
				'domain-template-modify' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:izmenitshabl}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('sort' => 100)),
				'shared-domain-template' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:shablondomen1}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('apkg_hidden' => 1, 'value' => 1, 'sort' => 100)),
				'locale' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:lokalizatsii}', 'name' => 'language', 'pkg_list' => 0, 'aacc' => 1),
				'dns_zone_type' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:tipdnszony}', 'pkg_list' => 0, 'extra' => array('additional' => array('master' => '{Call:Lang:modules:bill_hosting:osnovnaia}', 'slave' => '{Call:Lang:modules:bill_hosting:vtorichnaia}'))),
				'overuse' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:politikaprip}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('additional' => array('block' => '{Call:Lang:modules:bill_hosting:blokirovat}', 'notify' => '{Call:Lang:modules:bill_hosting:uvedomliat}', 'normal' => '{Call:Lang:modules:bill_hosting:poumolchanii}'), 'sort' => 100)),
				'shell' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkass3}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('additional' => array('/bin/false' => '{Call:Lang:modules:bill_hosting:net}', '/bin/sh' => '/bin/sh', '/bin/bash' => '/bin/bash', '/sbin/nologin' => '/sbin/nologin', '/bin/tcsh' => '/bin/tcsh', '/bin/csh' => '/bin/csh', '/usr/local/psa/bin/chrootsh' => '/usr/local/psa/bin/chrootsh', '/bin/rbash' => '/bin/rbash'))),
				'manage_sh_access' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:tipdostupach}', 'pkg_list' => 0, 'extra' => array('additional' => array('' => '{Call:Lang:modules:bill_hosting:klientnemozh}', 'chrooted' => '{Call:Lang:modules:bill_hosting:dostuptolkov}', 'any' => '{Call:Lang:modules:bill_hosting:liubojtipdos}'))),
				'webstat' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:statistikave}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('additional' => array('none' => '{Call:Lang:modules:bill_hosting:net}', 'awstats' => 'AW-stats', 'webalizer' => 'Webalizer', 'smarterstats' => 'Smarter stats', 'urchin' => 'Urchin'))),
				'php-type' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkaph}', 'pkg_list' => 0, 'extra' => array('additional' => array('' => '{Call:Lang:modules:bill_hosting:net}', 'module' => '{Call:Lang:modules:bill_hosting:kakmodulapac}', 'cgi' => '{Call:Lang:modules:bill_hosting:kakcgi}', 'fastcgi' => '{Call:Lang:modules:bill_hosting:kakfastcgi}'))),
				'webmail' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:webinterfejs}', 'pkg_list' => 0, 'extra' => array('additional' => array('none' => '{Call:Lang:modules:bill_hosting:net}', 'horde' => 'Horde IMP', 'atmail' => 'AtMail', 'atmailcom' => 'Atmail Multi-domain'))),
				'nonexistent-user' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:chtodelatspi}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('additional' => array('bounce' => '{Call:Lang:modules:bill_hosting:vernutotprav}', 'forward' => '{Call:Lang:modules:bill_hosting:pereslatnasp}', 'reject' => '{Call:Lang:modules:bill_hosting:udalit}'), 'value' => 'reject')),
				'domain-stat' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:skolkomesiat}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('sort' => 100)),
				'log-rotation' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:udaliatstary}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('additional' => array('' => '{Call:Lang:modules:bill_hosting:neudaliat}', 'BySize' => '{Call:Lang:modules:bill_hosting:udaliattolko}', 'Daily' => '{Call:Lang:modules:bill_hosting:ezhednevno}', 'Weekly' => '{Call:Lang:modules:bill_hosting:ezhenedelno}', 'Monthly' => '{Call:Lang:modules:bill_hosting:ezhemesiachn}'))),
				'log-bysize' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:udaliatlogib}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('sort' => 100)),
				'log-max-num-files' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:maksimalnoek}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('sort' => 100)),
				'log-compress' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:szhimatlogi}', 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('sort' => 100)),
				'log-email' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:otpravitlogi}', 'noFunc' => true, 'mpkg' => 0, 'pkg_list' => 0, 'extra' => array('comment' => '{Call:Lang:modules:bill_hosting:ukazhiteemai}', 'sort' => 100)),
				'disk_space' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:diskovoepros}', 'k' => pow(2, 20), 'name' => 'quota'),
				'allocate_ip' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvor}', 'name' => 'ips'),
				'max_traffic' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:trafikmb}', 'k' => pow(2, 20), 'name' => 'bandwidth'),
				'bandwidth-speed' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:maksimalnaia}', 'k' => pow(2, 10), 'extra' => array('warn' => '{Call:Lang:modules:bill_hosting:vyneukazalim}')),
				'max_connections' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:maksimalnoek1}', 'extra' => array('warn' => '{Call:Lang:modules:bill_hosting:vyneukazalim1}')),
				'max_dom' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvod}', 'name' => 'vdomains'),
				'max_dom_aliases' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvop3}', 'name' => 'domainptr'),
				'max_subdom' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvos}', 'name' => 'nsubdomains'),
				'max_wu' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvov}'),
				'max_webapps' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvoj}'),
				'max_db' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvob1}', 'name' => 'mysql'),
				'max_maillists' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvos1}', 'name' => 'nemailml'),
				'max_redir' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvop4}', 'name' => 'nemailf'),
				'max_mg' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvop5}'),
				'max_resp' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvop6}', 'name' => 'nemailr'),
				'max_box' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvop7}', 'name' => 'nemails'),
				'mbox_quota' => array('type' => 'text', 'unlimit' => '-1', 'text' => '{Call:Lang:modules:bill_hosting:ogranichenie4}', 'k' => pow(2, 20)),
				'ftp_quota' => array('type' => 'text', 'unlimit' => '-1', 'k' => pow(2, 20), 'text' => '{Call:Lang:modules:bill_hosting:kvotanaftpmb}'),
				'create_domains' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:razreshitsoz}', 'pkg_list' => 0),
				'manage_phosting' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravleniefi}', 'pkg_list' => 0),
				'manage_quota' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:ustanovkakvo}', 'pkg_list' => 0),
				'manage_domain_aliases' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:nastrojkaalt}', 'pkg_list' => 0),
				'manage_subdomains' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravleniesu}', 'pkg_list' => 0),
				'manage_dns' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravleniezo}', 'name' => 'dnscontrol'),
				'manage_log' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravlenieob}', 'pkg_list' => 0),
				'manage_crontab' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravleniepl}', 'name' => 'cron'),
				'change_limits' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:nastrojkaogr}', 'pkg_list' => 0),
				'manage_anonftp' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:anonimnyjftp}', 'name' => 'aftp'),
				'manage_webapps' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravlenieve}', 'pkg_list' => 0),
				'manage_maillists' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravleniesp}', 'pkg_list' => 0),
				'remote_access_interface' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:vozmozhnosti}', 'pkg_list' => 0),
				'manage_performance' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravleniena}', 'pkg_list' => 0),
				'cp_access' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:dostupkpanel}'),
				'manage_dashboard' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:nastrojkarab}', 'pkg_list' => 0),
				'manage_spamfilter' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravleniefi1}', 'name' => 'spam'),
				'manage_virusfilter' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravleniean}'),
				'select_db_server' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:vozmozhnostv}', 'pkg_list' => 0),
				'allow_ftp_backups' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:razreshitvyp}', 'pkg_list' => 0),
				'allow_local_backups' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:razreshitvyp1}', 'pkg_list' => 0),
				'stdgui' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:mozhnoupravl}', 'pkg_list' => 0),
				'ssl' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => 'SSL'),
				'ssi' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkass2}'),
				'wu_script' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:razreshitvyp2}', 'pkg_list' => 0),
				'cgi' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkacg}'),
				'perl' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkape}'),
				'python' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkapy}'),
				'coldfusion' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkaco}'),
				'asp' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkaas}'),
				'miva' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:podderzhkami}'),
				'fastcgi' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:fastcgidliar}', 'pkg_list' => 0),
				'errdocs' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:stranitsyosh}'),
				'php_safe_mode' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:phpvrezhimes}', 'pkg_list' => 0),
				'manage_php_safe_mode' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravleniebe}', 'pkg_list' => 0),
				'site_builder' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:ispolzovatsi}'),
				'sbnet-user' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:sozdatpolzov}', 'mpkg' => 0, 'pkg_list' => 0),
				'sb_publishing' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:razreshitpub}', 'pkg_list' => 0),
				'create-sb-subdomains' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:razreshitsub}', 'pkg_list' => 0),
				'manage_webstat' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:upravlenieve1}', 'name' => 'sysinfo'),
				'webstat_protected' => array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:zashchititpr}', 'pkg_list' => 0),
				'cname' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:kompaniia}', 'noFunc' => true, 'apkg' => 0, 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1, 'extra' => array('warn_function' => '')),
				'phone' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:telefon}', 'noFunc' => true, 'apkg' => 0, 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1, 'extra' => array('warn_function' => '')),
				'fax' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:faks}', 'noFunc' => true, 'apkg' => 0, 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1, 'extra' => array('warn_function' => '')),
				'country' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:strana}', 'apkg' => 0, 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1, 'extra' => array('eval' => 'return array("additional" => Geo::getCountries());')),
				'city' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:gorod}', 'noFunc' => true, 'apkg' => 0, 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1, 'extra' => array('warn_function' => '')),
				'address' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:adres}', 'noFunc' => true, 'apkg' => 0, 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'opkg' => 1, 'extra' => array('warn_function' => ''))
			),
			'Plesk',
			'bill_hosting'
		);
	}
}

?>