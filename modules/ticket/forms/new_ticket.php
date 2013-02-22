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


$matrix['department']['text'] = '{Call:Lang:modules:ticket:razdel}';
$matrix['department']['type'] = 'select';
$matrix['department']['warn'] = '{Call:Lang:modules:ticket:neukazanrazd}';
$matrix['department']['additional'] = $departments;

$matrix['rcpt_style']['text'] = '';
$matrix['rcpt_style']['type'] = 'radio';
$matrix['rcpt_style']['warn'] = '{Call:Lang:modules:ticket:neukazanokom}';
$matrix['rcpt_style']['additional'] = array(
	'personal' => '{Call:Lang:modules:ticket:otpravitpers}',
	'group' => '{Call:Lang:modules:ticket:otpravitgrup}',
);

$matrix['rcpt_style']['additional_style'] = array(
	'personal' => 'onClick="showFormBlock(\'forPersonal\'); hideFormBlock(\'forGroup\');"',
	'group' => 'onClick="showFormBlock(\'forGroup\'); hideFormBlock(\'forPersonal\');"',
);

$matrix['rcpt_login']['text'] = '{Call:Lang:modules:ticket:loginpolucha}';
$matrix['rcpt_login']['type'] = 'text';
$matrix['rcpt_login']['pre_text'] = '<div id="forPersonal" style="display: none">';
$matrix['rcpt_login']['post_text'] = '</div>';

$matrix['rcpt_grp']['text'] = '{Call:Lang:modules:ticket:gruppypoluch}';
$matrix['rcpt_grp']['type'] = 'checkbox_array';
$matrix['rcpt_grp']['additional'] = $groups;
$matrix['rcpt_grp']['pre_text'] = '<div id="forGroup" style="display: none">';
$matrix['rcpt_grp']['post_text'] = '</div><script type="text/javascript">
	if(document.getElementById("rcpt_style_personal").checked){
		showFormBlock(\'forPersonal\');
		hideFormBlock(\'forGroup\');
	}
	else if(document.getElementById("rcpt_style_group").checked){
		hideFormBlock(\'forPersonal\');
		showFormBlock(\'forGroup\');
	}
</script>';

$matrix['name']['text'] = '{Call:Lang:modules:ticket:tema}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:ticket:neukazanatem}';

require(_W.'modules/ticket/forms/new_message.php');

?>