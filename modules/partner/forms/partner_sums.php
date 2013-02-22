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


if(!empty($isPersonal)){
	$matrix['usePersonalBillPrice_'.$mod]['text'] = '';
	$matrix['usePersonalBillPrice_'.$mod]['type'] = 'radio';
	$matrix['usePersonalBillPrice_'.$mod]['additional'] = array(
		'default' => '{Call:Lang:modules:partner:ispolzovatra}',
		'hand' => '{Call:Lang:modules:partner:vystavitspet1}'
	);
	$matrix['usePersonalBillPrice_'.$mod]['additional_style'] = array(
		'default' => 'onClick="hideFormBlock(\'forBilling_'.$mod.'\');"',
		'hand' => 'onClick="showFormBlock(\'forBilling_'.$mod.'\');"'
	);
	$values['usePersonalBillPrice_'.$mod] = 'default';
	$matrix['settings_style_by_client_'.$mod]['pre_text'] = '<div id="forBilling_'.$mod.'" style="display: none;">';
}

$matrix['settings_style_by_client_'.$mod]['text'] = '';
$matrix['settings_style_by_client_'.$mod]['type'] = 'radio';
$matrix['settings_style_by_client_'.$mod]['warn'] = '{Call:Lang:modules:partner:neukazanoisp}';
$matrix['settings_style_by_client_'.$mod]['additional'] = array(
	'uni' => '{Call:Lang:modules:partner:ispolzovated}',
	'group' => '{Call:Lang:modules:partner:vystavitpers1}',
);
$matrix['settings_style_by_client_'.$mod]['additional_style'] = array(
	'uni' => 'onClick="showFormBlock(\'settings_style_by_client_'.$mod.'_uni_block\'); hideFormBlock(\'settings_style_by_client_'.$mod.'_group_block\');"',
	'group' => 'onClick="hideFormBlock(\'settings_style_by_client_'.$mod.'_uni_block\'); showFormBlock(\'settings_style_by_client_'.$mod.'_group_block\');"',
);
$values['settings_style_by_client_'.$mod] = 'uni';

$type = $grp = '';
$grpName = '{Call:Lang:modules:partner:nastrojkiotc}';
require(_W.'modules/partner/forms/partner_sum_settings.php');
$matrix['grp_caption_'.$mod.'_'.$type.'_'.$grp]['pre_text'] = '<div id="settings_style_by_client_'.$mod.'_uni_block" style="display: none;">';
$matrix[$last2]['post_text'] .= '</div>';

$type = '_settings';
$matrix['grp_caption_'.$mod.'_'.$type.'_new-clients']['pre_text'] = '<div id="settings_style_by_client_'.$mod.'_group_block" style="display: none;">';
foreach(Library::array_merge(array('new-clients' => '{Call:Lang:modules:partner:novyeklienty}', 'old-clients' => '{Call:Lang:modules:partner:staryeklient}'), $obj->getLoyaltyLevels()) as $grp => $grpName){
	require(_W.'modules/partner/forms/partner_sum_settings.php');
}
$matrix[$last2]['post_text'] .= '</div>';

$matrix[$last2]['post_text'] .= '<script type="text/javascript">
	if(document.getElementById("settings_style_by_client_'.$mod.'_uni").checked){
		showFormBlock(\'settings_style_by_client_'.$mod.'_uni_block\');
		hideFormBlock(\'settings_style_by_client_'.$mod.'_group_block\');
	}
	else if(document.getElementById("settings_style_by_client_'.$mod.'_group").checked){
		hideFormBlock(\'settings_style_by_client_'.$mod.'_uni_block\');
		showFormBlock(\'settings_style_by_client_'.$mod.'_group_block\');
	}
</script>';

if(!empty($isPersonal)){
	$matrix[$last2]['post_text'] .= '</div><script type="text/javascript">
		if(document.getElementById("usePersonalBillPrice_'.$mod.'_hand").checked) showFormBlock(\'forBilling_'.$mod.'\');
	</script>';
}

?>