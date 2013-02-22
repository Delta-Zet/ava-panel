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


if(empty($field)) $field = 'utc';
if(empty($text)) $text = '{Call:Lang:core:core:chasovojpoia2}';

$matrix[$field]['text'] = $text;
$matrix[$field]['type'] = 'select';
$matrix[$field]['additional'] = Dates::UTCList();
$values[$field] = $GLOBALS['Core']->getParam('UTC');

?>