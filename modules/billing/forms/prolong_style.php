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


$matrix[$prefix.'auto_prolong'.$id]['text'] = 'Продлять автоматически на';
$matrix[$prefix.'auto_prolong'.$id]['type'] = 'select';
$matrix[$prefix.'auto_prolong'.$id]['warn'] = '{Call:Lang:modules:billing:neukazansrok1}';
$matrix[$prefix.'auto_prolong'.$id]['additional'] = Library::array_merge(array(0 => 'Не продлять'), $pTerms);

if($fract){
	$matrix[$prefix.'auto_prolong_fract'.$id]['text'] = 'Дробить срок автопродления, если на счету недостаточно средств';
	$matrix[$prefix.'auto_prolong_fract'.$id]['type'] = 'checkbox';
}

?>