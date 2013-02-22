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


$matrix['file']['text'] = '{Call:Lang:core:core:kartinkadlia}';
$matrix['file']['type'] = 'file';
$matrix['file']['warn'] = '{Call:Lang:core:core:otsutstvuetf}';
$matrix['file']['additional'] = array(
	'allow_ext' => array('.jpg', '.gif', '.png', '.bmp'),
	'dstFolder' => $GLOBALS['Core']->getParam('captchaFolder')
);

?>