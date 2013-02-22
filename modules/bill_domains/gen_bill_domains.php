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



$GLOBALS['Core']->loadExtension('billing', 'serviceExtensionsObject');

class gen_bill_domains extends serviceExtensionsObject {
	/*
		Расширение по подключаемым удаленным панелям
		1. Управление формой создания новых соединений
		2. Проверка правильности соединения


		Расширения для отдельных услуг предполагают следующие возможности:
		1. Управление формой модификации услуги
		2. Управление формой описания ТП
		3. Управление формой модификации подключений
		4. Управление списком тарифов
		5. Управление списком заказов
		6. Участие в списке шаблонов для вывода списка тарифов
		7. Управление таблицами тарифов и услуг
	*/


	private $billingMod;
	private $billingModName;
	private $servicesName;

	private $newBlank;
	private $owners = array();
	private $ownerParams = array();
	private $ownersCorrespond = array();

	/********************************************************************************************************************************************************************

																			Служебные функции

	*********************************************************************************************************************************************************************/

	public function __ava____authUser($obj){
		/*
			Доп. аутентификация пользовтеля
		*/

		return array('blanks' => $this->getOwners());
	}

	public function __billing_order2($obj, $fObj, &$cnt){		/*
			Проверяет есть ли анкета у юзера. Если нету, предлагает ее создать, иначе возвращает дальше на регистрацию
		*/

		$clientId = $obj->getClientId();
		$needs = array();

		foreach($obj->values['entries'] as $i => $e){
			$eData = $obj->getOrderEntry($e);
			$pkgData = $obj->serviceData($eData['service'], $eData['package']);

			if($pkgData['extension'] == $this->mod){
				if(!$this->getOwners($clientId, $need = $this->getNeedOwnerType($pkgData['server_name']))){
					$needs[$need] = 1;
				}
			}
		}

		if($needs){			$this->addFormBlock($fObj, 'domain_owners', array(), array(), 'order'.$cnt);
			$userData = $obj->getUserByClientId($eData['client_id']);
			$names = regExp::Split("/\s+/", trim($userData['name']), true);
			$type = ($userData['type'] == 'person' || $userData['type'] == 'organization' || $userData['type'] == 'ip') ? $userData['type'] : 'person';

			$fObj->setValues(
				Library::array_merge(
					$obj->getUserByClientId($eData['client_id']),
					array(
						'type' => $type,
						'blankName' => library::getEmptyHashIndex(library::arrayValues2keys($this->getOwners($eData['client_id'])), $userData['name']),
						'fname' => isset($names['1']) ? $names['1'] : $names['0'],
						'pname' => isset($names['2']) ? $names['2'] : '',
						'lname' => $names['0'],
						'correspond' => $needs
					)
				)
			);

			$fObj->setParam('caption'.$cnt, 'Анкета регистранта домена');
			$fObj->setHiddens(array('newBlank' => 1));
			$cnt++;		}
	}

	public function __billing_check_order3($obj){		/*
			Создает анкету регистранта доменов
		*/

		$id = isset($obj->values['modify']) ? $obj->values['modify'] : 0;
		$clientId = $obj->getClientId();
		if($this->DB->cellFetch(array('domain_owners', 'id', "`client_id`='{$clientId}' AND `name`='{$obj->values['blankName']}' AND `id`!='$id'"))) $obj->setError('blankName', 'Такое имя уже используется');

		if(
			!empty($obj->values['correspond']['eu']) &&
			$obj->values['country'] != 'BE' &&
			$obj->values['country'] != 'DK' &&
			$obj->values['country'] != 'FR' &&
			$obj->values['country'] != 'DE' &&
			$obj->values['country'] != 'GR' &&
			$obj->values['country'] != 'IE' &&
			$obj->values['country'] != 'AT' &&
			$obj->values['country'] != 'BG' &&
			$obj->values['country'] != 'GB' &&
			$obj->values['country'] != 'HU' &&
			$obj->values['country'] != 'IE' &&
			$obj->values['country'] != 'ES' &&
			$obj->values['country'] != 'IT' &&
			$obj->values['country'] != 'CY' &&
			$obj->values['country'] != 'LV' &&
			$obj->values['country'] != 'LT' &&
			$obj->values['country'] != 'LU' &&
			$obj->values['country'] != 'MT' &&
			$obj->values['country'] != 'NL' &&
			$obj->values['country'] != 'PL' &&
			$obj->values['country'] != 'PT' &&
			$obj->values['country'] != 'RO' &&
			$obj->values['country'] != 'SK' &&
			$obj->values['country'] != 'SI' &&
			$obj->values['country'] != 'FI' &&
			$obj->values['country'] != 'FR' &&
			$obj->values['country'] != 'CZ' &&
			$obj->values['country'] != 'SE' &&
			$obj->values['country'] != 'EE'
		)  $obj->setError('correspond', 'Для регистрации анкет пригодных для доменов .eu ваша страна должна находиться в еврозоне');
	}

