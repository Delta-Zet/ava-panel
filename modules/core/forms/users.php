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


require_once(_W.'forms/type_newlogin.php');
require_once(_W.'forms/type_neweml.php');
if(empty($this->values['type_action'])) require_once(_W.'forms/type_newpwd.php');

$matrix['comment']['text'] = '{Call:Lang:core:core:kommentarija}';
$matrix['comment']['type'] = 'textarea';

$matrix['group']['text'] = '{Call:Lang:core:core:gruppa}';
$matrix['group']['type'] = 'select';
$matrix['group']['additional'] = $groups;

$matrix['name']['text'] = '{Call:Lang:core:core:familiiaimia}';
$matrix['name']['type'] = 'text';

$text = '{Call:Lang:core:core:datavnesenii}';
$field = 'date';
require_once(_W.'forms/type_calendar2.php');
$values['date'] = time();

$matrix['show']['text'] = '{Call:Lang:core:core:polzovatelim}';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

if($formTypes){
	$matrix['type']['text'] = 'Пользователь регистрируется как';
	$matrix['type']['type'] = 'select';
	$matrix['type']['warn'] = 'Вы не указали тип анкеты';
	$matrix['type']['additional'] = $formTypes;
	$matrix['type']['additional_style'] = 'onChange="showTypeFields();"';

	$matrix['type']['post_text'] = '
	<script type="text/javascript">
		function showTypeFields(){
			var show = document.getElementById("type").value;
			eval("showObj = {" + show + ": true};");
			switchFormBlocks('.Library::jsHash($formTypes).', showObj);
		}
	</script>';
}

?>