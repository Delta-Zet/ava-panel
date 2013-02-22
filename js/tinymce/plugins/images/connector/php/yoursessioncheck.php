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



/**
 * User session check, for registered users
 *
 * If you don't care about access,
 * please remove or comment following code
 *
 */

return true;

if(!isset( $_SESSION['user'] )) {
	echo 'Access denied, check file '.basename(__FILE__);
	exit();
}

?>