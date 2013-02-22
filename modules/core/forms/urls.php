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


$matrix['site']['text'] = '{Call:Lang:core:core:sajt}';
$matrix['site']['type'] = 'select';
$matrix['site']['warn'] = '{Call:Lang:core:core:neukazansajt}';
$matrix['site']['additional'] = $GLOBALS['Core']->DB->columnFetch(array('sites', 'name', 'id', "", "`default` DESC, `sort`"));

$matrix['rewrited']['text'] = '{Call:Lang:core:core:staryjpereza}';
$matrix['rewrited']['type'] = 'text';
$matrix['rewrited']['warn_pattern'] = '/[\S]*$/iUs';
$matrix['rewrited']['comment'] = '{Call:Lang:core:core:urlukazyvaet}';

$matrix['url']['text'] = '{Call:Lang:core:core:novyjurl}';
$matrix['url']['type'] = 'text';
$matrix['url']['warn_pattern'] = '/[\S]*$/iUs';
$matrix['url']['comment'] = '{Call:Lang:core:core:urlukazyvaet}';

?>