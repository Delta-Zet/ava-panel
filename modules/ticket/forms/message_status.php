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


$matrix['text']['text'] = '{Call:Lang:modules:ticket:imia}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:ticket:neukazanoimi}';

$matrix['name']['text'] = '{Call:Lang:modules:ticket:identifikato}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:ticket:neukazaniden}';
$matrix['name']['warn_function'] = 'regExp::ident';
if(!empty($modify)) $matrix['name']['disabled'] = 1;

$matrix['sort']['text'] = '{Call:Lang:modules:ticket:indekssortir}';
$matrix['sort']['type'] = 'text';

$matrix['use_support']['text'] = 'Статус может установить администратор';
$matrix['use_support']['type'] = 'checkbox';
$values['use_support'] = 1;

$matrix['use_user']['text'] = 'Статус может установить пользователь';
$matrix['use_user']['type'] = 'checkbox';

$matrix['superpriv']['text'] = 'Статус не может быть изменен';
$matrix['superpriv']['type'] = 'checkbox';

$matrix['auto_set_open']['text'] = 'Автоматически выставить при открытии тикета';
$matrix['auto_set_open']['type'] = 'checkbox';

$matrix['auto_set_show_user']['text'] = 'Автоматически выставить при просмотре пользователем';
$matrix['auto_set_show_user']['type'] = 'checkbox';

$matrix['auto_set_show_support']['text'] = 'Автоматически выставить при просмотре администратором';
$matrix['auto_set_show_support']['type'] = 'checkbox';

$matrix['auto_set_answer_user']['text'] = 'Автоматически выставить при ответе пользователя (если не выставлен принудительно)';
$matrix['auto_set_answer_user']['type'] = 'checkbox';

$matrix['auto_set_answer_support']['text'] = 'Автоматически выставить при ответе администратора (если не выставлен принудительно)';
$matrix['auto_set_answer_support']['type'] = 'checkbox';

$matrix['rights']['text'] = 'Если статус выставлен, пользователю запрещено';
$matrix['rights']['type'] = 'checkbox_array';
$matrix['rights']['additional'] = array(
	'show' => 'Просматривать тикет',
	'answer' => 'Отвечать на тикет'
);

?>