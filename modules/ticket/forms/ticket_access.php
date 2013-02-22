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


if(empty($lastMatrix)){
	$matrix['access_type']['text'] = '{Call:Lang:modules:ticket:tipdostupa}';
	$matrix['access_type']['type'] = 'radio';
	$matrix['access_type']['warn'] = '{Call:Lang:modules:ticket:neukazantipd}';
	$matrix['access_type']['additional'] = array(
		'0' => '{Call:Lang:modules:ticket:vse}',
		'1' => '{Call:Lang:modules:ticket:zaregistriro}',
		'2' => '{Call:Lang:modules:ticket:spetsialnyen}',
	);

	$matrix['access_type']['additional_style'] = array(
		'0' => 'onClick="hideFormBlock(\'type2_block\');"',
		'1' => 'onClick="hideFormBlock(\'type2_block\');"',
		'2' => 'onClick="showFormBlock(\'type2_block\');"',
	);

	$matrix['access_type_caption']['text'] = '{Call:Lang:modules:ticket:spetsialnyen1}';
	$matrix['access_type_caption']['type'] = 'caption';
	$matrix['access_type_caption']['pre_text'] = '<div id="type2_block" style="display: none">';
	$values['access_type'] = $access_type;
}
else{
	$matrix[$lastMatrix]['post_text'] = $lastMatrixText.'</div><script type="text/javascript">
		if(document.getElementById("access_type_2").checked) showFormBlock(\'type2_block\');
	</script>';
}

?>