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


$matrix['name']['text'] = '{Call:Lang:core:core:identifikato2}';
$matrix['name']['type'] = 'text';
$matrix['name']['comment'] = '{Call:Lang:core:core:ehtotekhnich1}';
$matrix['name']['warn'] = '{Call:Lang:core:core:vydolzhnyuka5}';
$matrix['name']['warn_function'] = 'regExp::Ident';
if(!empty($modify)) $matrix['name']['disabled'] = true;

$matrix['text']['text'] = '{Call:Lang:core:core:imia}';
$matrix['text']['type'] = 'text';
$matrix['text']['comment'] = '{Call:Lang:core:core:podnimiazykb}';
$matrix['text']['warn'] = '{Call:Lang:core:core:vydolzhnyuka6}';

$matrix['sort']['text'] = '{Call:Lang:core:core:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

?>