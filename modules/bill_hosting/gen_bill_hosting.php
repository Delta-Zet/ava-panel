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

class gen_bill_hosting extends serviceExtensionsObject {
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

	private $zoneFilter = false;

	public function __ava__setAddPkgMatrix($obj, $service, $params = array()){		/*
			Мацрица добавления пакета
		*/

		$obj->addFormBlock(
			$params['fObj'],
			array(
				'pkgdsc_save_params_in' => array(
					'type' => 'select',
					'text' => '{Call:Lang:modules:bill_hosting:prisozdaniia}',
					'comment' => '{Call:Lang:modules:bill_hosting:eslibudetust}',
					'additional' => array(
						'server' => '{Call:Lang:modules:bill_hosting:naservere}',
						'billing' => '{Call:Lang:modules:bill_hosting:vbillinge}'
					),
					'value' => 'server'
				)
			),
			array(),
			array(),
			'block0'
		);
		if(!empty($params['server'])) $this->callServerExtension($params['server'], 'setAddPkgMatrix', $obj, $service, $params);
		return true;
	}

	public function __ava__checkAddPkgMatrix($obj, $service, $params = array()){		/*
			Проверка формы добавления пакета. Возвращает доп. параметры, которые следует установить
		*/

		$return = $this->callServerExtension($params['server'], 'checkAddPkgMatrix', $obj, $service, $params);
		if(!is_array($return)) $return = array();
		$return['save_params_in'] = $obj->values['pkgdsc_save_params_in'];
		return $return;
	}

	public function __ava__setAccOrderMatrix($obj, $service, $params = array()){
		/*
			Устанавливает доп. поля матрицы заказа
			Если есть заказанные домены - делает их предвыставленными в заказе
		*/

		$excl = array();
		$sData = $obj->serviceData($service);
		if($params['genLoginAuto'] = !empty($sData['gen_login_auto'])) $excl[] = $params['prefix'].'ident'.$params['id'];

		if($params['genPwdAuto'] = !empty($sData['gen_pwd_auto'])){			$excl[] = $params['prefix'].'pwd'.$params['id'];
			$excl[] = $params['prefix'].'cpwd'.$params['id'];		}

		$this->addFormBlock($params['fObj'], 'order', $params, $excl, $params['bName']);
		if($params['server']) $this->callServerExtension($params['server'], 'setAccOrderMatrix', $obj, $service, $params);
	}

	public function __ava__checkAccOrderMatrix($obj, $service, $params = array()){
		/*
			Устанавливает доп. поля матрицы заказа
			Если есть заказанные домены - делает их предвыставленными в заказе
		*/

		$zone = gen_bill_domains::getDomainZone($params['values'][$params['prefix'].'domain'.$params['id']]);
		if($this->zoneIsFiltered($zone)) $obj->setError($params['prefix'].'domain'.$params['id'], "Заказ хостинга для доменов $zone запрещен");
		if($params['server']) $this->callServerExtension($params['server'], 'checkAccOrderMatrix', $obj, $service, $params);
	}

	public function __ava__setAddAccMatrix($obj, $service, $params = array()){
		/*
			Устанавливает доп. поля матрицы заказа
			Если есть заказанные домены - делает их предвыставленными в заказе
		*/

		if(!$params['fObj']->getValue($params['prefix'].'ident'.$params['id'])){			$eData = $obj->getOrderEntry($params['eId']);

			if(!empty($eData['extra']['params1']['domain'])) $login = Library::cyr2translit($eData['extra']['params1']['domain']);
			else $login = $obj->getClientEml($eData['client_id']);
			$login = regExp::substr(regExp::replace("/\W/", "", $login, true), 0, 7);

			$i = '';
			while($obj->DB->cellFetch(array('orders_'.$service, 'login', "`login`='".$login.$i."'"))) $i ++;			$params['fObj']->setValue($params['prefix'].'ident'.$params['id'], $login.$i);
		}

		if(!$params['fObj']->getValue($params['prefix'].'pwd'.$params['id'])){
			$pwd = Library::inventPass(8);			$params['fObj']->setValue($params['prefix'].'pwd'.$params['id'], $pwd);
			$params['fObj']->setValue($params['prefix'].'cpwd'.$params['id'], $pwd);		}

		$params['fObj']->rmExcludes(array($params['prefix'].'ident'.$params['id'], $params['prefix'].'pwd'.$params['id'], $params['prefix'].'cpwd'.$params['id']));
		if($params['server']) $this->callServerExtension($params['server'], 'setAddAccMatrix', $obj, $service, $params);
	}

