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


$GLOBALS['Core']->loadExtension('billing', 'installServiceObject');

class installModulesBill_lease extends installServiceObject implements InstallModuleInterface{

	public function Install(){
		/*
			Инсталляция пакета
		 */

		$this->createAllTables();
		$this->setAllDefaults($this->obj->values);
		$this->installExtension();
		$this->installService($this->obj->values['text_'.$this->ext], $this->prefix, 'month');
		return true;
	}

	public function prepareInstall(){
		return true;
	}

	public function checkInstall(){
		return true;
	}

	public function Uninstall(){
		$this->dropAllTables();
		$this->dropAllDefaults();
		$this->dropExtension();
		$this->dropService();

		return true;
	}

	public function checkUninstall(){
		return true;
	}

	public function Update($oldVersion, $newVersion){
		$v = $this->obj->values;
		$v['sites'] = $this->iObj->Core->getModuleSites($this->prefix);

		$this->updateAllTables();
		$this->updateAllDefaults($v);
		return true;
	}

	public function checkUpdate($oldVersion, $newVersion){
		return true;
	}
}

?>