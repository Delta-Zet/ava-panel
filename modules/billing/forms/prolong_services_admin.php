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


$matrix['prolong_type']['text'] = 'Продлить';
$matrix['prolong_type']['type'] = 'radio';
$matrix['prolong_type']['warn'] = 'Не указан тип продления';
$matrix['prolong_type']['additional'] = array(
	'days' => 'на указанное число дней',
	'date' => 'до указанной даты'
);

$values['prolong_type'] = 'days';
$matrix['prolong_type']['additional_style'] = array(
	'days' => 'onClick="selectBlock();"',
	'date' => 'onClick="selectBlock();"',
);

$matrix['days']['pre_text'] = '<div id="daysBlock" style="display: none;">';
$matrix['days']['text'] = 'Добавить дней';
$matrix['days']['type'] = 'text';
$matrix['days']['post_text'] = '</div>';

$matrix['date']['pre_text'] = '<div id="dateBlock" style="display: none;">';
$matrix['date']['text'] = 'Продлить до';
$matrix['date']['type'] = 'calendar2';
$matrix['date']['post_text'] = '</div><script type="text/javascript">
	function selectBlock(){
		if(ge("prolong_type_days").checked){ showFormBlock(\'daysBlock\'); hideFormBlock(\'dateBlock\'); }
		else if(ge("prolong_type_date").checked){ showFormBlock(\'dateBlock\'); hideFormBlock(\'daysBlock\'); }
	}

	selectBlock();
</script>';

$values['date'] = time();

?>