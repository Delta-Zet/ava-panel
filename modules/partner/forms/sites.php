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


$matrix['name']['text'] = '{Call:Lang:modules:partner:imiasajta}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:partner:neukazanoimi1}';

$matrix['url']['text'] = 'URL';
$matrix['url']['type'] = 'text';
$matrix['url']['warn'] = '{Call:Lang:modules:partner:neukazanurl}';
$values['url'] = 'http://';

if(!empty($inAdmin)){
	$matrix['partner_id']['text'] = '{Call:Lang:modules:partner:psevdonimpar}';
	$matrix['partner_id']['type'] = 'text';
	$matrix['partner_id']['warn'] = '{Call:Lang:modules:partner:neukazanpsev}';

	$matrix['grp']['text'] = '{Call:Lang:modules:partner:gruppa}';
	$matrix['grp']['type'] = 'select';
	$matrix['grp']['additional'] = $groups;

	$matrix['status']['text'] = '{Call:Lang:modules:partner:status}';
	$matrix['status']['type'] = 'select';
	$matrix['status']['additional'] = array(
		'0' => '{Call:Lang:modules:partner:ozhidaetprov}',
		'1' => '{Call:Lang:modules:partner:proverenrabo}',
		'-1' => '{Call:Lang:modules:partner:zablokirovan1}'
	);

	$matrix['sort']['text'] = '{Call:Lang:modules:partner:pozitsiiavso}';
	$matrix['sort']['type'] = 'text';
}

?>