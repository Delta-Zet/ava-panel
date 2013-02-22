<?php

	/******************************************************************************************************
	*** Package: AVA-Panel Version 3.0
	*** Copyright (c) 2006, Anton A. Rassypaeff. All rights reserved
	*** License: GNU General Public License v3
	*** Author: Anton A. Rassypaeff | Рассыпаев Антон Александрович
	*** Contacts:
	***   Site: http://ava-panel.ru
	***   E-mail: manage@ava-panel.ru
	******************************************************************************************************/


require_once 'JsHttpRequest.php';
require_once 'tinyimages.php';

$JsHttpRequest =& new JsHttpRequest('windows-1251');

if(!isset($_REQUEST['m'])) {
	$GLOBALS['_RESULT'] = array( 'error' => '  ');
	exit();
}
list($module, $method) = explode('->',$_REQUEST['m']);
if(empty($method)) {
	list($module, $method) = explode('-%3E',$_REQUEST['m']);
}
$method = 'ajax'.$method;

$timgs = new tinyimages();

$GLOBALS['_RESULT'] = $timgs->$method($_REQUEST);
exit();