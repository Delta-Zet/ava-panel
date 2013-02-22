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

$matrix['comment']['text'] = '{Call:Lang:modules:partner:kommentarija}';
$matrix['comment']['type'] = 'textarea';

$matrix['name']['text'] = '{Call:Lang:modules:partner:familiiaimia}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:partner:neukazanoimi}';

require(_W.'forms/type_utc.php');

$text = '{Call:Lang:modules:partner:dataustanovk}';
$field = 'date';
require_once(_W.'forms/type_calendar2.php');
$values['date'] = time();

$matrix['refered_by']['text'] = '{Call:Lang:modules:partner:privedenpore}';
$matrix['refered_by']['type'] = 'text';
$matrix['refered_by']['comment'] = '{Call:Lang:modules:partner:ukazhitepsev}';

$matrix['group']['text'] = '{Call:Lang:modules:partner:gruppapolzov}';
$matrix['group']['type'] = 'select';
$matrix['group']['additional'] = $groups;

$matrix['grp']['text'] = '{Call:Lang:modules:partner:gruppapartne}';
$matrix['grp']['type'] = 'select';
$matrix['grp']['additional'] = $partnerGroups;

$matrix['status']['text'] = '{Call:Lang:modules:partner:sostoianie}';
$matrix['status']['type'] = 'select';
$matrix['status']['additional'] = array(
	'0' => '{Call:Lang:modules:partner:ozhidaetrazr}',
	'1' => '{Call:Lang:modules:partner:rabotaet}',
	'-1' => '{Call:Lang:modules:partner:zablokirovan}'
);
$values['status'] = 1;

?>