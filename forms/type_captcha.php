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


if(!isset($field)) $field = 'captcha';
if(!isset($standart)) $standart = 'main';

$matrix[$field]['text'] = '{Call:Lang:core:core:vveditestrok}';
$matrix[$field]['type'] = 'captcha';
$matrix[$field]['warn_function'] = 'checkFunctions::captcha';
$matrix[$field]['warn'] = 'Вы не указали защитный код';
$matrix[$field]['template'] = 'captcha';
$matrix[$field]['captchaStandart'] = $standart;

?>