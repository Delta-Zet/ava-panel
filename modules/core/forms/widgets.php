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
$matrix['text']['warn'] = 'Не указано имя';

$matrix['name']['text'] = 'Идентификатор';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = 'Не указан идентификатор';

$matrix['sort']['text'] = 'Индекс сортировки';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = 'Блок отображается';
$matrix['show']['type'] = 'checkbox';

$textField = 'body';
require(_W.'forms/widget_select.php');

$matrix['body']['text'] = 'Содержимое блока';
$matrix['body']['type'] = 'textarea';

if(empty($modify)){
	$matrix['widget']['additional_style'] = '';
}
else{
	$matrix['body']['template'] = 'content';
}

?>