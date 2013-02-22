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


$matrix['text']['text'] = '{Call:Lang:modules:billing:imiagruppy}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:billing:vyneukazalii10}';

$matrix['name']['text'] = '{Call:Lang:modules:billing:identifikato6}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:vyneukazalii11}';
$matrix['name']['comment'] = '{Call:Lang:modules:billing:identifikato7}';
$matrix['name']['warn_function'] = 'regExp::ident';

$matrix['pkg_table_mode']['text'] = '{Call:Lang:modules:billing:spiskitarifo}';
$matrix['pkg_table_mode']['type'] = 'select';
$matrix['pkg_table_mode']['additional'] = array('' => '{Call:Lang:modules:billing:kakvosnovnoj}', 'h' => '{Call:Lang:modules:billing:gorizontalno}', 'v' => '{Call:Lang:modules:billing:vertikalno}');

$matrix['sort']['text'] = '{Call:Lang:modules:billing:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['hide_if_none']['text'] = '{Call:Lang:modules:billing:skryvatparam}';
$matrix['hide_if_none']['type'] = 'checkbox';
$matrix['hide_if_none']['comment'] = '{Call:Lang:modules:billing:pripostroeni}';

$matrix['compact_if_alike']['text'] = '{Call:Lang:modules:billing:obediniatpar}';
$matrix['compact_if_alike']['type'] = 'checkbox';
$matrix['compact_if_alike']['comment'] = '{Call:Lang:modules:billing:pripostroeni1}';

$matrix['main']['text'] = '{Call:Lang:modules:billing:gruppaiavlia}';
$matrix['main']['type'] = 'checkbox';
$matrix['main']['comment'] = '{Call:Lang:modules:billing:esliotmetite}';

if(!empty($extra)){
	$matrix['name']['disabled'] = 1;
}

?>