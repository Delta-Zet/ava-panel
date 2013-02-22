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


$matrix['text']['text'] = '{Call:Lang:core:core:nazvaniestan}';
$matrix['text']['type'] = 'text';
$matrix['text']['warn'] = '{Call:Lang:core:core:neukazanonaz}';

$matrix['name']['text'] = '{Call:Lang:core:core:identifikato}';
$matrix['name']['type'] = 'text';
$matrix['name']['warn'] = '{Call:Lang:core:core:neukazaniden}';
$matrix['name']['warn_function'] = 'regExp::ident';
if(!empty($modify)) $matrix['name']['disabled'] = 1;

$matrix['quality']['text'] = '{Call:Lang:core:core:kachestvoizo}';
$matrix['quality']['type'] = 'text';
$values['quality'] = 100;

$matrix['width']['text'] = '{Call:Lang:core:core:umenshatposh}';
$matrix['width']['type'] = 'text';
$matrix['width']['comment'] = '{Call:Lang:core:core:esliukazatno}';
$matrix['width']['warn_function'] = 'regExp::digit';

$matrix['height']['text'] = '{Call:Lang:core:core:umenshatpovy}';
$matrix['height']['type'] = 'text';
$matrix['height']['comment'] = '{Call:Lang:core:core:esliukazatno1}';
$matrix['height']['warn_function'] = 'regExp::digit';

$matrix['resize_style']['text'] = '{Call:Lang:core:core:prikonflikte}';
$matrix['resize_style']['type'] = 'select';
$matrix['resize_style']['comment'] = '{Call:Lang:core:core:esliukazanou}';
$matrix['resize_style']['additional'] = array(
	'0' => '{Call:Lang:core:core:umenshatprop}',
	'1' => '{Call:Lang:core:core:vyrezatlevyj}',
	'2' => '{Call:Lang:core:core:vyrezatiztse}',
	'3' => '{Call:Lang:core:core:vyrezatlevyj1}',
	'4' => '{Call:Lang:core:core:vyrezatverkh}',
	'5' => '{Call:Lang:core:core:vyrezattsent}',
	'6' => '{Call:Lang:core:core:vyrezatnizts}',
	'7' => '{Call:Lang:core:core:vyrezatpravy}',
	'8' => '{Call:Lang:core:core:vyrezatiztse1}',
	'9' => '{Call:Lang:core:core:vyrezatpravy1}',
);

$matrix['enlarge']['text'] = '{Call:Lang:core:core:esliizobrazh}';
$matrix['enlarge']['type'] = 'checkbox';

$matrix['watermarks']['text'] = '{Call:Lang:core:core:stavitvodian}';
$matrix['watermarks']['type'] = 'checkbox_array';
$matrix['watermarks']['additional'] = empty($watermarks) ? array() : $watermarks;

$matrix['sort']['text'] = '{Call:Lang:core:core:indekssortir}';
$matrix['sort']['type'] = 'text';

$matrix['rotate']['text'] = 'Повернуть на угол';
$matrix['rotate']['comment'] = 'Угол поворота указывается в градусах цельсия против часовой стрелке';
$matrix['rotate']['type'] = 'text';

$matrix['rotate_moment']['text'] = 'Поворачивать';
$matrix['rotate_moment']['type'] = 'select';
$matrix['rotate_moment']['additional'] = array(
	'1' => 'До наложения водяных знаков налагаемых перед изменением размера',
	'2' => 'До изменения размера',
	'3' => 'До наложения водяного знаков налагаемых после изменения размера',
	'4' => 'После наложения водяных знаков',
);

$matrix['rotate_color']['text'] = 'Полости после поворота заполнять';
$matrix['rotate_color']['comment'] = 'Укажите цвет в HTML-формате, например FFFFFF. Если оставить поле пустым, для png и gif цвет будет прозрачным, для jpg - черным';
$matrix['rotate_color']['type'] = 'text';
$matrix['rotate_color']['warn_pattern'] = '|^[0-9abcdef]{6}$|i';
$values['rotate_color'] = 'FFFFFF';

$matrix['rotate_color_transparent']['text'] = 'Сделать полости образовавшиеся после поворота прозрачными. Актуально для .GIF и .PNG';
$matrix['rotate_color_transparent']['type'] = 'checkbox';

$matrix['show']['text'] = '{Call:Lang:core:core:standartprim}';
$matrix['show']['type'] = 'checkbox';

?>