	public function __billing_order3($obj){
		/*
			Создает анкету регистранта доменов
		*/

		if(!empty($obj->values['newBlank'])){
			$clientId = $obj->getClientId();
			$id = $this->setNewBlank($obj, $clientId);

			foreach($obj->values['entries'] as $i => $e){
				$obj->values['domain_owner'.$i] = $id;
				$obj->values['domain_owner_a'.$i] = $id;
				$obj->values['domain_owner_b'.$i] = $id;
				$obj->values['domain_owner_t'.$i] = $id;
			}
		}
	}


	/********************************************************************************************************************************************************************

																		Взаимодействие с биллингом

	*********************************************************************************************************************************************************************/

	public function setPkgsListEntriesPreset($obj, $service, $params){
		/*
			Предварительная установка параметров
		*/

		unset($params['list']['order']);
	}

	public function setAddPkgMatrix($obj, $service, $params = array()){
		/*
			Устанавливает матрицу добавления тарифа
		*/

		if($obj->getFunc() == 'packagesData') $block = 'block0';
		else $block = 'form';

		$obj->addFormBlock(
			$params['fObj'],
			array(
				'server_name' => array(
					'text' => '{Call:Lang:modules:bill_domains:domennaiazon}',
					'comment' => '{Call:Lang:modules:bill_domains:zdessleduetu}',
					'warn_pattern' => '/^[a-zа-яё][a-zа-яё\.]*$/'
				)
			),
			array(),
			array(),
			$block
		);

		return true;
	}

	public function checkAddPkgMatrix($obj, $service, $params = array()){
		$obj->values['server_name'] = regExp::lower(regExp::replace("/^\./iUs", '', $obj->values['server_name']));
	}

	public function __ava__setAccOrderMatrix($obj, $service, $params = array()){
		/*
			Устанавливает доп. поля матрицы заказа
			Предлагается создать новую анкету или использовать ранее созданную.
			Если регится новый аккаунт, данные кочуют из формы реги нового акка в данные анкеты
			Если пользователь уже зареген
		*/

		$eData = $obj->getOrderEntry($params['eId']);
		$pkgData = $obj->serviceData($eData['service'], $eData['package']);
		$params['pkg'] = $pkgData['server_name'];

		$params['owners'] = $this->getOwners($eData['client_id'], $this->getNeedOwnerType($pkgData['server_name']));
		$params['serverName'] = $pkgData['server_name'];
		$this->addFormBlock($params['fObj'], 'order', $params, array($params['prefix'].'auto_prolong_fract'.$params['id']), $params['bName']);

		$values[$params['prefix'].'ident'.$params['id']] = $eData['ident'];
		$values[$params['prefix'].'ns1_'.$params['id']] = $GLOBALS['Core']->getParam('ns1', $this->mod);
		$values[$params['prefix'].'ns2_'.$params['id']] = $GLOBALS['Core']->getParam('ns2', $this->mod);

		$values[$params['prefix'].'ns3_'.$params['id']] = $GLOBALS['Core']->getParam('ns3', $this->mod);
		$values[$params['prefix'].'ns4_'.$params['id']] = $GLOBALS['Core']->getParam('ns4', $this->mod);
		$params['fObj']->setValues($values);

		$params['fObj']->setParam('caption'.(isset($params['cnt']) ? $params['cnt'] : $params['id']), '{Call:Lang:modules:bill_domains:domen:'.Library::serialize(array($eData['ident'])).'}');
		if($params['server']) $this->callServerExtension($params['server'], 'setAccOrderMatrix', $obj, $service, $params);
	}

