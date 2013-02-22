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



class InstallObject extends objectInterface{
	public $DB;
	public $obj;
	public $params = array();

	protected function __ava__paramReplaces($params){
		/*
			   
		*/

		return $GLOBALS['Core']->paramReplaces($params, $this->obj);
	}
}

?>