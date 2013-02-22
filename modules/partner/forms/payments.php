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


$matrix['name']['text'] = '{Call:Lang:modules:partner:identifikato2}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:partner:neukazaniden}';
$matrix['name']['warn_pattern'] = '^[A-Za-z][\w\-\.]+$';
$matrix['name']['comment'] = '{Call:Lang:modules:partner:identifikato3}';

$matrix['text']['text'] = '{Call:Lang:modules:partner:nazvaniespos}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:partner:neukazanonaz1}';

$matrix['extension']['text'] = '{Call:Lang:modules:partner:modulrasshir}';
$matrix['extension']['type'] = 'select';
$matrix['extension']['comment'] = '{Call:Lang:modules:partner:dliaavtomati}';
$matrix['extension']['additional'] = $extensions;

$matrix['currency']['text'] = '{Call:Lang:modules:partner:valiutapriem}';
$matrix['currency']['type'] = 'select';
$matrix['currency']['additional'] = $currencyList;

if(!empty($extra)){
	$matrix['comment']['text'] = '{Call:Lang:modules:partner:kommentarijk}';
	$matrix['comment']['comment'] = '{Call:Lang:modules:partner:ehtottekstbu}';
	$matrix['comment']['type'] = 'textarea';

	$matrix['show']['text'] = '{Call:Lang:modules:partner:sposoboplaty1}';
	$matrix['show']['type'] = 'checkbox';

	$matrix['sort']['text'] = '{Call:Lang:modules:partner:pozitsiiavso}';
	$matrix['sort']['type'] = 'text';

	$matrix['name']['disabled'] = '1';
}

?>