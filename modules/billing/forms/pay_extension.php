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


$matrix['name']['text'] = '{Call:Lang:modules:billing:nazvanierass}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:neukazanonaz}';

if(empty($modify)){
	require(_W.'modules/billing/forms/connection_extension_file.php');

	if($billMods){
		$matrix['bill_mods']['text'] = '{Call:Lang:modules:billing:drugiemoduli}';
		$matrix['bill_mods']['type'] = 'checkbox_array';
		$matrix['bill_mods']['additional'] = $billMods;
	}
}

?>