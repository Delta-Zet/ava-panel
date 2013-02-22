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



$GLOBALS['Core']->loadExtension('bill_hosting', 'servConnectPlesk');

class servConnectPleskReseller extends servConnectPlesk{


	/********************************************************************************************************************************************************************

																				Соединение

	*********************************************************************************************************************************************************************/

	public function __ava__connect($service = '', $params = array()){
		/*
			Проверяется возможность соединения вообще
		*/

		if(is_array($this->getTemplates('reseller-template'))) return true;
		return false;
	}


	/********************************************************************************************************************************************************************

																		Работа с пакетами

	*********************************************************************************************************************************************************************/

	public function __ava__setAddPkgMatrix($service, $params = array()){		$this->obj->addFormBlock($params['fObj'], $this->getAddPkgMatrix($service, $params), array(), array(), 'block2');
		return true;
	}

	//Управление пакетами
	public function __ava__addPkg($service, $params = array()){
		/*
			Добавляет пакет
		*/

		return $this->sendAddPkg($service, $params, 'reseller-template');
	}

	public function __ava__delPkg($service, $params = array()){
		/*
			Удаление пакетов
			В передаваемом obj должен быть установлен список delPkg. Все они будут удалены
		*/

		return $this->sendDelPkg($service, $params, 'reseller-template');
	}


	/********************************************************************************************************************************************************************

																		Работа с аккаунтами

	*********************************************************************************************************************************************************************/

	public function addAcc($service, $params = array()){
		/*
			Создает аккаунт пользовотеля
		*/

	}

	public function modifyAcc($service, $params = array()){
		/*
			Изминяит аккаунт пользовотиля
		*/
	}

	public function prolongAcc($service, $params = array()){
		/*
			Продливаит оккаунт пользоватиля. В данном случии нахуй не нужна
		*/
	}

	public function delAcc($service, $params = array()){
		/*
			Удоляит аккаунт пользоватиля
		*/
	}

	public function suspendAcc($service, $params = array()){
		/*
			Суспиндит акк
		*/
	}

	public function unsuspendAcc($service, $params = array()){
		/*
			Рассуспиндит акк
		*/
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

																		Инатсллятор

	*********************************************************************************************************************************************************************/

	public static function getInstallParams(){
		$data = parent::getInstallParams();
		$data[0]['create_clients'] = array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:razreshenoso}');
		$data[0]['allow_oversell'] = array('type' => 'checkbox', 'ch' => 'true', 'noch' => 'false', 'text' => '{Call:Lang:modules:bill_hosting:overselling}', 'name' => 'oversell');
		return array($data[0], '{Call:Lang:modules:bill_hosting:pleskpaketyr}', 'bill_hosting');
	}
}

?>