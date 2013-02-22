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


$modules = Library::array_merge(array('main' => '{Call:Lang:core:core:obshchijmodu}', 'core' => '{Call:Lang:core:core:iadro}'), $GLOBALS['Core']->getModules());

$matrix['text']['pre_text'] = '<script type="text/javascript">
	function setPluginFields(){
		ge("services_div").style.display = "block";

		ge("point_div").style.display = "none";
		ge("modulePoint_div").style.display = "none";
		ge("function_div").style.display = "none";

		ge("noExistFunc_div").style.display = "none";
		ge("position_div").style.display = "none";
		ge("widget_div").style.display = "none";

		if(ge("type_point").checked) ge("point_div").style.display = "block";
		else if(ge("type_modulePoint").checked) ge("modulePoint_div").style.display = "block";
		else if(ge("type_function").checked){
			ge("function_div").style.display = "block";
			ge("position_div").style.display = "block";
		}
		else if(ge("type_noExistFunc").checked){
			ge("noExistFunc_div").style.display = "block";
			ge("position_div").style.display = "block";
		}
		else if(ge("type_widget").checked){
			ge("widget_div").style.display = "block";
		}

		if(ge("type_widget").checked || ge("type_simple").checked) ge("services_div").style.display = "none";
	}
</script>';

$matrix['text']['text'] = '{Call:Lang:core:core:imia}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:core:core:neukazanoimi7}';

$matrix['name']['text'] = '{Call:Lang:core:core:identifikato}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:core:core:neukazanidnt}';

$matrix['services']['pre_text'] = '<div id="services_div">';
$matrix['services']['text'] = '{Call:Lang:core:core:plaginispolz}';
$matrix['services']['type'] = 'checkbox_array';
$matrix['services']['additional'] = array(
	'site' => '{Call:Lang:core:core:naobshchemsa}',
	'admin' => '{Call:Lang:core:core:vadminke}',
	'api' => '{Call:Lang:core:core:vapi}',
	'cron' => '{Call:Lang:core:core:vovnutrennik}'
);
$matrix['services']['post_text'] = '</div>';

$matrix['type']['text'] = '{Call:Lang:core:core:plaginbudetv}';
$matrix['type']['type'] = 'radio';
$matrix['type']['warn'] = '{Call:Lang:core:core:neukazantipp3}';
$matrix['type']['additional'] = array(
	'point' => '{Call:Lang:core:core:vopredelenno}',
	'modulePoint' => '{Call:Lang:core:core:vopredelenno1}',
	'function' => '{Call:Lang:core:core:vmestesopred}',
	'noExistFunc' => '{Call:Lang:core:core:vmestesopred1}',
	'widget' => 'в шаблоне как виджет',
	'simple' => '{Call:Lang:core:core:priamymvyzov}',
);
$matrix['type']['additional_style'] = array(
	'point' => 'onClick="setPluginFields();"',
	'modulePoint' => 'onClick="setPluginFields();"',
	'function' => 'onClick="setPluginFields();"',
	'noExistFunc' => 'onClick="setPluginFields();"',
	'widget' => 'onClick="setPluginFields();"',
	'simple' => 'onClick="setPluginFields();"'
);
$values['type'] = 'simple';

$matrix['descript']['text'] = '{Call:Lang:core:core:opisanie}';
$matrix['descript']['type'] = 'textarea';

$matrix['point']['pre_text'] = '<div id="point_div" style="display: none">';
$matrix['point']['text'] = '{Call:Lang:core:core:pozitsiia}';
$matrix['point']['type'] = 'select';
$matrix['point']['warn'] = '{Call:Lang:core:core:neukazanapoz}';
$matrix['point']['additional'] = array(
	'start' => '{Call:Lang:core:core:srazuposleza}',
	'settings' => '{Call:Lang:core:core:posleschityv}',
	'url' => '{Call:Lang:core:core:poslerazbora}',
	'access' => '{Call:Lang:core:core:posleproverk}',
	'callFunction' => '{Call:Lang:core:core:poslevyzovao}',
	'templateGen' => '{Call:Lang:core:core:poslezapuska}',
	'headers' => '{Call:Lang:core:core:posleotpravk}',
	'content' => '{Call:Lang:core:core:poslepoluche}',
	'contentTransform' => '{Call:Lang:core:core:poslepreobra}',
	'shutdown' => '{Call:Lang:core:core:privyzoveshu}',
	'closeSite' => '{Call:Lang:core:core:priobnaruzhe}',
	'accessDeny' => '{Call:Lang:core:core:priobnaruzhe1}',
	'exception' => '{Call:Lang:core:core:priobrabotke}'
);
$matrix['point']['post_text'] = '</div>';

