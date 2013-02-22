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


class serviceExtensionsObject extends ModuleInterface{

	/*

	//Используемые только для Service, без расширения на сервере
	public function setServiceMatrix($obj, $service, $params = array()){}
	public function setPkgsListEntries($obj, $service, $params = array()){}

	public function connect($obj, $params = array()){}

	//Управление пакетами
	public function addPkg($obj, $service, $params = array()){}
	public function delPkg($obj, $service, $params = array()){}

	//Управление аккаунтами
	public function addAcc($obj, $service, $params = array()){}
	public function modifyAcc($obj, $service, $params = array()){}
	public function prolongAcc($obj, $service, $params = array()){}
	public function delAcc($obj, $service, $params = array()){}
	public function suspendAcc($obj, $service, $params = array()){}
	public function unsuspendAcc($obj, $service, $params = array()){}
	public function isSuspendAcc($obj, $service, $params = array()){}
	public function listAccs($obj, $service, $params = array()){}

	//Установка матриц В params - fObj, pkg и extension
	public function setAddPkgMatrix($obj, $service, $params = array()){}
	public function checkAddPkgMatrix($obj, $service, $params = array()){}
	public function setDelPkgMatrix($obj, $service, $params = array()){}
	public function checkDelPkgMatrix($obj, $service, $params = array()){}

	public function setAddAccMatrix($obj, $service, $params = array()){}
	public function checkAddAccMatrix($obj, $service, $params = array()){}
	public function setModifyAccMatrix($obj, $service, $params = array()){}
	public function checkModifyAccMatrix($obj, $service, $params = array()){}
	public function setProlongAccMatrix($obj, $service, $params = array()){}
	public function checkProlongAccMatrix($obj, $service, $params = array()){}
	public function setDelAccMatrix($obj, $service, $params = array()){}
	public function checkDelAccMatrix($obj, $service, $params = array()){}
	public function setSuspendAccMatrix($obj, $service, $params = array()){}
	public function checkSuspendAccMatrix($obj, $service, $params = array()){}
	public function setUnsuspendAccMatrix($obj, $service, $params = array()){}
	public function checkUnsuspendAccMatrix($obj, $service, $params = array()){}
	public function setIsSuspendAccMatrix($obj, $service, $params = array()){}
	public function checkIsSuspendAccMatrix($obj, $service, $params = array()){}
	public function setListAccsMatrix($obj, $service, $params = array()){}
	public function checkListAccsMatrix($obj, $service, $params = array()){}

	//Используемые в разделе пользователя
	public function setAccOrderMatrix($obj, $service, $params = array()){}
	public function checkAccOrderMatrix($obj, $service, $params = array()){}
	public function setAccConstructorMatrix($obj, $service, $params = array()){}
	public function checkAccConstructorMatrix($obj, $service, $params = array()){}
	public function setAccUserModifyMatrix($obj, $service, $params = array()){}
	public function checkAccUserModifyMatrix($obj, $service, $params = array()){}
	public function setAccUserProlongMatrix($obj, $service, $params = array()){}
	public function checkAccUserProlongMatrix($obj, $service, $params = array()){}
	public function setAccUserDelMatrix($obj, $service, $params = array()){}
	public function checkAccUserDelMatrix($obj, $service, $params = array()){}
	public function setAccUserSuspendMatrix($obj, $service, $params = array()){}
	public function checkAccUserSuspendMatrix($obj, $service, $params = array()){}
	public function setAccUserUnsuspendMatrix($obj, $service, $params = array()){}
	public function checkAccUserUnsuspendMatrix($obj, $service, $params = array()){}

	//Параметры возвращаемые для установки в БД. Функции для Acc устанавливают массив данных mailSend, который в БД не вносится, а отправляется на e-mail
	public function getServiceParams($obj, $service, $params = array()){}

	public function getConnectParams($obj, $service, $params = array()){}
	public function getAddPkgParams($obj, $service, $params = array()){}
	public function getDelPkgParams($obj, $service, $params = array()){}

	public function getAddAccParams($obj, $service, $params = array()){}
	public function getModifyAccParams($obj, $service, $params = array()){}
	public function getProlongAccParams($obj, $service, $params = array()){}
	public function getDelAccParams($obj, $service, $params = array()){}
	public function getSuspendAccParams($obj, $service, $params = array()){}
	public function getUnsuspendAccParams($obj, $service, $params = array()){}

	//Используемые в разделе пользователя
	public function getAccOrderParams($obj, $service, $params = array()){}
	public function getAccConstructorParams($obj, $service, $params = array()){}
	public function getAccUserModifyParams($obj, $service, $params = array()){}
	public function getAccUserProlongParams($obj, $service, $params = array()){}
	public function getAccUserDelParams($obj, $service, $params = array()){}
	public function getAccUserSuspendParams($obj, $service, $params = array()){}
	public function getAccUserUnsuspendParams($obj, $service, $params = array()){}

	*/

	//Установка матриц
	public function __ava__setConnectMatrix($obj, $params = array()){
		if(!$params['values']['extension']) return true;
		$obj->loadServerConnect(0, $this->modName, $params['values']['extension'], $params['values'], $this);
		return $this->callServerExtension(0, 'setConnectMatrix', $obj, '', $params, array('type' => 'connections', 'id' => $params['values']['id']));
	}

	//Проверка соединения
	public function connect($obj, $params = array()){
		if(!$obj->values['extension']) return true;
		$obj->loadServerConnect(0, $this->modName, $obj->values['extension'], $obj->values, $this);
		return $this->callServerExtension(0, 'connect', $obj, '', $params, array('type' => 'connections', 'id' => $obj->values['id']));
	}

	public function delService($service){
		return true;
	}



	/********************************************************************************************************************************************************************

																		Дополнительные пораметры

	*********************************************************************************************************************************************************************/

	public function __ava__callServerExtension($serverId, $func, gen_billing $obj, $service, $params = array(), $saveParams = array()){
		if(empty($obj->connections[$serverId])){
			if(!$serverId) return false;
			$obj->loadServerConnectById($serverId, $this->modName, $this);
		}

		if(!method_exists($obj->connections[$serverId], $func) && !method_exists($obj->connections[$serverId], '__ava__'.$func)) return true;
		if(empty($saveParams['type'])) $saveParams['type'] = $obj->getFunc();
		if(empty($saveParams['id'])) $saveParams['id'] = !empty($obj->values['modify']) ? $obj->values['modify'] : empty($obj->values['id']) ? 0 : $obj->values['id'];

		$return = false;
		$return = $obj->connections[$serverId]->$func($service, $params);

		if($obj->connections[$serverId]->result || $obj->connections[$serverId]->code || $obj->connections[$serverId]->description)
			$obj->connections[$serverId]->saveResults($obj->DB, $saveParams['type'], $saveParams['id']);

		return $return;
	}
}

?>