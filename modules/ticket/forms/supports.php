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


$matrix['admin_id']['text'] = '{Call:Lang:modules:ticket:administrato}';
$matrix['admin_id']['type'] = 'select';
$matrix['admin_id']['additional'] = $admins;
$matrix['admin_id']['warn'] = '{Call:Lang:modules:ticket:neukazanadmi}';

$matrix['name']['text'] = '{Call:Lang:modules:ticket:otobrazhaemo}';
$matrix['name']['comment'] = '{Call:Lang:modules:ticket:esliostavitp}';
$matrix['name']['type'] = 'text';

$matrix['departments']['text'] = '{Call:Lang:modules:ticket:obsluzhivaem}';
$matrix['departments']['type'] = 'checkbox_array';
$matrix['departments']['warn'] = '{Call:Lang:modules:ticket:neukazanyobs}';
$matrix['departments']['additional'] = $departments;

$matrix['status']['text'] = '{Call:Lang:modules:ticket:status}';
$matrix['status']['type'] = 'select';
$matrix['status']['additional'] = array(
	'1' => '{Call:Lang:modules:ticket:rabotaet}',
	'0' => '{Call:Lang:modules:ticket:nerabotaet}',
	'2' => '{Call:Lang:modules:ticket:votpuske}'
);

$matrix['auto_status_change']['text'] = '{Call:Lang:modules:ticket:sapportmozhe}';
$matrix['auto_status_change']['type'] = 'checkbox';

$matrix['sort']['text'] = '{Call:Lang:modules:ticket:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

?>