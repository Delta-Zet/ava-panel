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


$matrix['ident']['type'] = 'text';
$matrix['ident']['text'] = 'Идентификатор';
$matrix['ident']['warn'] = 'Вы не указали идентификатор';
$matrix['ident']['warn_function'] = 'regExp::ident';
if(!empty($modify)) $matrix['ident']['disabled'] = true;

$matrix['driver']['type'] = 'select';
$matrix['driver']['text'] = '{Call:Lang:core:core:tipbazy}';
$matrix['driver']['warn'] = '{Call:Lang:core:core:neukazantipb1}';
$matrix['driver']['additional'] = $dbDrivers;

$matrix['name']['type'] = 'text';
$matrix['name']['text'] = '{Call:Lang:core:core:imiabazy}';
$matrix['name']['warn'] = '{Call:Lang:core:core:neukazanoimi6}';

$matrix['user']['type'] = 'text';
$matrix['user']['text'] = '{Call:Lang:core:core:polzovatel}';
$matrix['user']['warn'] = '{Call:Lang:core:core:neukazanpolz1}';

$matrix['pwd']['type'] = 'pwd';
$matrix['pwd']['text'] = '{Call:Lang:core:core:parol}';
$matrix['pwd']['warn'] = '{Call:Lang:core:core:neukazanparo}';

$matrix['host']['type'] = 'text';
$matrix['host']['text'] = '{Call:Lang:core:core:khostdostupa}';
$matrix['host']['warn'] = '{Call:Lang:core:core:neukazankhos1}';

$matrix['prefix']['type'] = 'text';
$matrix['prefix']['text'] = '{Call:Lang:core:core:prefiksdliat}';

$switchs = array();
$values = array();

foreach($dbDrivers as $i => $e){
	list($m2, $v2) = call_user_func(array('db_'.$i, 'getConnectMatrix'));
	$matrix = library::array_merge($matrix, $m2);
	$values = library::array_merge($values, $v2);

	$matrix[library::firstKey($m2)]['pre_text'] = '<div id="block_'.$i.'" style="display: none;">';
	$matrix[library::lastKey($m2)]['post_text'] = '</div>';
	$switchs[$i] = array('block_'.$i => 1);
	$switchs['blocks']['block_'.$i] = 1;
}

$switchCode = "switchByValue('driver', ".library::jsHash($switchs).");";
$matrix['driver']['additional_style'] = " onChange=\"$switchCode\"";
$matrix[library::lastKey($matrix)]['post_text'] .= "<script type=\"text/javascript\">\n{$switchCode}\n</script>";

?>