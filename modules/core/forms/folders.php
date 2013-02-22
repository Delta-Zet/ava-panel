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

$matrix['path']['text'] = '{Call:Lang:core:core:putkpapke}';
$matrix['path']['type'] = 'text';
$matrix['path']['warn'] = '{Call:Lang:core:core:neukazanput}';
$matrix['path']['comment'] = '{Call:Lang:core:core:putdopapkikh:'.Library::serialize(array(_W)).'}';

$matrix['main_standart']['text'] = '{Call:Lang:core:core:izobrazhenii}';
$matrix['main_standart']['type'] = 'select';
$matrix['main_standart']['additional'] = isset($imageStandarts) ? Library::array_merge(array('' => '{Call:Lang:core:core:net}'), $imageStandarts) : array();

$matrix['standarts']['text'] = '{Call:Lang:core:core:sozdavatkopi}';
$matrix['standarts']['type'] = 'checkbox_array';
$matrix['standarts']['additional'] = isset($imageStandarts) ? $imageStandarts : array();

$matrix['modules']['text'] = '{Call:Lang:core:core:modulicmskot}';
$matrix['modules']['type'] = 'checkbox_array';
$matrix['modules']['additional'] = isset($modules) ? $modules : array();

$matrix['sort']['text'] = '{Call:Lang:core:core:indekssortir}';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = '{Call:Lang:core:core:papkadostupn}';
$matrix['show']['type'] = 'checkbox';

?>