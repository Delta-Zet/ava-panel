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


class installTemplatesModulesPartnerDefault extends InstallTemplateObject implements InstallTemplateInterface{

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