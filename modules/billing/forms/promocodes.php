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


$field = 'started';
$text = '{Call:Lang:modules:billing:nachatprimen}';
require(_W.'forms/type_calendar2.php');
$matrix[$field]['comment'] = '   ,      ';

$field = 'actually';
$text = '{Call:Lang:modules:billing:primeniatdo}';
require(_W.'forms/type_calendar2.php');
$matrix[$field]['comment'] = '   ,       ';

$matrix['code_style']['text'] = '{Call:Lang:modules:billing:stilrabotyko}';
$matrix['code_style']['type'] = 'select';
$matrix['code_style']['warn'] = '{Call:Lang:modules:billing:neukazanstil}';
$matrix['code_style']['additional'] = array(
	'1' => '{Call:Lang:modules:billing:odnorazovye}',
	'2' => '{Call:Lang:modules:billing:mnogorazovye}'
);

if(empty($extra)){
	$matrix['insert']['type'] = 'radio';
	$matrix['insert']['additional'] = array(
		'hand' => '{Call:Lang:modules:billing:vstavitvruch}',
		'auto' => '{Call:Lang:modules:billing:vstavitavtom}'
	);
	$matrix['insert']['additional_style'] = array(
		'hand' => 'onChange="showFormBlock(\'handcodes\'); hideFormBlock(\'autocodes\');"',
		'auto' => 'onChange="hideFormBlock(\'handcodes\'); showFormBlock(\'autocodes\');"'
	);
	$values['insert'] = 'auto';

	$matrix['codes']['pre_text'] = '<div id="handcodes" style="display: none">';
	$matrix['codes']['text'] = '{Call:Lang:modules:billing:spisokvnosim}';
	$matrix['codes']['type'] = 'textarea';
	$matrix['codes']['post_text'] = '</div>';

	$matrix['codes_cnt']['pre_text'] = '<div id="autocodes" style="display: none">';
	$matrix['codes_cnt']['text'] = '{Call:Lang:modules:billing:kolichestvov}';
	$matrix['codes_cnt']['type'] = 'text';
	$matrix['codes_cnt']['post_text'] = '</div>
	<script type="text/javascript">
		if(document.getElementById("insert_hand").checked){
			showFormBlock("handcodes");
			hideFormBlock("autocodes");
		}
		else if(document.getElementById("insert_auto").checked){
			hideFormBlock("handcodes");
			showFormBlock("autocodes");
		}
	</script>';
}
else{
	$matrix['code']['text'] = '{Call:Lang:modules:billing:kod}';
	$matrix['code']['type'] = 'text';
	$matrix['code']['warn'] = '{Call:Lang:modules:billing:neukazankod}';
}

?>