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


$matrix['text']['text'] = '{Call:Lang:modules:billing:nazvanievali}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:billing:neukazanonaz1}';

$matrix['name']['text'] = '{Call:Lang:modules:billing:identifikato2}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:neukazaniden1}';
$matrix['name']['warn_pattern'] = '^[A-Za-z]{1,3}$';
$matrix['name']['comment'] = '{Call:Lang:modules:billing:vnimanieiden}';

$matrix['exchange']['text'] = '{Call:Lang:modules:billing:kursk:'.Library::serialize(array($currency)).'}';
$matrix['exchange']['comment'] = '{Call:Lang:modules:billing:zdessleduetu:'.Library::serialize(array($currency)).'}';
$matrix['exchange']['type'] = 'text';
$matrix['exchange']['warn'] = '{Call:Lang:modules:billing:neukazankurs}';

$matrix['coin']['text'] = 'Название разменной валюты';
$matrix['coin']['type'] = 'text';

$matrix['coincount']['text'] = 'Разменных едениц в одной базовой';
$matrix['coincount']['type'] = 'text';
$values['coincount'] = 100;

$matrix['sort']['text'] = '{Call:Lang:modules:billing:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

if(!empty($extra)){
	$matrix['name']['disabled'] = '1';
}

?>