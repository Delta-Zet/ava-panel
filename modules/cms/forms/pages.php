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


$matrix['name']['text'] = '{Call:Lang:modules:cms:nazvanietolk}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:cms:neukazanonaz}';

$matrix['url']['text'] = '{Call:Lang:modules:cms:urlnaimenova}';
$matrix['url']['type'] = 'text';
$matrix['url']['warn'] = '{Call:Lang:modules:cms:neukazanourl}';
$matrix['url']['warn_function'] = 'regexp::folder';
$matrix['url']['comment'] = '{Call:Lang:modules:cms:identifikato1}';

$matrix['parent']['text'] = '{Call:Lang:modules:cms:stranitsarod}';
$matrix['parent']['type'] = 'select';
$matrix['parent']['additional'] = $pages;

$matrix['page_template']['text'] = 'Заполнить по шаблону';
$matrix['page_template']['type'] = 'select';
$matrix['page_template']['additional'] = Library::array_merge(array('' => 'Не заполнять'), $pageTemplates);

?>