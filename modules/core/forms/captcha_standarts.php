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


$matrix['text']['text'] = '{Call:Lang:core:core:nazvanie}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:core:core:neukazanonaz}';

$matrix['name']['text'] = '{Call:Lang:core:core:identifikato}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:core:core:neukazaniden}';
$matrix['name']['warn_function'] = 'regExp::ident';
if(!empty($modify)) $matrix['name']['disabled'] = 1;

$matrix['backgrounds']['text'] = '{Call:Lang:core:core:ispolzuemyef}';
$matrix['backgrounds']['type'] = 'checkbox_array';
$matrix['backgrounds']['comment'] = '{Call:Lang:core:core:eslineukazan}';
$matrix['backgrounds']['additional'] = isset($backgrounds) ? $backgrounds : array();

$matrix['captcha_type']['text'] = '{Call:Lang:core:core:tipcaptcha}';
$matrix['captcha_type']['type'] = 'select';
$matrix['captcha_type']['warn'] = '{Call:Lang:core:core:neukazantipc}';
$matrix['captcha_type']['additional_style'] = 'onChange="switchByValue(\'captcha_type\', {blocks:{forText: 1, forMath: 1}, m:{forMath: 1}, t:{forText: 1}});"';
$matrix['captcha_type']['additional'] = array(
	't' => '{Call:Lang:core:core:tekstovaia}',
	'm' => '{Call:Lang:core:core:matematiches}'
);

$matrix['direction']['text'] = '{Call:Lang:core:core:napravlenie}';
$matrix['direction']['type'] = 'select';
$matrix['direction']['additional'] = array(
	'l' => '{Call:Lang:core:core:slevanapravo}',
	'r' => '{Call:Lang:core:core:spravanalevo}',
	't' => '{Call:Lang:core:core:sverkhuvniz}',
	'b' => '{Call:Lang:core:core:snizuvverkh}',
	'c' => '{Call:Lang:core:core:vkrugovuiupo}',
	'd' => '{Call:Lang:core:core:vkrugovuiupr}',
	'a' => '{Call:Lang:core:core:sluchajnymob}',
);

$matrix['symbols']['pre_text'] = '<div id="forText" style="display: none;">';
$matrix['symbols']['text'] = '{Call:Lang:core:core:dopustimyesi}';
$matrix['symbols']['type'] = 'textarea';
$values['symbols'] = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

$matrix['len']['text'] = '{Call:Lang:core:core:dlinasimvolo}';
$matrix['len']['type'] = 'gap';

$matrix['register_depend']['text'] = '{Call:Lang:core:core:captcharegis}';
$matrix['register_depend']['type'] = 'checkbox';
$matrix['register_depend']['post_text'] = '</div>';

$matrix['math_actions']['pre_text'] = '<div id="forMath" style="display: none;">';
$matrix['math_actions']['text'] = 'Допустимые математические действия';
$matrix['math_actions']['type'] = 'checkbox_array';
$matrix['math_actions']['additional'] = array('+' => 'Сложение', '-' => 'Вычитание', '*' => 'Умножение', '/' => 'Деление');

$matrix['math_nums']['text'] = '{Call:Lang:core:core:ispolzovatch}';
$matrix['math_nums']['type'] = 'gap';

$matrix['math_len']['text'] = '{Call:Lang:core:core:kolichestvoa}';
$matrix['math_len']['type'] = 'gap';
$matrix['math_len']['post_text'] = '</div><script type="text/javascript">
	switchByValue(\'captcha_type\', {blocks:{forText: 1, forMath: 1}, m:{forMath: 1}, t:{forText: 1}});
</script>';

$matrix['sort']['text'] = '{Call:Lang:core:core:indekssortir}';
$matrix['sort']['type'] = 'text';

$matrix['show']['text'] = '{Call:Lang:core:core:standartiavl}';
$matrix['show']['type'] = 'checkbox';

$matrix['settings_caption']['text'] = '{Call:Lang:core:core:dopolnitelny4}';
$matrix['settings_caption']['type'] = 'caption';

$matrix['fonts']['text'] = '{Call:Lang:core:core:ispolzuemyes}';
$matrix['fonts']['type'] = 'checkbox_array';
$matrix['fonts']['warn'] = '{Call:Lang:core:core:neukazanniod1}';
$matrix['fonts']['additional'] = isset($fonts) ? $fonts : array();

$matrix['font_size']['text'] = '{Call:Lang:core:core:razmershrift}';
$matrix['font_size']['type'] = 'gap';

$matrix['font_blur']['text'] = '{Call:Lang:core:core:urovenrazmyt}';
$matrix['font_blur']['type'] = 'gap';

$matrix['start_position']['text'] = '{Call:Lang:core:core:pozitsiiaper}';
$matrix['start_position']['type'] = 'gap';

$matrix['start_position_vertical']['text'] = '{Call:Lang:core:core:pozitsiiaper1}';
$matrix['start_position_vertical']['type'] = 'gap';

$matrix['letter_offset']['text'] = '{Call:Lang:core:core:smeshchenies}';
$matrix['letter_offset']['type'] = 'gap';

$matrix['letter_vertical_offset']['text'] = '{Call:Lang:core:core:smeshchenies1}';
$matrix['letter_vertical_offset']['type'] = 'gap';

$matrix['angle']['text'] = '{Call:Lang:core:core:ugolnaklonas}';
$matrix['angle']['type'] = 'gap';

$matrix['color']['text'] = '{Call:Lang:core:core:iskazheniets}';
$matrix['color']['comment'] = '{Call:Lang:core:core:iskazheniets1}';
$matrix['color']['type'] = 'gap';

$matrix['transparent']['text'] = '{Call:Lang:core:core:prozrachnost}';
$matrix['transparent']['type'] = 'gap';

?>