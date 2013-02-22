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



interface InstallPluginInterface{
	public static function Install($obj, $dbId, $prefix, $name = '');
	public static function prepareInstall($obj, $dbId, $prefix);
	public static function checkInstall($obj);

	public static function Uninstall($obj, $dbId, $prefix);
	public static function checkUninstall($obj, $dbId, $prefix);

	public static function Update($obj, $dbId, $prefix);
	public static function checkUpdate($obj, $dbId, $prefix);

	public static function getName();
	public static function getTextName();
	public static function getVersion();
	public static function getRequirements();
	public static function getParams();

}

?>