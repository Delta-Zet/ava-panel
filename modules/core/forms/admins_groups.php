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


$matrix['name']['text'] = '{Call:Lang:core:core:imiagruppydl}';
$matrix['name']['warn'] = '{Call:Lang:core:core:neukazanoimi4}';
$matrix['name']['type'] = 'text';
$matrix['name']['comment'] = '{Call:Lang:core:core:gruppyadmini1}';

$matrix['ip_access_type']['text'] = '';
$matrix['ip_access_type']['type'] = 'radio';
$matrix['ip_access_type']['warn'] = '{Call:Lang:core:core:neukazanspos3}';
$matrix['ip_access_type']['additional'] = array(
	'allow' => '{Call:Lang:core:core:dostuprazres}',
	'disallow' => '{Call:Lang:core:core:dostupzapres}'
);
$values['ip_access_type'] = 'allow';

$matrix['show']['text'] = '{Call:Lang:core:core:administrato3}';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>