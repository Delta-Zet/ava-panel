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



class gen_core extends ModuleInterface{
	private $aId;

	protected function __init(){
		if(!($this->aId = $this->Core->userIsAdmin()) && (!defined('IN_INSTALLATOR') || IN_INSTALLATOR < 1)){			throw new AVA_Access_Exception('{Call:Lang:core:core:vydolzhnybyt}');		}
	}

	protected function func_authAdminAsUser(){
		/*
			Форма авторизации админа как пользователя (любого)
		*/

		$this->setMeta('{Call:Lang:core:core:vojtikakpolz}');
		$fObj = $this->newForm('authAdminAsUser', 'authAdminAsUser2');
		$this->addFormBlock($fObj, 'auth_as_user');
		$this->setContent($this->getFormText($fObj));
	}

	protected function func_authAdminAsUser2(){
		/*
			Авторизует админа как юзера
		*/

		$this->setMeta('{Call:Lang:core:core:vojtikakpolz}');
		$uData = $this->DB->rowFetch(array('users', array('id', 'login'), "`id`='{$this->values['user']}' OR `login`='{$this->values['user']}'"));

		if(!$uData) $this->setError('user', '{Call:Lang:core:core:takogopolzov1}');
		else{
			if($aData = $this->DB->rowFetch(array('admins', array('id', 'login'), "`user_id`='{$uData['id']}'"))){
				$this->setError('user', '{Call:Lang:core:core:dannyjpolzov}');
			}
		}

		if(!$this->check()) return false;

		$this->Core->User->authById($uData['id']);
		if(!empty($this->values['redirect'])) $this->redirect2($this->values['redirect']);
		return $uData['id'];
	}
}

?>