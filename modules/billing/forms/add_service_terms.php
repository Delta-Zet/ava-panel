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


$field = 'date'.$id;
$text = '{Call:Lang:modules:billing:zakazana}';
require(_W.'forms/type_calendar2.php');
$values['date'.$id] = time();

$field = 'created'.$id;
$text = '{Call:Lang:modules:billing:sozdana}';
require(_W.'forms/type_calendar2.php');
$values['created'.$id] = time();

if($type != 'onetime'){
	$field = 'paid_to'.$id;
	$text = '{Call:Lang:modules:billing:oplachenado}';
	require(_W.'forms/type_calendar2.php');
}

?>