$matrix['modulePointMod']['pre_text'] = '<div id="modulePoint_div" style="display: none">';
$matrix['modulePointMod']['text'] = '{Call:Lang:core:core:modul}';
$matrix['modulePointMod']['type'] = 'select';
$matrix['modulePointMod']['warn'] = '{Call:Lang:core:core:neukazanmodu}';
$matrix['modulePointMod']['additional'] = $modules;

$matrix['modulePoint']['text'] = '{Call:Lang:core:core:pozitsiia}';
$matrix['modulePoint']['type'] = 'select';
$matrix['modulePoint']['warn'] = '{Call:Lang:core:core:neukazanapoz}';
$matrix['modulePoint']['additional'] = $GLOBALS['Core']->getPluginPoints();
$matrix['modulePoint']['post_text'] = '</div>';

$matrix['functionMod']['pre_text'] = '<div id="function_div" style="display: none">';
$matrix['functionMod']['text'] = '{Call:Lang:core:core:modul}';
$matrix['functionMod']['type'] = 'select';
$matrix['functionMod']['warn'] = '{Call:Lang:core:core:neukazanmodu}';
$matrix['functionMod']['additional'] = $modules;

$matrix['function']['text'] = '{Call:Lang:core:core:metod}';
$matrix['function']['type'] = 'text';
$matrix['function']['warn'] = '{Call:Lang:core:core:neukazanmeto}';
$matrix['function']['post_text'] = '</div>';

$matrix['noExistFuncClass']['pre_text'] = '<div id="noExistFunc_div" style="display: none">';
$matrix['noExistFuncClass']['text'] = '{Call:Lang:core:core:klass}';
$matrix['noExistFuncClass']['type'] = 'text';
$matrix['noExistFuncClass']['warn'] = '{Call:Lang:core:core:neukazanklas}';

$matrix['noExistFunc']['text'] = '{Call:Lang:core:core:metod}';
$matrix['noExistFunc']['type'] = 'text';
$matrix['noExistFunc']['warn'] = '{Call:Lang:core:core:neukazanmeto}';
$matrix['noExistFunc']['post_text'] = '</div>';

$matrix['position']['pre_text'] = '<div id="position_div" style="display: none">';
$matrix['position']['text'] = 'Запускать';
$matrix['position']['type'] = 'select';
$matrix['position']['additional'] = array('before' => 'До указанной функции', 'after' => 'После указанной функции', 'instead' => 'Вместо указанной функции');
$matrix['position']['post_text'] = '</div>';

$matrix['sort']['text'] = '{Call:Lang:core:core:pozitsiiavso}';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = '{Call:Lang:core:core:zapuskatplag}';
$matrix['show']['type'] = 'checkbox';
$values['show'] = '1';

$matrix['code']['text'] = '{Call:Lang:core:core:ispolniaemyj1}';
$matrix['code']['type'] = 'textarea';
$matrix['code']['warn'] = '{Call:Lang:core:core:netispolniae}';
$matrix['code']['template'] = 'big';

$matrix['settings_code']['text'] = 'Код выполняемый при вызове формы настройки плагина';
$matrix['settings_code']['type'] = 'textarea';
$matrix['settings_code']['template'] = 'big';

$matrix['set_code']['pre_text'] = '<div id="widget_div" style="display: none">';
$matrix['set_code']['text'] = 'Код выполняемый при установке виджета на страницу';
$matrix['set_code']['type'] = 'textarea';
$matrix['set_code']['template'] = 'big';
$matrix['set_code']['post_text'] = '</div><script type="text/javascript">
	setPluginFields();
</script>';

$matrix['point']['checkConditions']['type'] = 'point';
$matrix['modulePointMod']['checkConditions']['type'] = 'modulePoint';
$matrix['modulePoint']['checkConditions']['type'] = 'modulePoint';
$matrix['functionMod']['checkConditions']['type'] = 'function';
$matrix['function']['checkConditions']['type'] = 'function';
$matrix['noExistFuncClass']['checkConditions']['type'] = 'noExistFunc';
$matrix['noExistFunc']['checkConditions']['type'] = 'noExistFunc';
$matrix['set_code']['checkConditions']['type'] = 'widget';

if(!empty($modify)){
	$matrix['type']['disabled'] = 1;
	$matrix['name']['disabled'] = 1;
}

?>