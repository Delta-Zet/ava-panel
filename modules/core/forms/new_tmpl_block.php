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


$matrix['descript']['text'] = '{Call:Lang:core:core:imiabloka}';
$matrix['descript']['type'] = 'text';
$matrix['descript']['warn'] = '{Call:Lang:core:core:vydolzhnyuka7}';

$matrix['name']['text'] = '{Call:Lang:core:core:identifikato3}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:core:core:vydolzhnyuka8}';
$matrix['name']['warn_function'] = 'regExp::ident';
$matrix['name']['comment'] = '{Call:Lang:core:core:identifikato4}';

$matrix['template']['text'] = '{Call:Lang:core:core:imiashablona1}';
$matrix['template']['type'] = 'text';
$matrix['template']['warn_function'] = 'regExp::ident';
$matrix['template']['comment'] = '{Call:Lang:core:core:mozhetsoderz}';

$matrix['type']['text'] = '{Call:Lang:core:core:tipzapisi}';
$matrix['type']['type'] = 'select';
$matrix['type']['additional'] = array(
	'cover' => '{Call:Lang:core:core:shablonobolo}',
	'entry' => '{Call:Lang:core:core:shablonzapis}',
	'additentry' => '{Call:Lang:core:core:shablonsubza}',
	'head' => '{Call:Lang:core:core:shablonshapk}',
	'extra' => '{Call:Lang:core:core:dopolnitelna}',
);

$matrix['body']['text'] = '{Call:Lang:core:core:tekstshablon}';
$matrix['body']['type'] = 'textarea';
$matrix['body']['warn'] = '{Call:Lang:core:core:vydolzhnyuka9}';

if(!empty($modify)){
	$matrix['body']['additional_style'] = 'style="width: 827px; height: 500px;"';
	$matrix['body']['additional_entry_style'] = 'style="clear: both;"';
	$matrix['name']['disabled'] = 1;
}

?>