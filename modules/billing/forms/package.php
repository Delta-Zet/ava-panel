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


$matrix['text']['text'] = '{Call:Lang:modules:billing:imiatarifana}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:billing:vyneukazalii7}';
$matrix['text']['comment'] = '{Call:Lang:modules:billing:ehtoimiabude1}';

$matrix['server_name']['text'] = '{Call:Lang:modules:billing:imiatarifana1}';
$matrix['server_name']['type'] = 'text';
$matrix['server_name']['warn'] = '{Call:Lang:modules:billing:vyneukazalii8}';
$matrix['server_name']['comment'] = '{Call:Lang:modules:billing:zdessleduetu1}';

$matrix['name']['text'] = '{Call:Lang:modules:billing:identifikato4}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:billing:vyneukazalii9}';
$matrix['name']['comment'] = '{Call:Lang:modules:billing:identifikato5}';
$matrix['name']['warn_function'] = 'regExp::ident';
if(!empty($extra)) $matrix['name']['disabled'] = 1;

$matrix['sort']['text'] = '{Call:Lang:modules:billing:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

if(!empty($extra)){
	$matrix['rights']['text'] = '{Call:Lang:modules:billing:razresheno}';
	$matrix['rights']['type'] = 'checkbox_array';

	if($type != 'onetime'){
		$matrix['rights']['additional'] = array(
			'new' => '{Call:Lang:modules:billing:zakaznovykha}',
			'prolong' => '{Call:Lang:modules:billing:prodlenieakk}',
			'modify' => '{Call:Lang:modules:billing:svobodnaiamo}',
			'changeGrp' => '{Call:Lang:modules:billing:smenatarifas}',
			'changeSrv' => '{Call:Lang:modules:billing:smenatarifas1}',
			'changeDn' => '{Call:Lang:modules:billing:smenatarifas2}',
			'changeUp' => '{Call:Lang:modules:billing:smenatarifas3}',
			'pause' => '{Call:Lang:modules:billing:ustanovkaakk}',
			'del' => '{Call:Lang:modules:billing:udalenieakka}'
		);
	}
	else{
		$matrix['rights']['additional'] = array(
			'new' => '{Call:Lang:modules:billing:zakaznovykha}',
			'modify' => '{Call:Lang:modules:billing:svobodnaiamo}',
		);
	}
}

$matrix['server']['text'] = '{Call:Lang:modules:billing:soedineniedl}';
$matrix['server']['type'] = 'select';
$matrix['server']['additional'] = $connections;
$matrix['server']['comment'] = '{Call:Lang:modules:billing:vudalennojpa}';

if(!empty($extra)){
	$matrix['create_on_server']['text'] = '';
	$matrix['create_on_server']['type'] = 'checkbox_array';
	$matrix['create_on_server']['additional'] = array('default' => '{Call:Lang:modules:billing:sozdatnaudal}');
	$matrix['create_on_server']['value'] = array('default' => '1');
}

$matrix['main_group']['text'] = '{Call:Lang:modules:billing:strukturoobr}';
$matrix['main_group']['comment'] = '{Call:Lang:modules:billing:naosnovaniie}';
$matrix['main_group']['type'] = 'select';
$matrix['main_group']['additional'] = $main_groups;

$matrix['groups']['text'] = '{Call:Lang:modules:billing:dopolnitelny}';
$matrix['groups']['comment'] = '{Call:Lang:modules:billing:zdesmozhnouk}';
$matrix['groups']['type'] = 'checkbox_array';
$matrix['groups']['additional'] = $groups;

if(empty($extra)){
	$matrix['pkgs']['text'] = '{Call:Lang:modules:billing:sozdattarifi}';
	$matrix['pkgs']['comment'] = '{Call:Lang:modules:billing:eslivybratzd}';
	$matrix['pkgs']['type'] = 'select';
	$matrix['pkgs']['additional'] = $pkgs;
}
else{
	if($type != 'onetime'){
		$matrix['terms']['text'] = '{Call:Lang:modules:billing:srokinakotor}';
		$matrix['terms']['comment'] = '{Call:Lang:modules:billing:srokiukazyva:'.Library::serialize(array(Dates::termsListVars($baseTerm, 'multi2'), Dates::termsListVars($baseTerm, '2'))).'}';
		$matrix['terms']['type'] = 'text';
		$matrix['terms']['warn'] = '{Call:Lang:modules:billing:vyneukazalis3}';
		$matrix['terms']['warn_function'] = 'regExp::commaLine';

		$matrix['prolong_terms']['text'] = '{Call:Lang:modules:billing:srokinakotor1}';
		$matrix['prolong_terms']['comment'] = '{Call:Lang:modules:billing:srokiukazyva1:'.Library::serialize(array(Dates::termsListVars($baseTerm, 'multi2'), Dates::termsListVars($baseTerm, '2'))).'}';
		$matrix['prolong_terms']['type'] = 'text';
		$matrix['prolong_terms']['warn'] = '{Call:Lang:modules:billing:vyneukazalis4}';
		$matrix['prolong_terms']['warn_function'] = 'regExp::commaLine';

		$matrix['test']['text'] = '{Call:Lang:modules:billing:testovyjsrok:'.Library::serialize(array(Dates::termsListVars($testTerm, '2'))).'}';
		$matrix['test']['type'] = 'text';
		$matrix['test']['warn_function'] = 'regExp::digit';

		$matrix['max_test_accs']['text'] = 'Максимальное число тестовых аккаунтов';
		$matrix['max_test_accs']['type'] = 'text';
		$matrix['max_test_accs']['warn_function'] = 'regExp::digit';

		$matrix['inner_test']['text'] = '{Call:Lang:modules:billing:vkliuchattes}';
		$matrix['inner_test']['type'] = 'checkbox';

		$matrix['fract_prolong']['text'] = 'Разрешить дробить срок при автопродлении';
		$matrix['fract_prolong']['type'] = 'checkbox';
	}
}

$matrix['show']['text'] = '{Call:Lang:modules:billing:otobrazhatna}';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>