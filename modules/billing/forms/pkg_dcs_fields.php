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



$matrix[$bType]['text'] = '';
$matrix[$bType]['type'] = 'radio';
$matrix[$bType]['template'] = 'width100';
$matrix[$bType]['post_text'] = '<div id="'.$bType.'1" style="display: none;">';
$matrix[$bType]['additional'] = array(
	'0' => '{Call:Lang:modules:billing:neispolzovat1}',
	'1' => '{Call:Lang:modules:billing:ispolzovatod}',
	'2' => '{Call:Lang:modules:billing:vystavitpers}'
);

$matrix[$bType]['additional_style'] = array(
	'0' => 'onClick="hideFormBlock(\''.$bType.'1\'); hideFormBlock(\''.$bType.'2\');"',
	'1' => 'onClick="showFormBlock(\''.$bType.'1\'); hideFormBlock(\''.$bType.'2\');"',
	'2' => 'onClick="showFormBlock(\''.$bType.'2\'); hideFormBlock(\''.$bType.'1\');"'
);

if($bType == 'aacc' || $bType == 'mpkg' || $bType == 'opkg'){	$matrix[$bType]['additional']['3'] = '{Call:Lang:modules:billing:vystavliatpe}';
	$matrix[$bType]['additional_style']['3'] = 'onClick="hideFormBlock(\''.$bType.'1\'); hideFormBlock(\''.$bType.'2\');"';
}

$prefix = $postfix = '';
require(_W.'modules/billing/forms/fields4'.$bType.'_blk.php');
$matrix[($bType == 'pkg_list' ? $bType.'_group' : $bType.'_hidden')]['post_text'] = '</div><div id="'.$bType.'2" style="display: none;">';

foreach($groups as $i => $e){
	$matrix[$bType.'_capt_'.$i]['text'] = $e;
	$matrix[$bType.'_capt_'.$i]['type'] = 'caption';

	$postfix = '_'.$i;
	require(_W.'modules/billing/forms/fields4'.$bType.'_blk.php');
}

$matrix[($bType == 'pkg_list' ? $bType.'_group_'.$i : $bType.'_hidden_'.$i)]['post_text'] = '</div><script type="text/javascript">
	if(document.getElementById("'.$bType.'_0").checked){ hideFormBlock(\''.$bType.'1\'); hideFormBlock(\''.$bType.'2\'); }
	else if(document.getElementById("'.$bType.'_1").checked){ showFormBlock(\''.$bType.'1\'); hideFormBlock(\''.$bType.'2\'); }
	else if(document.getElementById("'.$bType.'_2").checked){ showFormBlock(\''.$bType.'2\'); hideFormBlock(\''.$bType.'1\'); }
</script>';

?>