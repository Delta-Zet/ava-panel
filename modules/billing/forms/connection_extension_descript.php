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


$matrix['text']['text'] = 'Имя';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = 'Не указано имя';

$matrix['name']['text'] = 'Идентификатор в панели управления';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = 'Не указан идентификатор в панели управления';
if(!empty($modify)) $matrix['name']['disabled'] = 1;

$matrix['type']['text'] = 'Формальный тип';
$matrix['type']['type'] = 'select';
$matrix['type']['additional_style'] = 'onChange="selectType();"';
$matrix['type']['additional'] = array(
	'text' => 'Текст',
	'checkbox' => 'Галочка',
	'select' => 'Список',
	'checkbox_array' => 'Список со множественным выбором',
);

$matrix['k']['pre_text'] = '<div id="type_text_blk">';
$matrix['k']['text'] = 'Коэффициент';
$matrix['k']['type'] = 'text';
$matrix['k']['comment'] = 'На эту сумму будет умножено передаваемое значение';

$matrix['unlimit']['text'] = 'Значение указывающее на безлимит';
$matrix['unlimit']['type'] = 'text';

$matrix['unlimitAlias']['text'] = 'Имя поля указывающего на безлимит';
$matrix['unlimitAlias']['type'] = 'text';
$matrix['unlimitAlias']['comment'] = 'Если оставить это поле пустым, будет использован идентификатор';
$matrix['unlimitAlias']['post_text'] = '</div>';

$matrix['ch']['pre_text'] = '<div id="type_checkbox_blk">';
$matrix['ch']['text'] = 'Значение передаваемое если пункт отмечен';
$matrix['ch']['type'] = 'text';

$matrix['noch']['text'] = 'Значение передаваемое если пункт не отмечен';
$matrix['noch']['type'] = 'text';
$matrix['noch']['post_text'] = '</div><script type="text/javascript">
	function selectType(){
		hideFormBlock("type_text_blk");
		hideFormBlock("type_checkbox_blk");

		var t = ge("type").value;
		if(t == "text" || t == "select") showFormBlock("type_text_blk");
		else if(t == "checkbox") showFormBlock("type_checkbox_blk");
	}
	selectType();
</script>';

?>