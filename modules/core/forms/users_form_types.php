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


$matrix['text']['text'] = 'Имя';
$matrix['text']['warn'] = 'Не указано имя';
$matrix['text']['type'] = 'text';

$matrix['name']['text'] = 'Идентификатор';
$matrix['name']['warn'] = 'Не указан идентификатор';
$matrix['name']['type'] = 'text';

$matrix['auto_reg_group']['text'] = 'Автоматически присваивать группу';
$matrix['auto_reg_group']['type'] = 'select';
$matrix['auto_reg_group']['additional'] = $GLOBALS['Core']->getUserGroups();

$matrix['sort']['text'] = 'Позиция в сортировке';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = 'Используется';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>