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


$matrix['text']['text'] = 'Тег';
$matrix['text']['type'] = 'text';
$matrix['text']['comment'] = 'В теге не допускается наличие запятых';
$matrix['text']['warn'] = 'Вы не указали тег';
$matrix['text']['warn_pattern'] = '|[^\,]|';

$matrix['name']['text'] = 'URL-имя';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = 'Вы не указали URL-имя';
$matrix['name']['warn_function'] = 'regExp::ident';
if(!empty($modify)) $matrix['name']['disabled'] = 1;

$matrix['sort']['text'] = '{Call:Lang:modules:cms:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = 'Тег используется';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>