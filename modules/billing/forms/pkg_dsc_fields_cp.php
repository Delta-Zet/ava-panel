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



if($type == 'text'){
	$matrix['use_unlimit']['type'] = 'checkbox';
	$matrix['use_unlimit']['text'] = '{Call:Lang:modules:billing:parametrdopu}';
}

$matrix['cp_capt']['type'] = 'caption';
$matrix['cp_capt']['text'] = '{Call:Lang:modules:billing:sootvetstvie}';

if(!empty($cp)){
	foreach($cp as $i => $e){
		if(!$e['extra']) continue;
		$matrix['cp_conformity_'.$i]['type'] = 'select';
		$matrix['cp_conformity_'.$i]['text'] = $e['name'];
		$matrix['cp_conformity_'.$i]['additional'] = Library::array_merge(array('' => '{Call:Lang:modules:billing:netsootvetst}'), $e['extra']['cpParams']);
	}
}

$matrix['use_if_no_conformity']['type'] = 'checkbox';
$matrix['use_if_no_conformity']['text'] = '{Call:Lang:modules:billing:otobrazhatdl}';

$matrix['use_if_no_panel']['type'] = 'checkbox';
$matrix['use_if_no_panel']['text'] = '{Call:Lang:modules:billing:otobrazhates}';

?>