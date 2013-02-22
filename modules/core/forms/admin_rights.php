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


$matrix['rights_'.$module]['text'] = '';
$matrix['rights_'.$module]['type'] = 'radio';
$matrix['rights_'.$module]['template'] = 'width100';

$matrix['rights_'.$module]['additional_style'] = array(
	0 => 'onClick="hideFormBlock(\'forFuncs_'.$module.'\')"',
	1 => 'onClick="hideFormBlock(\'forFuncs_'.$module.'\')"',
	2 => 'onClick="hideFormBlock(\'forFuncs_'.$module.'\')"',
	4 => 'onClick="showFormBlock(\'forFuncs_'.$module.'\')"',
);

$matrix['rights_'.$module]['additional'] = array(
	0 => '{Call:Lang:core:core:dostupanet}',
	1 => '{Call:Lang:core:core:tolkopravopr}',
	2 => '{Call:Lang:core:core:pravoizmenen}',
	4 => '{Call:Lang:core:core:personalnyen}'
);

if(regExp::lower($func) == 'adminsrights'){
	$matrix['rights_'.$module]['additional'][5] = '{Call:Lang:core:core:ispolzovatpr}';
	$matrix['rights_'.$module]['additional_style'][5] = 'onClick="hideFormBlock(\'forFuncs_'.$module.'\')"';
}

foreach($funcs as $i => $e){
	if(empty($first)) $first = $i;
	$last = $i;

	$matrix['rights_'.$module.'_'.$i]['text'] = '{Call:Lang:core:core:dliafunktsii:'.Library::serialize(array($e)).'}';
	$matrix['rights_'.$module.'_'.$i]['type'] = 'select';
	$matrix['rights_'.$module.'_'.$i]['additional'] = array('0' => '{Call:Lang:core:core:dostupanet}', '1' => '{Call:Lang:core:core:tolkopravopr}', '2' => '{Call:Lang:core:core:pravoizmenen}');
}

if(!empty($first)){
	$matrix['rights_'.$module.'_'.$first]['pre_text'] = '<div id="forFuncs_'.$module.'" style="display: none;">';
	$matrix['rights_'.$module.'_'.$last]['post_text'] = '</div><script type="text/javascript">
			if(document.getElementById("rights_'.$module.'_4").checked) showFormBlock(\'forFuncs_'.$module.'\');
			else hideFormBlock(\'forFuncs_'.$module.'\');
		</script>';
}

?>