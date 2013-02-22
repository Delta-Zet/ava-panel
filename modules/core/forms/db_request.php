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


$matrix['sql']['type'] = 'textarea';
$matrix['sql']['text'] = '';
$matrix['sql']['warn'] = '{Call:Lang:core:core:pustojzapros}';
$matrix['sql']['template'] = 'big';

$matrix['strip']['type'] = 'checkbox';
$matrix['strip']['text'] = '{Call:Lang:core:core:obrezatslish}';
$values['strip'] = 1;

$matrix['db']['type'] = 'select';
$matrix['db']['text'] = '{Call:Lang:core:core:bazadannykh}';
$matrix['db']['additional'] = $db;

?>