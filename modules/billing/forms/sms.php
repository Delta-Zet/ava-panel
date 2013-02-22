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


$matrix['name']['text'] = '{Call:Lang:modules:billing:identifikato8}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:neukazaniden1}';
$matrix['name']['warn_pattern'] = '^[A-Za-z][\w\-\.]+$';
$matrix['name']['comment'] = '{Call:Lang:modules:billing:identifikato9}';
if(!empty($extra)) $matrix['name']['disabled'] = '1';

$matrix['text']['text'] = '{Call:Lang:modules:billing:nazvaniespos}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:billing:neukazanonaz2}';

$matrix['extension']['text'] = '{Call:Lang:modules:billing:modulrasshir}';
$matrix['extension']['type'] = 'select';
$matrix['extension']['comment'] = '{Call:Lang:modules:billing:dliaavtomati}';
$matrix['extension']['additional'] = $extensions;

$matrix['sort']['text'] = '{Call:Lang:modules:billing:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = '{Call:Lang:modules:billing:sposoboplaty2}';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>