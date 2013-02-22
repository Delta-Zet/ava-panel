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



class InstallPluginObject extends InstallObject{
	public $iObj;		//Инсталлируемый объект
	public $prefix;		//URL-имя инсталируемого объекта

	public final function __construct($DB, $obj, $prefix, $params = array()){		$this->DB = $DB;
		$this->obj = $obj;
		$this->prefix = $prefix;
		$this->params = $params;
		if(method_exists($this, '__init')) $this->__init();
	}

	public function Install(){
		return true;
	}

	public function prepareInstall(){
		return true;
	}

	public function checkInstall(){
		return true;
	}

	public function Uninstall(){
		return true;
	}

	public function checkUninstall(){
		return true;
	}

	public function Update(){
		return true;
	}

	public function checkUpdate(){
		return true;
	}
}

?>