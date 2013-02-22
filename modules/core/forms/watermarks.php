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

$matrix['transparency']['text'] = '{Call:Lang:core:core:prozrachnost1}';
$matrix['transparency']['type'] = 'text';

$matrix['corner']['text'] = '{Call:Lang:core:core:ugolpovorota}';
$matrix['corner']['type'] = 'text';

$matrix['hpos']['text'] = '{Call:Lang:core:core:pozitsiiapog}';
$matrix['hpos']['type'] = 'text';

$matrix['hcorner']['text'] = '{Call:Lang:core:core:otschityvato}';
$matrix['hcorner']['type'] = 'select';
$matrix['hcorner']['additional'] = array(
	'l' => '{Call:Lang:core:core:levogougla}',
	'r' => '{Call:Lang:core:core:pravogougla}'
);

$matrix['vpos']['text'] = '{Call:Lang:core:core:pozitsiiapov}';
$matrix['vpos']['type'] = 'text';

$matrix['vcorner']['text'] = '{Call:Lang:core:core:otschityvato}';
$matrix['vcorner']['type'] = 'select';
$matrix['vcorner']['additional'] = array(
	't' => '{Call:Lang:core:core:verkhnegougl}',
	'b' => '{Call:Lang:core:core:nizhnegougla}'
);

$matrix['sort']['text'] = '{Call:Lang:core:core:indekssortir}';
$matrix['sort']['type'] = 'text';

$matrix['type']['text'] = '{Call:Lang:core:core:tipznaka}';
$matrix['type']['type'] = 'select';
$matrix['type']['warn'] = '{Call:Lang:core:core:neukazantipz}';
$matrix['type']['additional_style'] = 'onChange="switchByValue(\'type\', {blocks:{forImage: 1, forText: 1}, image:{forImage: 1}, text:{forText: 1}});"';
$matrix['type']['additional'] = array(
	'image' => '{Call:Lang:core:core:izobrazhenie}',
	'text' => '{Call:Lang:core:core:tekst}'
);

$matrix['moment']['text'] = 'Водяные знаки накладывать';
$matrix['moment']['type'] = 'select';
$matrix['moment']['additional'] = array(
	'' => 'После изменения размера',
	'1' => 'До изменения размера',
);

$matrix['file']['pre_text'] = '<div id="forImage" style="display: none;">';
$matrix['file']['text'] = '{Call:Lang:core:core:fajlizobrazh}';
$matrix['file']['type'] = 'file';
$matrix['file']['additional'] = array(
	'allow_ext' => array('.jpg', '.gif', 'png', '.bmp'),
	'dstFolder' => $GLOBALS['Core']->getParam('watermarksFolder')
);
$matrix['file']['post_text'] = '</div>';

$matrix['content']['pre_text'] = '<div id="forText" style="display: none;">';
$matrix['content']['text'] = '{Call:Lang:core:core:tekst}';
$matrix['content']['type'] = 'textarea';

$matrix['font']['text'] = '{Call:Lang:core:core:shrift}';
$matrix['font']['type'] = 'select';
$matrix['font']['additional'] = empty($fonts) ? array() : $fonts;

$matrix['font_size']['text'] = '{Call:Lang:core:core:razmershrift}';
$matrix['font_size']['type'] = 'text';

$matrix['color']['text'] = '{Call:Lang:core:core:tsvetnaprime}';
$matrix['color']['type'] = 'text';
$matrix['color']['warn_pattern'] = '|\d{6}|';
$matrix['color']['post_text'] = '</div><script type="text/javascript">
	switchByValue("type", {blocks:{forImage: 1, forText: 1}, image:{forImage: 1}, text:{forText: 1}});
</script>';

$matrix['show']['text'] = '{Call:Lang:core:core:vodianojznak}';
$matrix['show']['type'] = 'checkbox';

?>