	public function __ava__checkAccOrderMatrix($obj, $service, $params = array()){
		/*
			Проверяет правильность ввода
			Возвращает список значений которые будут храниться как временно установленные к заказу
		*/

		if(
			($obj->values[$params['prefix'].'ns1_'.$params['id']] && $obj->values[$params['prefix'].'ns1_'.$params['id']] == $obj->values[$params['prefix'].'ns2_'.$params['id']]) ||
			($obj->values[$params['prefix'].'ns1_'.$params['id']] && $obj->values[$params['prefix'].'ns1_'.$params['id']] == $obj->values[$params['prefix'].'ns3_'.$params['id']]) ||
			($obj->values[$params['prefix'].'ns1_'.$params['id']] && $obj->values[$params['prefix'].'ns1_'.$params['id']] == $obj->values[$params['prefix'].'ns4_'.$params['id']]) ||
			($obj->values[$params['prefix'].'ns2_'.$params['id']] && $obj->values[$params['prefix'].'ns2_'.$params['id']] == $obj->values[$params['prefix'].'ns3_'.$params['id']]) ||
			($obj->values[$params['prefix'].'ns2_'.$params['id']] && $obj->values[$params['prefix'].'ns2_'.$params['id']] == $obj->values[$params['prefix'].'ns4_'.$params['id']]) ||
			($obj->values[$params['prefix'].'ns3_'.$params['id']] && $obj->values[$params['prefix'].'ns3_'.$params['id']] == $obj->values[$params['prefix'].'ns4_'.$params['id']])
		){
			$obj->setError('ns1_'.$params['id'], '{Call:Lang:modules:bill_domains:nelziaispolz}');
		}

		if(!empty($obj->values[$params['prefix'].'ident'.$params['id']])){
			$obj->Core->LoadExtension('bill_domains', 'whois');
			$whois = new whois($obj->values[$params['prefix'].'ident'.$params['id']], $this->DB);
			$whois->send();
			$r = $whois->getResultStatus();

			if($r == 1) $obj->setError($params['prefix'].'ident'.$params['id'], 'Домен занят');
			elseif($r == 2) $obj->setError($params['prefix'].'ident'.$params['id'], 'Whois-сервер недоступен');
			elseif($r == 3) $obj->setError($params['prefix'].'ident'.$params['id'], 'Домен недопустим');
			elseif($r) $obj->setError($params['prefix'].'ident'.$params['id'], 'Ошибка проверки домена');
		}

		if($params['server']) $this->callServerExtension($params['server'], 'checkAccOrderMatrix', $obj, $service, $params);
	}

	public function __ava__getOrderEntry($obj, $service, $params = array()){
		/*
			Возвращает вариант записи для счета
		*/

		$eData = $obj->getOrderEntry($params['id'], true);
		if($eData['entry_type'] == 'prolong') return '{Call:Lang:modules:bill_domains:prodleniedom:'.Library::serialize(array($eData['ident'])).'}';
		else return '{Call:Lang:modules:bill_domains:registratsii:'.Library::serialize(array($eData['ident'])).'}';
	}

	public function __ava__getServiceCaption($obj, $service, $params = array()){
		/*
			Запись в счете
		*/

		$sData = $obj->getOrderedService($params['id'], true);
		return "Домен ".$sData['ident'];
	}

	public function __ava__addAcc($obj, $service, $params = array()){
		/*
			Устанавливает все доп. параметры которые следует установить в связи с добавлением акка
		*/

		$eData = $obj->getOrderEntry($params['id']);

		$obj->upOrderEntry(
			$params['id'],
			array(
				'extra' => array(
					'params2' => array(
						'domain' => $eData['ident'],
						'domain_owner' => $eData['extra']['params1']['domain_owner'],
					)
				)
			)
		);

		if($params['server']) return $this->callServerExtension($params['server'], 'addAcc', $obj, $service, $params);
		return true;
	}

