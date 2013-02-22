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



$GLOBALS['Core']->loadExtension('bill_hosting', 'servconnectIsp');

class servConnectVDS extends servconnectIsp{


	/********************************************************************************************************************************************************************

																				Соединение

	*********************************************************************************************************************************************************************/

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		if($this->sendReq('vdspreset')) return true;
		return false;
	}


	/********************************************************************************************************************************************************************

																		Работа с пакетами

	*********************************************************************************************************************************************************************/

	public function __ava__setAddPkgMatrix($service, $params = array()){
		/*
			Матрица для пакета
			Создаются поля: Шаблон диска (в т.ч. чтобы можно было сделать новый),
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				'pkgdsc_'.$this->getParamsVar($service, 'disktempl') =>
					!($result = $this->sendReq('disktempl')) ?
						$templData = array('type' => 'text') :
						array('additional' => $this->formSelect($result['0']['doc']['elem'], 'name', 'name'))
			),
			array(),
			array(),
			'block2'
		);
	}

	//Управление пакетами
	public function __ava__addPkg($service, $params = array()){
		/*
			Добавляет пакет
		*/

		return $this->sendAddPkg($service, $params, 'vds');
	}

	public function __ava__delPkg($service, $params = array()){
		/*
			Удаление пакетов
			В передаваемом obj должен быть установлен список delPkg. Все они будут удалены
		*/

		return $this->sendDelPkg($service, $params, 'vdspreset.delete');
	}


	/********************************************************************************************************************************************************************

																		Работа с аккаунтами

	*********************************************************************************************************************************************************************/

	public function __ava__setAccOrderMatrix($service, $params = array()){
		/*
			Матрица для заказа
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array($params['prefix'].'domain'.$params['id'] => array('text' => 'Имя сервера (рекомендуется указать имя домена, если он есть)', 'warn' => '')),
			array(),
			array($params['prefix'].'ident'.$params['id']),
			$params['bName']
		);
	}

	public function __ava__setAddAccMatrix($service, $params = array()){
		/*
			Матрица для пакета
		*/

		$this->obj->addFormBlock(
			$params['fObj'],
			array(
				$params['prefix'].$this->getParamsVar($service, 'ip').$params['id'] => array(
					'additional' => Library::array_merge(array('auto' => '{Call:Lang:modules:bill_hosting:naznachitavt}'), $this->getIps(false))
				),
				$params['prefix'].$this->getParamsVar($service, 'disktempl').$params['id'] =>
					!($result = $this->sendReq('disktempl')) ? $templData = array('type' => 'text') : array('additional' => $this->formSelect($result['0']['doc']['elem'], 'name', 'name'))
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

		$eData = $this->obj->getOrderEntry($params['id'], true);
		$pkgData = $this->obj->serviceData($service, $eData['package']);
		$reqParams = $this->insertSpecialParams($this->convertParams($service, $params['installData']), true);

		$reqParams['vdspreset'] = $pkgData['server_name'];
		$reqParams['id'] = 'auto';
		$reqParams['name'] = $this->idna($eData['extra']['params1']['domain']);
		$reqParams['owner'] = $this->connectData['login'];

		if($return = $this->sendAddAcc($service, $params, $reqParams, false, 'vds.edit', $result)){
			$this->obj->upOrderEntry(
				$params['id'],
				array(
					'ident' => $result['0']['doc']['ip'],
					'extra' => array(
						'params1' => array($this->getParamsVar($service, 'ip') => $result['0']['doc']['ip']),
						'params2' => array('login' => $result['0']['doc']['ip']),
					)
				)
			);
		}

		return $return;
	}





























	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		$reqParams['vdspreset'] = $params['pkgData']['server_name'];
		return $this->sendModifyAcc($service, $params, $reqParams, 'vds.edit', 'vdspreset.edit');
	}

	public function __ava__delAcc($service, $params = array()){
		/*
			Удоляит аккаунт пользоватиля
		*/

		return $this->sendMultiAccsReq($service, $params, 'vds.delete');
	}

	public function __ava__suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, 'vds.disable');
	}

	public function __ava__unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, 'vds.enable');
	}

	public function __ava__isSuspendAcc($service, $params = array()){
		/*
			Проверяи чта акк из суспиндит
		*/

		$result = $this->sendReq('vds');
		if(Library::isHash($result['0']['doc']['elem'])) $result['0']['doc']['elem'] = array($result['0']['doc']['elem']);

		foreach($result['0']['doc']['elem'] as $i => $e){
			if($e['ip'] == $params['login']){
				if(isset($e['disabled'])) return 1;
				else return -1;
			}
		}

		return false;
	}

	public function __ava__listAccs($service, $params = array()){
		/*
			Усе акки
		*/

		$result = $this->sendReq('vds');
		if(Library::isHash($result['0']['doc']['elem'])) $result['0']['doc']['elem'] = array($result['0']['doc']['elem']);
		$return = array();

		foreach($result['0']['doc']['elem'] as $i => $e){
			$return[] = $e['ip'];
		}

		return $return;
	}



	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		return array(
			array(
				'ip' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:ipadres}', 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'apkg' => 0, 'name' => 'usedip', 'extra' => array('value' => 'auto', 'warn_function' => '')),
				'disktempl' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:shablondiska}', 'mpkg' => 0, 'aacc' => 1),
				'ispmgr' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:litsenziiais}', 'extra' => array('additional' => array('None' => '{Call:Lang:modules:bill_hosting:netlitsenzii}', 'Lite' => 'ISPmanager Lite', 'Prof' => 'ISPmanager Professional'))),
				'config' => array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:shablonkonfi}', 'extra' => array('additional' => array('none' => '{Call:Lang:modules:bill_hosting:neispolzovat}', 'basic' => 'basic', 'unlimited' => 'unlimited', 'light' => 'light'))),
				'traf' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:mesiachnyjtr}', 'name' => 'bandwidth', 'unlimit' => '999999999'),
				'disk' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:razmerdiska}', 'unlimit' => '9999999'),
				'mem' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:pamiat}', 'unlimit' => '99999'),
				'bmem' => array('type' => 'text', 'text' => 'Burstable RAM', 'unlimit' => '99999'),
				'cpu' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:chastotacpu}', 'unlimit' => '99999'),
				'ncpu' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvoc}', 'unlimit' => '99999'),
				'proc' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvop8}', 'unlimit' => '99999'),
				'desc' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:deskriptoryf}', 'unlimit' => '99999'),
				'ipcount' => array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvod2}', 'unlimit' => '999'),
				'vnet' => array('type' => 'checkbox', 'ch' => 'on', 'text' => '{Call:Lang:modules:bill_hosting:virtualnoese}')
			),
			'VDS Manager',
			'bill_hosting'
		);
	}
}

?>