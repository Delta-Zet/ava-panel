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



//    
foreach($GLOBALS['Core']->getPlugins('noExistFunc', $GLOBALS['Core']->getCurrentPluginService(), 'before', $this, $__runnedFunc) as $__i => $__e){
	if($__code = $GLOBALS['Core']->getPluginCode($__e)) eval($__code);
}

$__return = array();
foreach($GLOBALS['Core']->getPlugins('noExistFunc', $GLOBALS['Core']->getCurrentPluginService(), 'instead', $this, $__runnedFunc) as $__i => $__e){
	if($__code = $GLOBALS['Core']->getPluginCode($__e)) $__return[$__i] = eval($__code);
}

switch(count($__return)){
	case 0: $__return = 'noAnyPlugins'; break;
	case 1: $__return = $__return[Library::firstKey($__return)]; break;
	default: $__return = $__return; break;
}

?>