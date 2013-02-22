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
$matrix['text']['comment'] = '{Call:Lang:modules:cms:liuboeponiat1}';

$matrix['name']['text'] = '{Call:Lang:modules:cms:identifikato}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:modules:cms:neukazaniden}';
$matrix['name']['warn_function'] = 'regExp::ident';
$matrix['name']['comment'] = '{Call:Lang:modules:cms:znacheniebud2}';
if(!empty($modify)) $matrix['name']['disabled'] = 1;

$matrix['sort']['text'] = '{Call:Lang:modules:cms:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

if(empty($modify)){
	$matrix['type']['text'] = '{Call:Lang:modules:cms:istochnikkon}';
	$matrix['type']['type'] = 'radio';
	$matrix['type']['warn'] = '{Call:Lang:modules:cms:neukazanisto}';
	$matrix['type']['comment'] = '{Call:Lang:modules:cms:zdesukazhite}';
	$matrix['type']['additional'] = array(
		'table' => '{Call:Lang:modules:cms:ispolzovatsu1}',
		'internal' => '{Call:Lang:modules:cms:sozdatnovuiu1}'
	);

	$matrix['type']['additional_style'] = array(
		'table' => ' onClick="setInsertType();"',
		'internal' => ' onClick="setInsertType();"'
	);
}

$options = array();
if(empty($modify) || $type == 'table'){
	$matrix['module']['type'] = 'select';
	$matrix['module']['text'] = '{Call:Lang:modules:cms:ukazhitemodu}';
	$matrix['module']['additional'] = $modules;
	$matrix['module']['additional_style'] = "onChange='eval(\"setOptions(document.getElementById(\\\"table\\\"), opt.\" + this.value + \")\");'";

	$all = array();
	foreach($tables as $i => $e){
		$tblList = array();
		foreach($e as $i1 => $e1){
			$tblList[$i1] = "{$i1}:'{$i1}'";
			$all[$i1] = $i1;
		}

		$options[] = $i.':{'.implode(',', $tblList).'}';
	}

	$matrix['table']['type'] = 'select';
	$matrix['table']['text'] = '{Call:Lang:modules:cms:ukazhiteimia}';
	$matrix['table']['additional'] = $all;
	$matrix['table']['post_text'] = '';

	if(empty($modify)){
		$matrix['table']['post_text'] = '</div>';
		$matrix['module']['pre_text'] = '<div id="isset" style="display: none;">';
	}

	$matrix['table']['post_text'] .= '<script type="text/javascript">
			var opt = {'.implode(',', $options).'};
			eval("setOptions(document.getElementById(\'table\'), opt." + document.getElementById(\'module\').value + ")");
		</script>';
}

if(empty($modify) || $type == 'internal'){
	$matrix['in_page']['type'] = 'select';
	$matrix['in_page']['text'] = '{Call:Lang:modules:cms:predlagatvne}';
	$matrix['in_page']['additional'] = array(
		'0' => '{Call:Lang:modules:cms:net}',
		'1' => '{Call:Lang:modules:cms:vnositvsegda}',
		'4' => '{Call:Lang:modules:cms:spetsialnyen}',
	);
	$matrix['in_page']['additional_style'] = "onChange='switchByValue(\"in_page\", {blocks:{in_page_templates: 1}, 4:{in_page_templates: 1}});'";
	$matrix['in_page']['pre_text'] = '<div id="internal" style="display: none;">';

	$matrix['in_page_up']['type'] = 'checkbox';
	$matrix['in_page_up']['text'] = '{Call:Lang:modules:cms:obnovliatsod}';
	$matrix['in_page_up']['post_text'] = '<div id="in_page_templates" style="display: none;">';
	$values['in_page_up'] = 1;
	$last = 'in_page_up';

	foreach($pageTemplates as $i => $e){
		if(!empty($e['pages'])){
			$matrix['in_page_template_'.$i.'_caption']['type'] = 'caption';
			$matrix['in_page_template_'.$i.'_caption']['text'] = '{Call:Lang:modules:cms:nastrojkidli:'.Library::serialize(array($e['name'])).'}';

			foreach($e['pages'] as $i1 => $e1){
				$matrix['in_page_template_'.$i.'_'.$i1.'_style']['type'] = 'checkbox';
				$matrix['in_page_template_'.$i.'_'.$i1.'_style']['text'] = '{Call:Lang:modules:cms:nastranitsak:'.Library::serialize(array($i1)).'}';
				$last = 'in_page_template_'.$i.'_'.$i1.'_style';
			}
		}
	}

	$matrix[$last]['post_text'] = '';
	if(!empty($modify)) unset($matrix['in_page']['pre_text']);
	else $matrix[$last]['post_text'] = '</div>';

	$matrix[$last]['post_text'] .= '</div><script type="text/javascript">
		switchByValue("in_page", {blocks:{in_page_templates: 1}, 4:{in_page_templates: 1}});
	</script>';
}

$matrix['templatesCapt']['text'] = '{Call:Lang:modules:cms:shablonystru}';
$matrix['templatesCapt']['type'] = 'caption';

foreach($templates as $i => $e){
	$matrix['template_'.$i]['type'] = 'select';
	$matrix['template_'.$i]['text'] = '{Call:Lang:modules:cms:dliashablona:'.Library::serialize(array($e['name'])).'}';
	$matrix['template_'.$i]['additional'] = $e['blocks'];
}

$matrix['admin_template']['type'] = 'select';
$matrix['admin_template']['text'] = '{Call:Lang:modules:cms:shablondliaa}';
$matrix['admin_template']['additional'] = $adminTemplates;

if(empty($modify)){
	$matrix['admin_template']['post_text'] = '<script type="text/javascript">
		function setInsertType(){
			if(document.getElementById(\'type_table\').checked){
				showFormBlock(\'isset\');
				hideFormBlock(\'internal\');
			}
			else if(document.getElementById(\'type_internal\').checked){
				hideFormBlock(\'isset\');
				showFormBlock(\'internal\');
			}
		}

		setInsertType();
	</script>';
}

?>