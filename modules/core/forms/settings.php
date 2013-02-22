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


require(_W.'forms/type_newlogin.php');
require(_W.'forms/type_neweml.php');

$matrix['name']['text'] = '{Call:Lang:core:core:imia}';
$matrix['name']['type'] = 'text';
$matrix['name']['template'] = 'center';

require(_W.'forms/type_newpwd.php');

?>