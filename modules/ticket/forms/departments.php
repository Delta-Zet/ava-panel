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


$matrix['text']['text'] = '{Call:Lang:modules:ticket:nazvanierazd}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:ticket:neukazanonaz}';

$matrix['name']['text'] = '{Call:Lang:modules:ticket:identifikato}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:ticket:neukazaniden}';
$matrix['name']['warn_function'] = 'regExp::ident';
if(!empty($modify)) $matrix['name']['disabled'] = 1;

$matrix['access_type']['text'] = '{Call:Lang:modules:ticket:dostuppolzov}';
$matrix['access_type']['type'] = 'select';
$matrix['access_type']['additional'] = array(
	'0' => '{Call:Lang:modules:ticket:vse}',
	'1' => '{Call:Lang:modules:ticket:zaregistriro}'
);

$matrix['transmit_type']['text'] = '    ';
$matrix['transmit_type']['type'] = 'select';
$matrix['transmit_type']['additional'] = array(
	'0' => '{Call:Lang:modules:ticket:posleotvetas}',
	'1' => '{Call:Lang:modules:ticket:natiketmozhe}'
);

$matrix['sort']['text'] = '{Call:Lang:modules:ticket:indekssortir}';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = '{Call:Lang:modules:ticket:otobrazhaets}';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>