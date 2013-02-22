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


$matrix[$prefix.'pkg_list_value'.$postfix]['text'] = '{Call:Lang:modules:billing:znacheniepou}';
$matrix[$prefix.'pkg_list_value'.$postfix]['type'] = 'text';

$matrix[$prefix.'pkg_list_sort'.$postfix]['text'] = '{Call:Lang:modules:billing:parametrsort}';
$matrix[$prefix.'pkg_list_sort'.$postfix]['type'] = 'text';

$matrix[$prefix.'pkg_list_ind_pkg_value'.$postfix]['text'] = '{Call:Lang:modules:billing:ispolzovatzn}';
$matrix[$prefix.'pkg_list_ind_pkg_value'.$postfix]['type'] = 'select';
$matrix[$prefix.'pkg_list_ind_pkg_value'.$postfix]['additional'] = array(
	'' => '{Call:Lang:modules:billing:izadminki}',
	'default' => '{Call:Lang:modules:billing:vystavlennoe}',
	'ind' => '{Call:Lang:modules:billing:individualno}',
);

$matrix[$prefix.'pkg_list_group'.$postfix]['text'] = '{Call:Lang:modules:billing:gruppirovato}';
$matrix[$prefix.'pkg_list_group'.$postfix]['type'] = 'select';
$matrix[$prefix.'pkg_list_group'.$postfix]['additional'] = array(
	'' => '{Call:Lang:modules:billing:kakvnastrojk}',
	'group' => '{Call:Lang:modules:billing:gruppirovat}',
	'nogroup' => '{Call:Lang:modules:billing:negruppirova}',
);

?>