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


$matrix['folder']['text'] = '{Call:Lang:core:core:papkashablon}';
$matrix['folder']['type'] = 'text';
$matrix['folder']['comment'] = '{Call:Lang:core:core:tolkolatinsk2}';
$matrix['folder']['warn'] = '{Call:Lang:core:core:vydolzhnyuka1}';
$matrix['folder']['warn_function'] = 'regExp::folder';

$matrix['name']['text'] = '{Call:Lang:core:core:imiashablona}';
$matrix['name']['type'] = 'text';
$matrix['name']['comment'] = '{Call:Lang:core:core:liuboeponiat2}';
$matrix['name']['warn'] = '{Call:Lang:core:core:vydolzhnyuka}';

if(($type == 'main' || $type == 'admin') && !empty($languages)){
	$matrix['language']['text'] = '{Call:Lang:core:core:iazykispolzu}';
	$matrix['language']['type'] = 'select';
	$matrix['language']['warn'] = '{Call:Lang:core:core:vydolzhnyuka2}';
	$matrix['language']['additional'] = $languages;
}
elseif($type == 'module' && !empty($modules)){
	$matrix['module']['text'] = 'Модуль';
	$matrix['module']['type'] = 'select';
	$matrix['module']['warn'] = 'Вы не указали модуль';
	$matrix['module']['additional'] = $modules;
}

$matrix['sort']['text'] = '{Call:Lang:core:core:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

if($type == 'main' || $type == 'admin'){
	if(!empty($dependSysTmp)){
		$matrix['sys_depend_tmp']['text'] = '{Call:Lang:core:core:ispolzuemyjs}';
		$matrix['sys_depend_tmp']['type'] = 'select';
		$matrix['sys_depend_tmp']['additional'] = $dependSysTmp;
	}

	if(!empty($dependBlockTmp)){
		$matrix['block_depend_tmp']['text'] = '{Call:Lang:core:core:ispolzuemyjs1}';
		$matrix['block_depend_tmp']['type'] = 'select';
		$matrix['block_depend_tmp']['additional'] = $dependBlockTmp;
	}

	if(!empty($dependModuleTmps) && is_array($dependModuleTmps)){
		foreach($dependModuleTmps as $i => $e){
			if(!empty($e)){
				$depModName = empty($depModNames[$i]) ? $i : $depModNames[$i];

				$matrix['depend_tmp_'.$i]['text'] = '{Call:Lang:core:core:ispolzuemyjs2:'.Library::serialize(array($depModName)).'}';
				$matrix['depend_tmp_'.$i]['type'] = 'select';
				$matrix['depend_tmp_'.$i]['additional'] = $e;
			}
		}
	}

	$matrix['show']['text'] = '{Call:Lang:core:core:shablondostu}';
	$matrix['show']['type'] = 'checkbox';
}

?>