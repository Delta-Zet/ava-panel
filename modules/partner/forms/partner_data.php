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


$matrix['login']['text'] = '{Call:Lang:modules:partner:psevdonim}';
$matrix['login']['type'] = 'text';
$matrix['login']['warn'] = '{Call:Lang:modules:partner:neukazanpsev1}';
$matrix['login']['disabled'] = 1;

$matrix['status']['text'] = '{Call:Lang:modules:partner:sostoianie}';
$matrix['status']['type'] = 'select';
$matrix['status']['additional'] = array(
	'0' => '{Call:Lang:modules:partner:ozhidaetrazr}',
	'1' => '{Call:Lang:modules:partner:rabotaet}',
	'-1' => '{Call:Lang:modules:partner:zablokirovan}'
);

$matrix['grp']['text'] = '{Call:Lang:modules:partner:gruppa}';
$matrix['grp']['type'] = 'select';
$matrix['grp']['additional'] = $partnerGroups;

?>