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


$matrix['sites']['text'] = '{Call:Lang:core:core:sajtyskotory}';
$matrix['sites']['type'] = 'checkbox_array';
$matrix['sites']['warn'] = 'Не указан ни один сайт';
$matrix['sites']['comment'] = '{Call:Lang:core:core:ustanavlivae}';
$matrix['sites']['additional'] = $sites;

$matrix['text_'.$modName]['text'] = '{Call:Lang:core:core:imiamodulia}';
$matrix['text_'.$modName]['type'] = 'text';
$matrix['text_'.$modName]['comment'] = '{Call:Lang:core:core:liuboeponiat}';
$matrix['text_'.$modName]['warn'] = '{Call:Lang:core:core:neukazanoimi5}';

$matrix['name_'.$modName]['text'] = '{Call:Lang:core:core:urlimiadliam}';
$matrix['name_'.$modName]['type'] = 'text';
$matrix['name_'.$modName]['warn_function'] = 'regExp::ident';
$matrix['name_'.$modName]['comment'] = '{Call:Lang:core:core:tolkolatinsk}';
$matrix['name_'.$modName]['warn'] = '{Call:Lang:core:core:neukazanourl}';

if(empty($modify)){
	if(!empty($dbList)){
		$matrix['db']['text'] = '{Call:Lang:core:core:bazadannykhd}';
		$matrix['db']['type'] = 'select';
		$matrix['db']['comment'] = '{Call:Lang:core:core:vukazannuiub}';
		$matrix['db']['additional'] = $dbList;
	}

	foreach($requiredModules as $i => $e){
		$name = $GLOBALS['Core']->getModulePrototypeName($i);
		$matrix['united_'.$i]['text'] = '{Call:Lang:core:core:sviazatskopi:'.Library::serialize(array($name)).'}';
		$matrix['united_'.$i]['type'] = 'select';
		$matrix['united_'.$i]['comment'] = '{Call:Lang:core:core:dannyjpaketm:'.Library::serialize(array($name)).'}';
		$matrix['united_'.$i]['warn'] = '{Call:Lang:core:core:neukazanakop:'.Library::serialize(array($name)).'}';
		$matrix['united_'.$i]['additional'] = $GLOBALS['Core']->getModulesByType($i);
	}

	$matrix['settings']['type'] = 'radio';
	$matrix['settings']['additional'] = array(
		'' => '{Call:Lang:core:core:sozdatsnastr}',
		'isset' => '{Call:Lang:core:core:dublirovatna}',
		'duplicate' => '{Call:Lang:core:core:sozdatpolnyj}'
	);
}
else{
	$matrix['text_'.$modName]['disabled'] = 1;
	$matrix['name_'.$modName]['disabled'] = 1;
}

$matrix['show']['text'] = '{Call:Lang:core:core:moduldostupe}';
$matrix['show']['type'] = 'checkbox';

?>