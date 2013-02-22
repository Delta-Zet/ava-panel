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



$GLOBALS['Core']->loadExtension('bill_hosting', 'servconnectDa');

class servconnectDaReseller extends servconnectDa{


	/********************************************************************************************************************************************************************

																				Соединение

	*********************************************************************************************************************************************************************/

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		$http = $this->sendReq('/CMD_API_SHOW_ADMINS');
		if(regExp::Match('list[]', $http->getResponse())){
			return true;
		}

		if($this->setErrorByHttp($http)) $this->setIntError($http);
		return false;
	}



	/********************************************************************************************************************************************************************

																		Работа с пакетами

	*********************************************************************************************************************************************************************/

	public function setAddPkgMatrix($service, $params = array()){}

	public function __ava__addPkg($service, $params = array()){
		/*
			Добавляет пакет
		*/

		return $this->sendAddPkg($service, $params, '/CMD_MANAGE_RESELLER_PACKAGES', '/CMD_API_PACKAGES_RESELLER');
	}

	public function __ava__delPkg($service, $params = array()){
		/*
			Удаление пакетов
			В передаваемом obj должен быть установлен список delPkg. Все они будут удалены
		*/

		return $this->sendDelPkg($service, $params, '/CMD_MANAGE_RESELLER_PACKAGES', '/CMD_API_PACKAGES_RESELLER');
	}



	/********************************************************************************************************************************************************************

																		Работа с аккаунтами

	*********************************************************************************************************************************************************************/

	//Мацрицы для пользовательского акка
	public function __ava__setAccOrderMatrix($service, $params = array()){}

	public function __ava__setAddAccMatrix($service, $params = array()){}

	public function __ava__addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

		$this->insIps($service, $params);
		return $this->sendAddAcc($service, $params, '/CMD_ACCOUNT_RESELLER');
	}

	public function __ava__modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/

		$this->insIps($service, $params);
		return $this->sendModifyAcc($service, $params, '/CMD_MODIFY_RESELLER');
	}

	private function insIps($service, &$params){		$addIp = 0;
		$var = $this->getParamsVar($service, 'dns');
		$ips = $this->getParamsVar($service, 'ips');

		if(!empty($params['installData'][$var])){
			if($params['installData'][$var] == 'TWO') $addIp = 2;
			elseif($params['installData'][$var] == 'THREE') $addIp = 3;
			if($params['modify']) $params['installData'][$ips] = $params['installData'][$ips] + $addIp;
		}
	}

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

		return $this->sendMultiAccsReq($service, $params, '/CMD_SELECT_USERS', array('confirmed' => 'Confirm', 'delete' => 'yes'), 'delete');
	}

	public function __ava__suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, '/CMD_SELECT_USERS', array('suspend' => 'Suspend'), 'suspendreseller');
	}

	public function __ava__unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/

		return $this->sendMultiAccsReq($service, $params, '/CMD_SELECT_USERS', array('suspend' => 'Unsuspend'), 'unsuspendreseller');
	}

	public function isSuspendAcc($service, $params = array()){
		/*
			Проверяи чта акк из суспиндит
			Возвращает 1 - если да, -1 если нет, false - если ХЗ и -2 если такого вообще нет
		*/

		return false;
	}

	public function __ava__listAccs($service, $params = array()){
		/*
			Тупо список логинов без параметров
		*/

		return $this->sendListAccs($service, $params, '/CMD_API_SHOW_RESELLERS');
	}


	/********************************************************************************************************************************************************************

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		$data = parent::getInstallParams();

		$data[0]['dns'] = array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:personalnyed}', 'extra' => array('additional' => array('OFF' => '{Call:Lang:modules:bill_hosting:net}', 'TWO' => '{Call:Lang:modules:bill_hosting:dvaipdomenna}', 'THREE' => '{Call:Lang:modules:bill_hosting:triippoddnsi}')));
		$data[0]['ip'] = array('type' => 'select', 'text' => '{Call:Lang:modules:bill_hosting:domenip}', 'name' => 'ipstyle', 'mpkg' => 0, 'pkg_list' => 0, 'aacc' => 1, 'extra' => array('additional' => array('shared' => '{Call:Lang:modules:bill_hosting:obshchijserv}', 'sharedreseller' => '{Call:Lang:modules:bill_hosting:obshchijrese}', 'assign' => '{Call:Lang:modules:bill_hosting:naznachit}')));
		$data[0]['ips'] = array('type' => 'text', 'text' => '{Call:Lang:modules:bill_hosting:kolichestvoi}');

		$data[0]['userssh'] = array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:dostuppolzov}');
		$data[0]['oversell'] = array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:overselling}');
		$data[0]['serverip'] = array('type' => 'checkbox', 'ch' => 'ON', 'text' => '{Call:Lang:modules:bill_hosting:obshchijip}');

		unset(
			$data[0]['suspend_at_limit'],
			$data[0]['language'],
			$data[0]['skin']
		);

		return array($data[0], '{Call:Lang:modules:bill_hosting:directadminp}', 'bill_hosting');
	}
}

?>