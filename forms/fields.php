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


$matrix['text']['text'] = '{Call:Lang:core:core:tekstkomment}';
$matrix['text']['type'] = 'text';

$matrix['name']['text'] = '{Call:Lang:core:core:imiapoliaide}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:core:core:neukazaniden}';
$matrix['name']['warn_function'] = 'regExp::ident';
$matrix['name']['comment'] = '{Call:Lang:core:core:ukazannoezna}';

$matrix['type']['text'] = '{Call:Lang:core:core:tippolia}';
$matrix['type']['type'] = 'select';
$matrix['type']['warn'] = '{Call:Lang:core:core:neukazantipp2}';
$matrix['type']['additional'] = array(
	'text' => '{Call:Lang:core:core:tekstovoepol}',
	'textarea' => '{Call:Lang:core:core:tekstovaiaob}',
	'checkbox' => '{Call:Lang:core:core:galochka}',
	'checkbox_array' => '{Call:Lang:core:core:spisokiznesk}',
	'select' => '{Call:Lang:core:core:vypadaiushch}',
	'multiselect' => '{Call:Lang:core:core:vypadaiushch1}',
	'radio' => '{Call:Lang:core:core:radioknopka}',
	'pwd' => '{Call:Lang:core:core:polevvodapar}',
	'file' => '{Call:Lang:core:core:zagruzkafajl1}',
	'calendar2' => '{Call:Lang:core:core:data}',
	'calendar' => '{Call:Lang:core:core:promezhutokd}',
	'gap' => '{Call:Lang:core:core:promezhutokc}',
	'captcha' => 'CAPTCHA',
	'hidden' => '{Call:Lang:core:core:skrytoepole}'
);

if(!empty($extra)){
	$matrix['name']['disabled'] = 1;

	$matrix['value']['text'] = '{Call:Lang:core:core:znacheniepou}';
	$matrix['value']['type'] = 'textarea';
	$matrix['value']['comment'] = '{Call:Lang:core:core:dliapolejtip}';

	$matrix['comment']['text'] = '{Call:Lang:core:core:vsplyvaiushc}';
	$matrix['comment']['type'] = 'textarea';

	$matrix['eval']['text'] = '{Call:Lang:core:core:ispolniaemyj}';
	$matrix['eval']['type'] = 'textarea';

	$matrix['additional_text']['text'] = '{Call:Lang:core:core:dopolnitelny1}';
	$matrix['additional_text']['type'] = 'textarea';
	$matrix['additional_text']['comment'] = '{Call:Lang:core:core:kakpraviloeh}';

	$matrix['additional_style']['text'] = '{Call:Lang:core:core:dopolnitelny2}';
	$matrix['additional_style']['type'] = 'textarea';
	$matrix['additional_style']['comment'] = '{Call:Lang:core:core:naprimerstyl}';

	$matrix['other_params']['text'] = '{Call:Lang:core:core:inyeparametr}';
	$matrix['other_params']['type'] = 'textarea';
	$matrix['other_params']['comment'] = '{Call:Lang:core:core:dopolnitelny3}';

	$matrix['warn']['text'] = '{Call:Lang:core:core:tekstuvedoml}';
	$matrix['warn']['type'] = 'text';
	$matrix['warn']['comment'] = '{Call:Lang:core:core:ehtottekstot}';

	$matrix['warn_function']['text'] = '{Call:Lang:core:core:spetsialnaia}';
	$matrix['warn_function']['type'] = 'text';
	$matrix['warn_function']['comment'] = '{Call:Lang:core:core:zdesmozhnouk}';

	$matrix['warn_pattern']['text'] = '{Call:Lang:core:core:patternpravi}';
	$matrix['warn_pattern']['type'] = 'text';
	$matrix['warn_pattern']['comment'] = '{Call:Lang:core:core:ispolzuiutsi}';

	$matrix['warn_pattern_text']['text'] = '{Call:Lang:core:core:uvedomleniev}';
	$matrix['warn_pattern_text']['type'] = 'text';
	$matrix['warn_pattern_text']['comment'] = '{Call:Lang:core:core:ehtottekstbu}';
}

$matrix['sort']['text'] = '{Call:Lang:core:core:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['disabled']['text'] = '{Call:Lang:core:core:ehtopoleiavl}';
$matrix['disabled']['type'] = 'checkbox';

$matrix['show']['text'] = '{Call:Lang:core:core:ehtopoledolz}';
$matrix['show']['type'] = 'checkbox';
$matrix['show']['comment'] = '{Call:Lang:core:core:ubravzdesgal}';
$values['show'] = 1;

