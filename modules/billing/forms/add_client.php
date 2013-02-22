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


require(_W.'forms/type_newlogin.php');
require(_W.'forms/type_neweml.php');
if(empty($modify)) require(_W.'forms/type_newpwd.php');
else $matrix['login']['disabled'] = 1;

$matrix['comment']['text'] = '{Call:Lang:modules:billing:kommentarija}';
$matrix['comment']['type'] = 'textarea';

$matrix['name']['text'] = '{Call:Lang:modules:billing:familiiaimia}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:neukazanoimi}';

require(_W.'forms/type_utc.php');

$text = '{Call:Lang:modules:billing:dataustanovk}';
$field = 'date';
require_once(_W.'forms/type_calendar2.php');
$values['date'] = time();

$matrix['group']['text'] = '{Call:Lang:modules:billing:gruppapolzov}';
$matrix['group']['type'] = 'select';
$matrix['group']['additional'] = $groups;

$matrix['loyal_level']['text'] = '{Call:Lang:modules:billing:urovenklient}';
$matrix['loyal_level']['type'] = 'select';
$matrix['loyal_level']['additional'] = $clientLevels;

?>