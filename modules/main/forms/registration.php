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


$matrix['type_auth']['type'] = 'radio';
$matrix['type_auth']['warn'] = '{Call:Lang:core:core:vyneukazalii}';
$matrix['type_auth']['additional'] = array(
	'1' => '{Call:Lang:core:core:iakhochuzare}',
	'2' => '{Call:Lang:core:core:umeniauzhees}'
);
$values['type_auth'] = 1;

$matrix['type_auth']['additional_style'] = array(
	'1' => 'onClick="showFormBlock(\'block1\'); showFormBlock(\'block2\'); hideFormBlock(\'block3\');"',
	'2' => 'onClick="hideFormBlock(\'block2\'); hideFormBlock(\'block1\'); showFormBlock(\'block3\');"'
);

$matrix['name']['text'] = '{Call:Lang:core:core:vashifamilii}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:core:core:vyneukazalis2}';

require(_W.'forms/type_neweml.php');
require(_W.'forms/type_newlogin.php');
require(_W.'forms/type_newpwd.php');
require(_W.'forms/type_utc.php');
require(_W.'forms/type_auth.php');

$matrix['name']['pre_text'] = '<div id="block1" style="display: none">';
$matrix['eml']['post_text'] = '</div>';
$matrix['pwd']['comment'] = '';
$matrix['cpwd']['pre_text'] = '<div id="block2" style="display: none">';

$matrix['memory']['template'] = '';
$matrix['memory']['post_text'] = '</div>';
$matrix['memory']['pre_text'] = '<div id="block3" style="display: none">';
$matrix['memory']['post_text'] = '</div>
<script type="text/javascript">
	if(document.getElementById(\'type_auth_1\').checked){
		showFormBlock(\'block1\');
		showFormBlock(\'block2\');
		hideFormBlock(\'block3\');
	}
	else if(document.getElementById(\'type_auth_2\').checked){
		hideFormBlock(\'block2\');
		hideFormBlock(\'block1\');
		showFormBlock(\'block3\');
	}

	function showTypeFields(){
		var show = document.getElementById("type").value;
		eval("showObj = {" + show + ": true};");
		switchFormBlocks('.Library::jsHash($formTypes).', showObj);
	}
</script>';

if($formTypes){
	$matrix['type']['text'] = 'Вы регистрируетесь как';
	$matrix['type']['type'] = 'select';
	$matrix['type']['warn'] = 'Вы не указали тип анкеты';
	$matrix['type']['additional'] = $formTypes;
	$matrix['type']['additional_style'] = 'onChange="showTypeFields();"';
}

$matrix['name']['checkConditions']['type_auth'] = '1';
$matrix['eml']['checkConditions']['type_auth'] = '1';
$matrix['pwd']['checkConditions']['type_auth'] = '1';
$matrix['cpwd']['checkConditions']['type_auth'] = '1';

?>