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


$matrix['name']['text'] = 'Название расширения';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = 'Не указано название расширения';

if(empty($modify)){
	require(_W.'forms/type_extension_file.php');

	if($mods){
		$matrix['bill_mods']['text'] = 'Другие модули в которые устанавливается расширение';
		$matrix['bill_mods']['type'] = 'checkbox_array';
		$matrix['bill_mods']['additional'] = $mods;
	}
}

$matrix['sort']['text'] = 'Позиция в сортировке';
$matrix['sort']['type'] = 'text';

?>