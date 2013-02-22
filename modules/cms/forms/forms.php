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


$matrix['text']['text'] = '{Call:Lang:modules:cms:imia}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:modules:cms:neukazanoimi}';
$matrix['text']['comment'] = '{Call:Lang:modules:cms:liuboeponiat}';

$matrix['name']['text'] = '{Call:Lang:modules:cms:identifikato}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:cms:neukazaniden}';
$matrix['name']['warn_function'] = 'regExp::ident';
$matrix['name']['comment'] = '{Call:Lang:modules:cms:znacheniebud1}';
if(!empty($modify)) $matrix['name']['disabled'] = 1;

$matrix['caption']['text'] = 'Заголовок';
$matrix['caption']['type'] = 'text';

$matrix['sort']['text'] = '{Call:Lang:modules:cms:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = '{Call:Lang:modules:cms:formaispolzu}';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

$matrix['templates_caption']['text'] = 'Шаблоны построения';
$matrix['templates_caption']['type'] = 'caption';

if(!empty($templates)){
	foreach($templates as $i => $e){
		$matrix['template']['text'] = 'Шаблон формы для шаблона "'.$e['text'].'"';
		$matrix['template']['type'] = 'select';
		$matrix['template']['additional'] = $e['templates'];
	}
}

$matrix['send_caption']['text'] = 'Данные для отправки формы';
$matrix['send_caption']['type'] = 'caption';

$matrix['action_type']['text'] = '{Call:Lang:modules:cms:peredavatfor}';
$matrix['action_type']['type'] = 'select';
$matrix['action_type']['additional_style'] = 'onChange="switchByValue(\'action_type\', {blocks:{forAuto: 1, forUrl: 1}, 1:{forAuto: 1}, 2:{forUrl: 1}});"';
$matrix['action_type']['additional'] = array(
	'0' => '{Call:Lang:modules:cms:neperedavat}',
	'1' => '{Call:Lang:modules:cms:formirovatur}',
	'2' => '{Call:Lang:modules:cms:peredavatnav}',
);

$matrix['action']['pre_text'] = '<div id="forUrl" style="display: none;">';
$matrix['action']['text'] = 'URL';
$matrix['action']['type'] = 'text';
$matrix['action']['warn'] = '{Call:Lang:modules:cms:neukazanurl}';

$matrix['action_method']['text'] = '{Call:Lang:modules:cms:metodperedac}';
$matrix['action_method']['type'] = 'select';
$matrix['action_method']['additional'] = array('post' => 'POST', 'get' => 'GET');
$matrix['action_method']['post_text'] = '</div>';

$matrix['save_caption']['text'] = 'Данные для сохранения формы';
$matrix['save_caption']['type'] = 'caption';
$matrix['save_caption']['pre_text'] = '<div id="forAuto" style="display: none;">';

$matrix['save_style']['text'] = '';
$matrix['save_style']['type'] = 'checkbox_array';
$matrix['save_style']['additional'] = array(
	'db' => 'Сохранить введенные в форму данные в таблицу БД',
	'eml' => 'Отправить введенные данные на e-mail',
	'http' => 'Отправить введенные данные http-запросом',
);
$matrix['save_style']['additional_style'] = array(
	'db' => 'onClick="switchByValue(\'save_style_db\', {blocks:{forDb: 1}, 1:{forDb: 1}});"',
	'eml' => 'onClick="switchByValue(\'save_style_eml\', {blocks:{forEml: 1}, 1:{forEml: 1}});"',
	'http' => 'onClick="switchByValue(\'save_style_http\', {blocks:{forHttp: 1}, 1:{forHttp: 1}});"',
);

$matrix['save_style_table']['pre_text'] = '<div id="forDb" style="display: none;">';
$matrix['save_style_table']['text'] = '{Call:Lang:modules:cms:sokhranitvba}';
$matrix['save_style_table']['type'] = 'select';
$matrix['save_style_table']['additional_style'] = 'onClick="switchByValue(\'save_style_table\', {blocks:{forTable: 1, forIsset: 1, forStructure: 1}, 1:{forTable: 1}, 2:{forIsset: 1}, 3:{forStructure: 1}});"';
$matrix['save_style_table']['additional'] = array(
	'1' => '{Call:Lang:modules:cms:sozdatnovuiu}',
	'2' => '{Call:Lang:modules:cms:ispolzovatsu}',
	'3' => '{Call:Lang:modules:cms:sviazatskont}'
);

