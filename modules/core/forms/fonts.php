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


$matrix['name']['text'] = '{Call:Lang:core:core:nazvanie}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:core:core:neukazanonaz}';
$matrix['name']['comment'] = '{Call:Lang:core:core:liuboeponiat1}';

$matrix['file']['text'] = '{Call:Lang:core:core:fajlshriftat}';
$matrix['file']['type'] = 'file';
$matrix['file']['warn'] = '{Call:Lang:core:core:netfajlashri}';
$matrix['file']['additional'] = array(
	'allow_ext' => array('.ttf', '.ft', '.ps'),
	'dstFolder' => $GLOBALS['Core']->getParam('fontsFolder')
);

$matrix['sort']['text'] = '{Call:Lang:core:core:indekssortir}';
$matrix['sort']['type'] = 'text';

?>