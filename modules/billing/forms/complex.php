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


$matrix['text']['text'] = 'Имя комплекса';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = 'Вы не указали имя комплекса';

$matrix['name']['text'] = 'Идентификатор';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = 'Вы не указали идентификатор';
$matrix['name']['warn_function'] = 'regExp::ident';
if(!empty($modify)) $matrix['name']['disabled'] = true;

$matrix['sort']['text'] = '{Call:Lang:modules:billing:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = 'Доступен для заказа';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>