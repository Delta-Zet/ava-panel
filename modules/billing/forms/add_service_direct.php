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


$matrix['client_id']['text'] = '{Call:Lang:modules:billing:idpolzovatel}';
$matrix['client_id']['type'] = 'text';
$matrix['client_id']['warn'] = '{Call:Lang:modules:billing:neukazanidpo}';

$matrix['pkg']['text'] = '{Call:Lang:modules:billing:tarifnyjplan}';
$matrix['pkg']['type'] = 'select';
$matrix['pkg']['additional'] = $packages;

?>