	public function __ava__getOrderEntry($obj, $service, $params = array()){		/*
			Запись в счете
		*/

		$eData = $obj->getOrderEntry($params['id'], true);

		if($eData['entry_type'] == 'new'){
			$pkgData = $obj->serviceData($service, $eData['package']);
			if($eData['ident']) return "Услуга {$pkgData['service_textname']}, аккаунт ".$eData['ident'];
			elseif(!empty($eData['extra']['params1']['domain'])) return "Услуга {$pkgData['service_textname']}, домен ".$eData['extra']['params1']['domain'];
			return "Услуга {$pkgData['service_textname']}, тариф ".$pkgData['text'];
		}

		return false;
	}

	public function __ava__getServiceCaption($obj, $service, $params = array()){
		/*
			Запись в счете
		*/

		$sData = $obj->getOrderedService($params['id'], true);
		$pkgData = $obj->serviceData($service, $eData['package']);
		return "Услуга {$pkgData['service_textname']}, аккаунт ".$sData['ident'];
	}

	public function __ava__addAcc($obj, $service, $params = array()){
		/*
			Устанавливает все доп. параметры которые следует установить в связи с добавлением акка
		*/

		$eData = $obj->getOrderEntry($params['id']);
		$pkgData = $obj->serviceData($eData['service'], $eData['package']);
		$params['installData'] = $eData['extra']['params1'];

		if($pkgData['vars']['save_params_in'] == 'billing' || !$obj->modifyIsEmpty($eData['extra']['params3'])){
			$params['installData'] = Library::array_merge($obj->sumParams($pkgData['vars'], $eData['extra']['params3'], $service), $params['installData']);
			$params['modify'] = true;
		}

		$obj->upOrderEntry(
			$params['id'],
			array(
				'extra' => array(
					'params2' => array(
						'domain' => isset($eData['extra']['params1']['domain']) ? $eData['extra']['params1']['domain'] : '',
						'login' => $eData['ident'],
					)
				)
			)
		);

		if($params['server']) return $this->callServerExtension($params['server'], 'addAcc', $obj, $service, $params);
		return true;
	}

	public function __ava__modifyAcc($obj, $service, $params = array()){
		/*
			Устанавливает все доп. параметры которые следует установить в связи с добавлением акка
		*/

		if($params['server']){			$mData = $obj->getServiceMainModifyData($params['id']);
			$pkgData = $obj->serviceData($mData['service'], $mData['pkg']);

			if($pkgData['vars']['save_params_in'] == 'billing' || !$obj->modifyIsEmpty($mData['vars']['params3'])){
				$params['installData'] = $obj->sumParams($pkgData['vars'], $eData['vars']['params3'], $service);
				$params['modify'] = true;
			}

			return $this->callServerExtension($params['server'], 'modifyAcc', $obj, $service, $params);		}
		else return Library::array_fill($params['accs'], true);
	}





















































































	public function __ava__getServiceParams($obj, $service, $params = array()){		/*
			Устанавливает дополнительные параметры для услуги
		*/

		$tbl = 'orders_'.$service;
		$obj->DB->Alter(array($tbl, array('add' => array('login' => '', 'domain' => '', 'ip' => '', 'name' => ''))));
		return true;
	}


	/********************************************************************************************************************************************************************

																	Взаимодействие с биллингом

	*********************************************************************************************************************************************************************/

	public function __ava__zoneIsFiltered($zone){
		/*
			Вызывается при создании формы услуги
		*/

		if($this->zoneFilter === false){			$this->zoneFilter = $this->DB->columnFetch(array('filter', 'zone', 'zone'));		}
		return !empty($this->zoneFilter[$zone]);
	}

	public function __ava__setNewService($obj, $service, $params){		/*
			Вызывается при создании формы услуги
		*/

		$fObj = $this->addFormBlock(
			$params['fObj'],
			array(
				'gen_login_auto' => array(
					'type' => 'checkbox',
					'text' => '{Call:Lang:modules:bill_hosting:generirovatl}'
				),
				'gen_pwd_auto' => array(
					'type' => 'checkbox',
					'text' => 'Генерировать пароль автоматически'
				)
			)
		);
	}

	public function getNewServiceParams($obj, $service, $params){		return array(
			'gen_login_auto' => empty($obj->values['gen_login_auto']) ? '' : $obj->values['gen_login_auto'],
			'gen_pwd_auto' => empty($obj->values['gen_pwd_auto']) ? '' : $obj->values['gen_pwd_auto'],
		);
	}
}

?>