$matrix['new_table']['pre_text'] = '<div id="forTable" style="display: none;">';
$matrix['new_table']['text'] = '{Call:Lang:modules:cms:ispolzuemaia}';
$matrix['new_table']['type'] = 'text';
$matrix['new_table']['warn'] = '{Call:Lang:modules:cms:neukazanatab}';
$matrix['new_table']['warn_function'] = 'regExp::alNum';
$matrix['new_table']['post_text'] = '</div>';

$matrix['module']['text'] = '{Call:Lang:modules:cms:modul}';
$matrix['module']['type'] = 'select';
$matrix['module']['additional'] = $modules;
$matrix['module']['additional_style'] = "onChange='eval(\"setOptions(document.getElementById(\\\"table\\\"), opt.\" + this.value + \")\");'";
$matrix['module']['pre_text'] = '<div id="forIsset" style="display: none;">';

$all = $options = array();
if(!empty($tables)){
	foreach($tables as $i => $e){
		$tblList = array();
		foreach($e as $i1 => $e1){
			$tblList[$i1] = "{$i1}:'{$i1}'";
			$all[$i1] = $i1;
		}

		if($tblList) $options[] = $i.':{'.implode(',', $tblList).'}';
	}
}

$matrix['table']['text'] = '{Call:Lang:modules:cms:ispolzuemaia}';
$matrix['table']['type'] = 'select';
$matrix['table']['additional'] = $all;
$matrix['table']['post_text'] = '</div>';

$matrix['structure']['text'] = '{Call:Lang:modules:cms:kontentstruk}';
$matrix['structure']['type'] = 'select';
$matrix['structure']['additional'] = $structures;
$matrix['structure']['pre_text'] = '<div id="forStructure" style="display: none;">';
$matrix['structure']['post_text'] = '</div>';

if(!empty($modify)){
	$matrix['save_style_table']['disabled'] = 1;
	$matrix['new_table']['disabled'] = 1;
	$matrix['module']['disabled'] = 1;
	$matrix['table']['disabled'] = 1;
	$matrix['structure']['disabled'] = 1;
}

$matrix['eml_cap']['pre_text'] = '<div id="forEml" style="display: none;">';
$matrix['eml_cap']['text'] = 'Данные для отправки на e-mail';
$matrix['eml_cap']['type'] = 'caption';

$matrix['eml']['text'] = '{Call:Lang:modules:cms:spisokemailn}';
$matrix['eml']['warn'] = '{Call:Lang:modules:cms:neukazanyema}';
$matrix['eml']['type'] = 'textarea';

$matrix['eml_template']['text'] = '{Call:Lang:modules:cms:shablonpisma}';
$matrix['eml_template']['type'] = 'select';
$matrix['eml_template']['additional'] = $emlTemplates;
$matrix['eml_template']['post_text'] = '</div>';

$matrix['http_cap']['pre_text'] = '<div id="forHttp" style="display: none;">';
$matrix['http_cap']['text'] = 'Данные для отправки http-запросом';
$matrix['http_cap']['type'] = 'caption';

$matrix['url']['text'] = '{Call:Lang:modules:cms:urlnakotoryj}';
$matrix['url']['warn'] = '{Call:Lang:modules:cms:neukazanurlz}';
$matrix['url']['warn_function'] = 'regExp::url';
$matrix['url']['type'] = 'text';

$matrix['method']['text'] = '{Call:Lang:modules:cms:metodperedac}';
$matrix['method']['type'] = 'select';
$matrix['method']['additional'] = array('POST' => 'POST', 'GET' => 'GET');
$matrix['method']['post_text'] = '</div></div></div><script type="text/javascript">
	switchByValue(\'action_type\', {blocks:{forAuto: 1, forUrl: 1}, 1:{forAuto: 1}, 2:{forUrl: 1}});
	switchByValue(\'save_style_db\', {blocks:{forDb: 1}, 1:{forDb: 1}});
	switchByValue(\'save_style_eml\', {blocks:{forEml: 1}, 1:{forEml: 1}});

	switchByValue(\'save_style_http\', {blocks:{forHttp: 1}, 1:{forHttp: 1}});
	switchByValue(\'save_style_table\', {blocks:{forTable: 1, forIsset: 1, forStructure: 1}, 1:{forTable: 1}, 2:{forIsset: 1}, 3:{forStructure: 1}});
	var opt = {'.implode(',', $options).'};
	eval("setOptions(document.getElementById(\'table\'), opt." + document.getElementById(\'module\').value + ")");
</script>';

$matrix['action']['checkConditions']['action_type'] = '2';
$matrix['new_table']['checkConditions']['save_style'] = 'db';
$matrix['new_table']['checkConditions']['save_style_table'] = '1';
$matrix['url']['checkConditions']['save_style'] = 'http';
$matrix['eml']['checkConditions']['save_style'] = 'eml';

?>