	public function __ava__setAccUserProlongMatrix($obj, $service, $params = array()){
		/*
			Форма продления домена
		*/

		$eData = $obj->getOrderEntry($params['eId']);
		$this->addFormBlock(
			$params['fObj'],
			array(
				$params['prefix'].'term'.$params['id'] => array(
					'text' => '{Call:Lang:modules:bill_domains:domen:'.Library::serialize(array($eData['s_ident'])).'}'
				)
			),
			array(),
			array(),
			$params['bName']
		);

		$this->callServerExtension($eData['s_server'], 'setProlongAccMatrix', $obj, $service, $params);
		return true;
	}

	public function __ava__setProlongAccMatrix($obj, $service, $params = array()){
		/*
			Форма продления домена
		*/

		$eData = $obj->getOrderEntry($params['eId']);
		$this->addFormBlock(
			$params['fObj'],
			array(
				$params['prefix'].'term'.$params['id'] => array(
					'text' => 'Срок продления домена '.$eData['s_ident'].', лет'
				)
			),
			array(),
			array(),
			$params['bName']
		);

		$this->callServerExtension($eData['s_server'], 'setProlongAccMatrix', $obj, $service, $params);
		return true;
	}






























	/********************************************************************************************************************************************************************

																					Прочие

	*********************************************************************************************************************************************************************/

	public function __ava__addDomainOwner($blankName, $clientId, $correspond, $params, $id = false){
		/*
			Добавляет пользователя в анкету
		*/

		return $this->DB->{$id ? 'Upd' : 'Ins'}(array('domain_owners', array('client_id' => $clientId, 'name' => $blankName, 'correspond' => Library::arrKeys2str($correspond), 'vars' => $params)));
	}

	public function __ava__setNewBlank($obj, $clientId){
		/*
			Добавляет хуй-ню
		*/

		$dFields = $this->getFields('domain_owners', array('blankName', 'correspond'), true, $obj->values);
		foreach($dFields as $i => $e) $dFields[$i] = trim($e);
		return $this->addDomainOwner($obj->values['blankName'], $clientId, $obj->values['correspond'], $dFields, isset($obj->values['modify']) ? $obj->values['modify'] : 0);
	}

	public function getServiceParams($obj, $service, $params = array()){		/*
			При внесении услуги, помеченной расширением "Домены" вызывается этот метод
		 */

		$tbl = 'orders_'.$service;
		$obj->DB->Alter(array($tbl, array('add' => array('domain' => '', 'domain_owner' => 'INT', 'login' => ''))));
		return true;
	}

	public function getDomainZone($domain, &$d2 = ''){		/*
			Выделяет доменную зону из имени домена
		*/

		$domain = regExp::lower($domain);
		$parts = regExp::split('.', $domain, false, 2);
		$d2 = $parts[0];
		return $parts[1];
	}

	public function getBillingModName(){
		/*
			Возвращает модуль биллинга привязанный к этому модулю доменов
		*/

		if(!$this->billingModName){
			$this->billingModName = $this->Core->getUnitedModule($this->mod, 'billing');
		}

		return $this->billingModName;
	}

	public function getBillingMod(){		/*
			Возвращает модуль биллинга привязанный к этому модулю доменов
		*/

		if(!is_object($this->billingMod)){			$this->billingMod = $this->Core->callModule($this->getBillingModName());		}

		return $this->billingMod;	}

	public function getServices(){		/*
			Возвращает имена всех услуг (идентификаторы) обслуживаемых данным модулем
		*/

		if(!$this->servicesName){			$this->servicesName = $this->getBillingMod()->DB->columnFetch(array('services', 'text', 'name', "`extension`='{$this->mod}'"));		}

		return $this->servicesName;	}

	public function __ava__getRegistrators($service, $tld, $all = true){		/*
			Возвращает список всех регистраторов которые поддерживаются для данной доменной зоны,
			как idСоединения => имя соединения
		*/

		$bObj = $this->getBillingMod();
		$return = $bObj->getPkgsByServerName($service, $tld);

		if($all){			foreach($return as $i => $e){				$pData = $bObj->serviceData($service, $i);
				if(empty($pData['show']) || empty($pData['rights']['new'])) unset($return[$i]);			}		}

		return $return;
	}

