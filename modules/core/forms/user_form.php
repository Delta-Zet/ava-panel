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


$matrix['in_reg']['text'] = '{Call:Lang:core:core:ispolzovatvf}';
$matrix['in_reg']['type'] = 'checkbox';

$matrix['in_account']['text'] = '{Call:Lang:core:core:ispolzovatvf1}';
$matrix['in_account']['type'] = 'checkbox';

$matrix['in_admin']['text'] = '{Call:Lang:core:core:ispolzovatvf2}';
$matrix['in_admin']['type'] = 'checkbox';

$matrix['form_types']['text'] = 'В каких типах анкет используется';
$matrix['form_types']['type'] = 'checkbox_array';
$matrix['form_types']['additional'] = Library::array_merge(array('@without' => 'Общая анкета'), $GLOBALS['Core']->getUserFormTypes());

?>