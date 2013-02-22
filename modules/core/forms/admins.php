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

$matrix['user_login']['text'] = '{Call:Lang:core:core:loginpolzova}';
$matrix['user_login']['type'] = 'text';
$matrix['user_login']['comment'] = '{Call:Lang:core:core:administrato1}';

require(_W.'forms/type_newpwd.php');
require(_W.'forms/type_neweml.php');

$matrix['group']['text'] = '{Call:Lang:core:core:otnestikgrup}';
$matrix['group']['type'] = 'select';
$matrix['group']['comment'] = '{Call:Lang:core:core:vsluchaeotne}';
$matrix['group']['additional'] = $groups;

$matrix['ip_access_type']['text'] = '';
$matrix['ip_access_type']['type'] = 'radio';
$matrix['ip_access_type']['additional'] = array(
	'allow' => '{Call:Lang:core:core:dostuprazres}',
	'disallow' => '{Call:Lang:core:core:dostupzapres}'
);
$values['ip_access_type'] = 'allow';

$matrix['show']['text'] = '{Call:Lang:core:core:administrato2}';
$matrix['show']['type'] = 'checkbox';
$values['show'] = 1;

?>