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
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = 'Вы не указали имя';

$matrix['name']['text'] = 'Идентификатор';
$matrix['name']['comment'] = 'Только латинские буквы, цифры и знак подчеркивания';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = 'Вы не указали идентификатор';
$matrix['name']['warn_function'] = 'regExp::alNum';
if(!empty($modify)) $matrix['name']['disabled'] = true;

$matrix['url']['text'] = 'URL RSS-канала';
$matrix['url']['type'] = 'text';
$matrix['url']['warn'] = 'Вы не указали URL RSS-канала';

$matrix['format']['text'] = 'Формат хранения новости';
$matrix['format']['type'] = 'select';
$matrix['format']['additional'] = array(
	's' => 'Краткий',
	'f' => 'Полный',
);

$matrix['parent_page']['text'] = 'Страница-родитель сохраняемой новости';
$matrix['parent_page']['type'] = 'select';
$matrix['parent_page']['additional'] = isset($parentPages) ? $parentPages : array();

$matrix['update_interval']['text'] = 'Минимальный интервал обновления, секунд';
$matrix['update_interval']['type'] = 'text';
$matrix['update_interval']['warn'] = 'Вы не указали интервал обновления';
$matrix['update_interval']['warn_function'] = 'regExp::digit';

$matrix['sort']['text'] = 'Позиция в сортировке';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = 'Включено';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>