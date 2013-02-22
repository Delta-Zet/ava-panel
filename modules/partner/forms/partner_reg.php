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


$matrix['login']['text'] = '{Call:Lang:modules:partner:psevdonimpar1}';
$matrix['login']['type'] = 'hidden';
$values['login'] = $GLOBALS['Core']->getParam('partnerIDStyle', $this->mod) == 'id' ? $GLOBALS['Core']->User->getUserId() : $GLOBALS['Core']->User->params['login'];

if($GLOBALS['Core']->getParam('partnerIDStyle', $this->mod) == 'free'){
	$matrix['login']['type'] = 'text';
	$matrix['login']['warn_function'] = 'regExp::ident';
}

?>