if(!empty($canAddNewField)){
	$matrix['insert_field']['text'] = 'Создать дополнительное поле в таблице "'.$canAddNewField.'"';
	$matrix['insert_field']['type'] = (empty($extra) || empty($issetField)) ? 'checkbox' : 'hidden';
	$matrix['insert_field']['additional_style'] = " onClick='insertFldBlk();'";

	$matrix['insert_field_type']['pre_text'] = '<div id="insertFldBlk" style="display: none;">';
	$matrix['insert_field_type']['text'] = 'Тип поля в таблице БД';
	$matrix['insert_field_type']['type'] = 'select';
	$matrix['insert_field_type']['additional_style'] = " onChange='insertFldBlk();'";
	$matrix['insert_field_type']['additional'] = array(
		'' => 'По умолчанию',
		'VARCHAR(255)' => 'Строка (VARCHAR(255))',
		'CHAR(1)' => 'Символ (CHAR(1))',
		'TEXT' => 'Текст (TEXT)',
		'TINYINT' => 'Маленькое число (TINYINT)',
		'INT' => 'Целое число (INT)',
		'DECIMAL(12,2)' => 'Дробь (DECIMAL(12,2))',
		'manual' => 'Указать свое'
	);

	$matrix['insert_field_type_manual']['pre_text'] = '<div id="insertFldBlkManual" style="display: none;">';
	$matrix['insert_field_type_manual']['text'] = 'Указать тип поля вручную';
	$matrix['insert_field_type_manual']['type'] = 'text';
	$matrix['insert_field_type_manual']['post_text'] = '</div></div><script type="text/javascript">
		function insertFldBlk(){
			if((ge("insert_field").type == "checkbox" && ge("insert_field").checked) || (ge("insert_field").type != "checkbox" && ge("insert_field").value)){
				showFormBlock("insertFldBlk");
				if(ge("insert_field_type").value == "manual") showFormBlock("insertFldBlkManual");
				else hideFormBlock("insertFldBlkManual");
			}
			else hideFormBlock("insertFldBlk");
		}

		insertFldBlk();
	</script>';
}

if(!isset($type)) $type = '';
$sync = array();

switch($type){
	case 'select':
	case 'radio':
	case 'multiselect':
	case 'checkbox_array':
		$matrix['additional']['text'] = '{Call:Lang:core:core:znacheniiasp}';
		$matrix['additional']['type'] = 'textarea';
		$matrix['additional']['comment'] = '{Call:Lang:core:core:dolzhnysosto}';

		$sync = array('name', 'text', 'type', 'value', 'additional', 'additional_text', 'additional_style');
		break;

	case 'checkbox':
		$matrix['value']['text'] = '{Call:Lang:core:core:ehtopolepoum}';
		$matrix['value']['type'] = 'checkbox';
		$matrix['value']['comment'] = '';

		unset($matrix['warn_pattern'], $matrix['warn_pattern_text'], $matrix[''], $matrix[''], $matrix[''], $matrix['']);
		$sync = array('name', 'text', 'type', 'warn', 'warn_function', 'sort', 'value', 'disabled', 'show', 'comment', 'additional_text');
		break;

	case 'file':
		$matrix['value']['comment'] = '{Call:Lang:core:core:dliapoliatip}';

		$matrix['max']['text'] = '{Call:Lang:core:core:maksimalnodo}';
		$matrix['max']['type'] = 'text';
		$matrix['max']['comment'] = '{Call:Lang:core:core:esliostavitp}';

		$matrix['min']['text'] = '{Call:Lang:core:core:minimalnodop}';
		$matrix['min']['type'] = 'text';

		$matrix['dstFolder']['text'] = '{Call:Lang:core:core:papkadliaraz}';
		$matrix['dstFolder']['type'] = 'select';
		$matrix['dstFolder']['additional'] = $folders;

		$matrix['allow_ext']['text'] = '{Call:Lang:core:core:dopustimyeti1}';
		$matrix['allow_ext']['type'] = 'text';
		$matrix['allow_ext']['warn'] = '{Call:Lang:core:core:neukazanniod}';
		$matrix['allow_ext']['comment'] = '{Call:Lang:core:core:zdessleduetu}';

		break;

	case 'captcha':
		$matrix['captchaStandart']['text'] = 'Стандарт CAPTCHA';
		$matrix['captchaStandart']['type'] = 'select';
		$matrix['captchaStandart']['warn'] = 'Не указан стандарт используемой CAPTCHA';
		$matrix['captchaStandart']['additional'] = $GLOBALS['Core']->getCaptchaStandarts();
}

if(!empty($formTpl) && $formTpl == 'big') $sync = array('name', 'text', 'value', 'type', 'warn', 'warn_pattern', 'warn_pattern_text', 'additional', 'additional_text', 'additional_style');
$matrix = Library::syncArraySeq($matrix, $sync);

?>