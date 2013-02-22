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



$GLOBALS['Core']->loadExtension('bill_hosting', 'servConnectVDS');

class servConnectVDSReseller extends servConnectVDS{


	/********************************************************************************************************************************************************************

																				Соединение

	*********************************************************************************************************************************************************************/

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		if($this->sendReq('sysinfo')) return true;
		return false;
	}



	/********************************************************************************************************************************************************************

																		Работа с пакетами

	*********************************************************************************************************************************************************************/

	public function setAddPkgMatrix($service, $params = array()){
		/*
			Матрица для пакета
		*/

		return true;
	}

	//Управление пакетами
	public function __ava__addPkg($service, $params = array()){
		/*
			Добавляет пакет
		*/

		return $this->sendAddPkg($service, $params, 'vdsreseller');
	}

	public function __ava__delPkg($service, $params = array()){
		/*
			Удаление пакетов
			В передаваемом obj должен быть установлен список delPkg. Все они будут удалены
		*/

		return $this->sendDelPkg($service, $params, 'rslrtempl.delete');
	}


	/********************************************************************************************************************************************************************

																		Работа с аккаунтами

	*********************************************************************************************************************************************************************/

	public function __ava__setAccOrderMatrix($service, $params = array()){		/*
			Матрица для заказа
		*/

		$this->obj->addFormBlock($params['fObj'], array(), array(), array($params['prefix'].'domain'.$params['id']));
	}

	public function __ava__setAddAccMatrix($service, $params = array()){}

	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

		$eData = $this->obj->getOrderEntry($params['id'], true);
		$pkgData = $this->obj->serviceData($service, $eData['package']);
		return $this->sendAddAcc($service, $params, array('alevel' => 'reseller', 'rslrtempl' => $pkgData['server_name']), false, 'user.edit');
	}
























	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		$reqParams['rslrtempl'] = $params['pkgData']['server_name'];
		$reqParams['alevel'] = 'reseller';
		return $this->sendModifyAcc($service, $params, $reqParams, $func = 'user.edit', $pkgFunc = 'rslrtempl.edit');
	}

	public function __ava__delAcc($service, $params = array()){
		/*
			Удоляит аккаунт пользоватиля
		*/

		return $this->sendMultiAccsReq($service, $params, 'user.delete');
	}

	public function __ava__suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, 'user.disable');
	}

	public function __ava__unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, 'user.enable');
	}

	public function __ava__isSuspendAcc($service, $params = array()){
		/*
			Проверяи чта акк из суспиндит. Если да - вернет 1, если нет - -1, в иных случаях false
		*/

		$result = $this->sendReq('user');
		if(Library::isHash($result['0']['doc']['elem'])) $result['0']['doc']['elem'] = array($result['0']['doc']['elem']);

		foreach($result['0']['doc']['elem'] as $i => $e){			if($e['level'] == 'Reseller' && $e['name'] == $params['ident']){				if(isset($e['disabled'])) return 1;
				else return -1;			}		}

		return false;
	}

	public function __ava__listAccs($service, $params = array()){
		/*
			Усе акки. Возвращает список существующих логинов.
		*/

		$result = $this->sendReq('user');
		if(Library::isHash($result['0']['doc']['elem'])) $result['0']['doc']['elem'] = array($result['0']['doc']['elem']);
		$return = array();

		foreach($result['0']['doc']['elem'] as $i => $e){
			if($e['level'] == 'Reseller') $return[] = $e['name'];
		}

		return $return;
	}

	protected function accAliaces($params){		/*
			Устанавливает алиасы названиям параметров в акке
		*/

		$return = array();
		foreach($params as $i => $e){			if($i == 'vds' || $i == 'mem' || $i == 'cpu' || $i == 'disk') $return[$i.'limit'] = $e;
			else $return[$i] = $e;
		}

		return $return;	}



	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){		return array(
			array(
				'vds' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvov1}', 'unlimit' => '9999999'),
				'mem' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:ogranichenie5}', 'unlimit' => '9999999'),
				'cpu' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:ogranichenie6}', 'unlimit' => '9999999'),
				'maxmem' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:limitpamiati}', 'unlimit' => '99999'),
				'maxcpu' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:limitcpunavd}', 'unlimit' => '99999'),
				'maxdesc' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:limitfajlovn}', 'unlimit' => '99999'),
				'disk' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:razmerdiska}', 'unlimit' => '999999999'),
				'ipalias' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:limitdopipad}', 'unlimit' => '999999999'),
				'allowdns' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:razreshitisp}'),
				'allowlic' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:razreshitisp1}'),
				'allowbak' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:razreshitisp2}'),
			),
			'{Call:Lang:modules:bill_hosting:vdsmanagerpa}',
			'bill_hosting'
		);
	}
}

?>