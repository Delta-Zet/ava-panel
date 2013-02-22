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

$matrix['url']['text'] = '{Call:Lang:modules:cms:urlnaimenova1}';
$matrix['url']['type'] = 'text';
$matrix['url']['warn'] = '{Call:Lang:modules:cms:neukazanourl}';
$matrix['url']['warn_function'] = 'regexp::folder';
$matrix['url']['disabled'] = 1;

$matrix['parent']['text'] = '{Call:Lang:modules:cms:stranitsarod}';
$matrix['parent']['type'] = 'select';
$matrix['parent']['additional'] = $pages;

$matrix['version_name']['text'] = '{Call:Lang:modules:cms:imiaversiist}';
$matrix['version_name']['type'] = 'text';

$matrix['tags']['text'] = 'Теги';
$matrix['tags']['type'] = 'checkbox_array';
$matrix['tags']['additional'] = $tags;

$matrix['start']['text'] = '{Call:Lang:modules:cms:nachalopubli}';
$matrix['start']['type'] = 'calendar2';

$matrix['stop']['text'] = '{Call:Lang:modules:cms:konetspublik}';
$matrix['stop']['type'] = 'calendar2';

$matrix['sort']['text'] = '{Call:Lang:modules:cms:pozitsiiavso}';
$matrix['sort']['warn_function'] = 'regExp::digit';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = '{Call:Lang:modules:cms:stranitsados}';
$matrix['show']['type'] = 'select';
$matrix['show']['additional'] = array(
	'0' => '{Call:Lang:modules:cms:stranitsaned}',
	'1' => '{Call:Lang:modules:cms:dostupnaadmi}',
	'2' => '{Call:Lang:modules:cms:dostupnapolz}',
	'3' => '{Call:Lang:modules:cms:dostupnavsem}',
	'4' => '{Call:Lang:modules:cms:dostupnavsem1}',
//	'5' => 'Индивидуальные настройки доступа'
);

$matrix['version_on']['text'] = '{Call:Lang:modules:cms:ehtodejstvui}';
$matrix['version_on']['type'] = 'checkbox';

$matrix['templateCaptions']['text'] = '{Call:Lang:modules:cms:ispolzuemyes}';
$matrix['templateCaptions']['type'] = 'caption';

foreach($templates as $i => $e){
	if($e['pages']){
		$matrix['template_'.$i]['text'] = '{Call:Lang:modules:cms:dliashablona:'.Library::serialize(array($e['name'])).'}';
		$matrix['template_'.$i]['type'] = 'select';
		$matrix['template_'.$i]['additional'] = Library::array_merge(array('' => '{Call:Lang:modules:cms:nepokazyvatd}'), $e['pages']);
	}
}

?>