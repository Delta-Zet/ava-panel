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


$matrix['access_caption_'.$mod]['text'] = $modName;
$matrix['access_caption_'.$mod]['type'] = 'caption';

$matrix['access_'.$mod]['text'] = '{Call:Lang:modules:partner:dostupimeiut}';
$matrix['access_'.$mod]['type'] = 'radio';
$matrix['access_'.$mod]['additional'] = array(
	'no' => '{Call:Lang:modules:partner:nikto}',
	'all' => '{Call:Lang:modules:partner:vsepartnery}',
	'group' => '{Call:Lang:modules:partner:vzavisimosti}'
);
$values['access_'.$mod] = 'no';

$matrix['access_'.$mod]['additional_style'] = array(
	'no' => 'onClick="hideFormBlock(\'forPertnerGroups_'.$mod.'\');"',
	'all' => 'onClick="hideFormBlock(\'forPertnerGroups_'.$mod.'\');"',
	'group' => 'onClick="showFormBlock(\'forPertnerGroups_'.$mod.'\');"'
);

$matrix['access_groups_'.$mod]['text'] = '{Call:Lang:modules:partner:gruppyimeius}';
$matrix['access_groups_'.$mod]['type'] = 'checkbox_array';
$matrix['access_groups_'.$mod]['additional'] = $groups;
$matrix['access_groups_'.$mod]['pre_text'] = '<div id="forPertnerGroups_'.$mod.'" style="display: none;">';
$matrix['access_groups_'.$mod]['post_text'] = '</div><script type="text/javascript">
	if(document.getElementById("access_'.$mod.'_group").checked) showFormBlock(\'forPertnerGroups_'.$mod.'\');
</script>';

?>