	public function __ava__getTerms($tld){		/*
			Возвращает список всех сроков на которые можно заказать данный домен, при этом в списке будет указано для какого регистратора какой срок
		*/

		$return = array();
		$billObj = $this->getBillingMod();
		$services = $billObj->fetchServicesData();

		foreach($services as $i => $e){			foreach($e as $i1 => $e1){				if($e1['server_name'] == $tld) $return[$e1['server']] = $billObj->getTermsList($i, $i1);			}		}

		return $return;	}

	public function __ava__getOwnerParams($id){
		/*
			Возвращает список анкет клиента
		*/

		if(empty($this->ownerParams[$id])){			$this->ownerParams[$id] = $this->DB->rowFetch(array('domain_owners', '*', "`id`='$id'"));
			$this->ownerParams[$id]['vars'] = Library::unserialize($this->ownerParams[$id]['vars']);
		}

		return $this->ownerParams[$id];
	}

	public function __ava__getOwners($clientId = false, $correspond = false){
		/*
			Возвращает список анкет клиента
		*/

		if(!$clientId) $clientId = $this->getBillingMod()->getClientId();
		if(!isset($this->owners[$clientId])){
			$this->owners[$clientId] = array();
			$this->ownersCorrespond[$clientId] = array();

			foreach($this->DB->columnFetch(array('domain_owners', array('name', 'correspond'), 'id', "`client_id`='".$clientId."'", "`sort`")) as $i => $e){
				$this->owners[$clientId][$i] = $e['name'];
				foreach(Library::str2arrKeys($e['correspond']) as $i1 => $e1){
					$this->ownersCorrespond[$clientId][$i1][$i] = $e['name'];
				}
			}
		}

		return $correspond === false ? $this->owners[$clientId] : (isset($this->ownersCorrespond[$clientId][$correspond]) ? $this->ownersCorrespond[$clientId][$correspond] : array());
	}

	public function __ava__getNeedOwnerType($zone){		/*
			Возвращает небходимый тип анкеты по зоне
		*/

		switch($zone){			case 'ru': case 'su': case 'рф': return 'ru';
			case 'kz': case 'uz': case 'tj': return 'tj';
			case 'us': return 'us';
			case 'asia': return 'asia';
			case 'eu': return 'eu';
		}

		return false;	}

	public function __ava__regDomainOwnerForm($domain, $registrator, $action, $hiddens = array()){
		/*
			Генерирует форму для регистрации новой анкеты владельца домена
		*/

		$tld = $this->getDomainZone($domain);
		$this->setMeta('{Call:Lang:modules:bill_domains:domen:'.Library::serialize(array($domain)).'}');

		$this->setContent(
			$this->getFormText(
				$this->addFormBlock(
					$this->newForm(
						'regDomainOwnerForm',
						$action
					),
					'domain_owners',
					array(
						'tld' => $tld,
						'owners' => $this->getOwners()
					)
				),
				array(),
				$hiddens
			)
		);
	}

	public function fetchDomainOwnerParams(){		/*
			Извлекает список параметров домена из $this->values
		*/

		$domain = $this->values['domains'][$this->values['registered_owner']];
		$tld = $this->getDomainZone($domain);
		$owners = array();
		$return = array();

		require(_W.'modules/bill_domains/forms/domain_owners.php');
		foreach($matrix as $i => $e){			if(isset($this->values[$i])){				$return[$i] = $this->values[$i];			}		}

		return $return;	}


	/********************************************************************************************************************************************************************

																	Взаимодействие с биллингом

	*********************************************************************************************************************************************************************/

	public function __ava__setOrderListEntries($obj, $service, $params = array()){		/*
			Записи для списка заказанныхъ услугъ
		*/

		$params['lObj']->setTemplFile($this->Core->getModuleTemplatePath($this->mod).'list.tmpl');
		$params['lObj']->actions = Library::array_merge(
			$params['lObj']->actions,
			array(
				'modifyNs' => $this->path.'?mod='.$this->mod.'&func=modifyNs',
				'modifyWhois' => $this->path.'?mod='.$this->mod.'&func=modifyWhois'
			)
		);
	}

	public function setOrderAdminListParams($obj, $service, $params = array()){
		/*
			Записи для списка заказанныхъ услугъ
		*/

		return array('searchForm' => array('searchFields' => array('ident' => '{Call:Lang:modules:bill_domains:domen1}')));
	}
}

?>